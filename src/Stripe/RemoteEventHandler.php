<?php

namespace App\Stripe;

use Stripe\Event;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Webmozart\Assert\Assert;

#[AsMessageHandler]
final readonly class RemoteEventHandler
{
    public function __construct(
        #[TaggedIterator('stripe.remote_event_handler')]
        private iterable $handlers,
    ) {
    }

    /**
     * @throws \JsonException
     */
    public function __invoke(RemoteEvent $message): void
    {
        $event = Event::constructFrom(
            (array) json_decode($message->payload, true, 512, JSON_THROW_ON_ERROR)
        );

        foreach ($this->handlers as $handler) {
            Assert::object($handler);
            Assert::methodExists($handler, '__invoke');
            /* @psalm-suppress MixedMethodCall */
            $handler->__invoke($event);
        }
    }
}
