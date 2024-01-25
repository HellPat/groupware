<?php

namespace App\Stripe;

use Stripe\Event;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\RecoverableExceptionInterface;
use Symfony\Component\Messenger\Exception\UnrecoverableExceptionInterface;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Webmozart\Assert\Assert;

/**
 * TODO: try out multiple handlers for RemoteEvent
 */
#[AsMessageHandler]
final readonly class RemoteEventHandler
{
    public function __construct(
        #[TaggedIterator('stripe.remote_event_handler')]
        /**
         * @var iterable<StripeEventAware>
         */
        private iterable $handlers,
    ) {
    }

    /**
     * @throws RecoverableExceptionInterface
     * @throws UnrecoverableExceptionInterface
     * @throws \Exception
     */
    public function __invoke(RemoteEvent $message): void
    {
        
        try {
            $event = Event::constructFrom(
                (array) json_decode($message->payload, true, 512, JSON_THROW_ON_ERROR)
            );    
        } catch (\JsonException $e) {
            throw new UnrecoverableMessageHandlingException('Invalid JSON payload', 0, $e);
        }

        foreach ($this->handlers as $handler) {
            Assert::object($handler);
            // TODO: Try out multiple handlers for RemoteEvent
            //       Exceptions handling affects other handlers
            //       When one handler throws recoverable / unrecoverable exception
            //       all handlers are affected by the decision.
            //       We should probably catch the exception and continue with the next handler.
            $handler->handleStripeEvent($event);
        }
    }
}
