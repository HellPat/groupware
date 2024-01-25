<?php
declare(strict_types=1);

namespace App\Stripe;

use Stripe\Event;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Messenger\Exception\RecoverableExceptionInterface;
use Symfony\Component\Messenger\Exception\UnrecoverableExceptionInterface;

#[AutoconfigureTag('stripe.remote_event_handler')]
interface StripeEventAware
{
    /**
     * @throws UnrecoverableExceptionInterface If the event should not be retried
     * @throws RecoverableExceptionInterface If the event should be retried forever
     * @throws \Exception If the event should be retried according to the transport configuration
     */
    public function handleStripeEvent(Event $event): void;
}