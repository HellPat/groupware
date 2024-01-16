<?php

namespace App\Stripe;

use Doctrine\DBAL\Connection;

final readonly class MysqlPriceRepository
{
    public function __construct(private Connection $connection)
    {
    }

    public function createOrUpdate(Price $price): void
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

    public function remove(PriceId $price): void
    {
        $this->connection->executeStatement(
            'DELETE FROM price WHERE id = :id',
            ['id' => $price->__toString()]
        );
    }
}
