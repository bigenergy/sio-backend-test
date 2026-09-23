<?php

declare(strict_types=1);

namespace App\Exception;

final class ProductNotFound extends PricingException
{
    public static function withId(int $id): self
    {
        return new self(sprintf('Product "%d" does not exist.', $id));
    }

    public function field(): string
    {
        return 'product';
    }
}
