<?php

namespace App\Stripe;

use Stripe\Event;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Webmozart\Assert\Assert;

#[AutoconfigureTag('stripe.remote_event_handler')]
final readonly class UpdateProduct
{
    public function __construct(
        private MysqlProductRepository $products,
    ) {
    }

    private static function extract(Event $event): \Stripe\Product
    {
        /** @psalm-suppress UndefinedMagicPropertyFetch */
        $obj = $event->data->object;
        Assert::isInstanceOf($obj, \Stripe\Product::class);

        return $obj;
    }

    public function __invoke(Event $event): void
    {
        match ($event->type) {
            Event::PRODUCT_CREATED, Event::PRODUCT_UPDATED => $this->createOrUpdateProduct(self::extract($event)),
            Event::PRODUCT_DELETED => $this->removeProduct(self::extract($event)),
            default => null,
        };
    }

    private function createOrUpdateProduct(\Stripe\Product $product): void
    {
        $this->products->createOrUpdate(new Product(
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
        $this->products->remove(new ProductId($product->id));
    }
}
