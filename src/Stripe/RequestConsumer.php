<?php

namespace App\Stripe;

use Symfony\Component\RemoteEvent\Attribute\AsRemoteEventConsumer;
use Symfony\Component\RemoteEvent\RemoteEvent;

#[AsRemoteEventConsumer('stripe')]
final class RequestConsumer
{
    public function consume(RemoteEvent $event): void
    {
        // Process the event returned by our parser
    }
}
