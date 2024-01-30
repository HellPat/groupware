<?php

namespace App\Stripe;

use function Psl\Str\starts_with;

abstract readonly class PrefixedId implements \Stringable
{
    final public function __construct(private string $id)
    {
        // See https://gist.github.com/fnky/76f533366f75cf75802c8052b577e2a5
        if (!starts_with($id, static::prefix())) {
            throw new \InvalidArgumentException(sprintf('Invalid customer id %s', $id));
        }
    }

    abstract protected static function prefix(): string;

    #[\Override]
    final public function __toString(): string
    {
        return $this->id;
    }
}
