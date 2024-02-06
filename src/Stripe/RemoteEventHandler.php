<?php

namespace App\Stripe;

use Doctrine\DBAL\Connection;
use Stripe\Event;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\RecoverableExceptionInterface;
use Symfony\Component\Messenger\Exception\UnrecoverableExceptionInterface;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Webmozart\Assert\Assert;

/**
 * TODO: try out multiple handlers for RemoteEvent.
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
        private Connection $connection,
    ) {
    }

    /**
     * @throws RecoverableExceptionInterface
     * @throws UnrecoverableExceptionInterface
     * @throws \Throwable
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

        $handlers = $this->handlers;
        Assert::allIsInstanceOf($handlers, StripeEventAware::class);

        /*
         * All or nothing approach.
         * When one handler fails, the whole transaction is rolled back.
         *
         * TODO: consider handlers that modify state outside the database.
         *       e.g. what happens when a handler updates a search index etc...
         */
        $this->connection->transactional(function () use ($event, $handlers) {
            foreach ($handlers as $handler) {
                $handler->handleStripeEvent($event);
            }
        });
    }
}
