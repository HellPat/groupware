<?php

namespace App\Stripe;

use Stripe\Event;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class RemoteEventHandler
{
    /**
     * @throws \JsonException
     */
    public function __invoke(RemoteEvent $event): void
    {
        $action = Event::constructFrom(
            json_decode($event->payload, true, 512, JSON_THROW_ON_ERROR)
        );
        
        // handle it by $action->type
    }
}
