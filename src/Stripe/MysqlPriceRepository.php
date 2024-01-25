<?php

namespace App\Stripe;

use Doctrine\DBAL\Connection;
use Money\Currency;
use Money\Money;
use Stripe\Event;
use Webmozart\Assert\Assert;

final readonly class MysqlPriceRepository implements StripeEventAware
{
    public function __construct(private Connection $connection)
    {
    }

    private function createOrUpdate(Price $price): void
    {
        $this->connection->executeStatement(
            '
            INSERT INTO price
                (id, product_id, active, billing_scheme, currency, unit_amount)
            VALUES
                (:id, :product_id, :active, :billing_scheme, :currency, :unit_amount)
            ON DUPLICATE KEY UPDATE
                product_id = :product_id,
                active = :active,
                billing_scheme = :billing_scheme,
                currency = :currency,
                unit_amount = :unit_amount
            ',
            [
                'id' => $price->id->__toString(),
                'product_id' => $price->productId->__toString(),
                'active' => $price->active,
                'billing_scheme' => $price->billingScheme,
                'unit_amount' => $price->unitAmount->getAmount(),
                'currency' => $price->unitAmount->getCurrency()->getCode(),
            ]
        );
    }

    private function remove(PriceId $price): void
    {
        $this->connection->executeStatement(
            'DELETE FROM price WHERE id = :id',
            ['id' => $price->__toString()]
        );
    }

    private static function extract(Event $event): \Stripe\Price
    {
        /** @psalm-suppress UndefinedMagicPropertyFetch */
        $obj = $event->data->object;
        Assert::isInstanceOf($obj, \Stripe\Price::class);

        return $obj;
    }

    public function handleStripeEvent(Event $event): void
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
        $this->createOrUpdate(new Price(
            new PriceId($object->id),
            new ProductId((string) $object->product),
            $object->active,
            $object->billing_scheme, // TODO: this should be an enum
            new Money($unitAmount, new Currency($object->currency))
        ));
    }

    private function removePrice(\Stripe\Price $price): void
    {
        $this->remove(new PriceId($price->id));
    }
}
