<?php

namespace App\Stripe;

use Money\Currency;
use Money\Money;
use Stripe\Event;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Webmozart\Assert\Assert;

#[AutoconfigureTag('stripe.remote_event_handler')]
final readonly class UpdatePrice
{
    public function __construct(
        private MysqlPriceRepository $prices,
    ) {
    }

    private static function extract(Event $event): \Stripe\Price
    {
        /** @psalm-suppress UndefinedMagicPropertyFetch */
        $obj = $event->data->object;
        Assert::isInstanceOf($obj, \Stripe\Price::class);

        return $obj;
    }

    public function __invoke(Event $event): void
    {
        match ($event->type) {
            Event::PRICE_CREATED, Event::PRICE_UPDATED => $this->createOrUpdatePrice(self::extract($event)),
            Event::PRICE_DELETED => $this->removePrice(self::extract($event)),
            default => null,
        };
    }

    private function createOrUpdatePrice(\Stripe\Price $object): void
    {
        $unitAmount = (string) $object->unit_amount;
        Assert::stringNotEmpty($unitAmount);
        Assert::numeric($unitAmount);
        Assert::stringNotEmpty($object->currency);

        // TODO: handle $object->recurring
        // TODO: why does not handle multiple currencies?
        $this->prices->createOrUpdate(new Price(
            new PriceId($object->id),
            new ProductId((string) $object->product),
            $object->active,
            $object->billing_scheme, // TODO: this should be an enum
            new Money($unitAmount, new Currency($object->currency))
        ));
    }

    private function removePrice(\Stripe\Price $price): void
    {
        $this->prices->remove(new PriceId($price->id));
    }
}
