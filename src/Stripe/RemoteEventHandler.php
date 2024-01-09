<?php

namespace App\Stripe;

use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class RemoteEventHandler
{
    public function __invoke(RemoteEvent $event): void
    {
        // ...
    }
}
