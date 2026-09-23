<?php

declare(strict_types=1);

namespace App\ValueObject;

/**
 * How a total was reached: the product price, what the coupon took off, and
 * the tax charged on the remainder.
 */
final readonly class PriceBreakdown
{
    public function __construct(
        public Money $subtotal,
        public Money $discount,
        public Money $tax,
        public float $taxRatePercent,
    ) {
    }

    /**
     * Price after the coupon, before tax.
     */
    public function net(): Money
    {
        return $this->subtotal->minus($this->discount);
    }

    public function total(): Money
    {
        return $this->net()->plus($this->tax);
    }
}
