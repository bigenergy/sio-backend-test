<?php

declare(strict_types=1);

namespace App\Exception;

final class CouponNotFound extends PricingException
{
    public static function withCode(string $code): self
    {
        return new self(sprintf('Coupon "%s" does not exist.', $code));
    }

    public function field(): string
    {
        return 'couponCode';
    }
}
