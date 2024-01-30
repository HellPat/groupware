<?php

namespace App\Stripe;

use Doctrine\DBAL\Connection;
use Stripe\Event;
use Webmozart\Assert\Assert;

final readonly class MysqlProductRepository implements StripeEventAware
{
    public function __construct(private Connection $connection)
    {
    }

    private function createOrUpdate(Product $product): void
    {
        $this->connection->executeStatement(
            '
            INSERT INTO product
                (id, name, default_price_id, active, type, description, created_at)
            VALUES (:id, :name, :default_price_id, :active, :type, :description, :created_at)
            ON DUPLICATE KEY UPDATE
                name = :name,
                default_price_id = :default_price_id,
                active = :active,
                type = :type,
                description = :description
            ',
            [
                'id' => $product->id->__toString(),
                'name' => $product->name,
                'default_price_id' => $product->price?->__toString(),
                'active' => $product->active ? 1 : 0,
                'type' => $product->type,
                'description' => $product->description,
                'created_at' => $product->createdAt->format('Y-m-d H:i:s'),
            ]
        );
    }

    private function remove(ProductId $id): void
    {
        $this->connection->executeStatement(
            'DELETE FROM product WHERE id = :id',
            ['id' => $id->__toString()]
        );
    }

    /**
     * @return list<Product>
     */
    public function all(): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT id, name, default_price_id, active, type, description, created_at FROM product'
        );

        return array_map(
            fn (array $row) => new Product(
                id: new ProductId((string) $row['id']),
                name: (string) $row['name'],
                price: $row['default_price_id'] ? new PriceId((string) $row['default_price_id']) : null,
                active: (bool) $row['active'],
                createdAt: new \DateTimeImmutable((string) $row['created_at']),
                type: (string) $row['type'],
                description: (string) $row['description'],
            ),
            $rows
        );
    }

    private static function extract(Event $event): \Stripe\Product
    {
        /** @psalm-suppress UndefinedMagicPropertyFetch */
        $obj = $event->data->object;
        Assert::isInstanceOf($obj, \Stripe\Product::class);

        return $obj;
    }

    #[\Override]
    public function handleStripeEvent(Event $event): void
    {
        match ($event->type) {
            Event::PRODUCT_CREATED, Event::PRODUCT_UPDATED => $this->createOrUpdateProduct(self::extract($event)),
            Event::PRODUCT_DELETED => $this->removeProduct(self::extract($event)),
            default => null,
        };
    }

    private function createOrUpdateProduct(\Stripe\Product $product): void
    {
        $this->createOrUpdate(new Product(
            new ProductId($product->id),
            $product->name,
            $product->default_price ? new PriceId((string) $product->default_price) : null,
            $product->active,
            new \DateTimeImmutable('@' . $product->created),
            $product->type, // TODO: this should be an enum
            (string) $product->description,
        ));
    }

    private function removeProduct(\Stripe\Product $product): void
    {
        $this->remove(new ProductId($product->id));
    }
}
