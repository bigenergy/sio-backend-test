<?php

declare(strict_types=1);

namespace App\Price;

/**
 * Result of pricing one product for one customer, in cents.
 *
 * The intermediate amounts are kept rather than just the total: they are what
 * makes the response self-explanatory and the unit tests precise about where a
 * rounding error would come from.
 */
final readonly class PriceBreakdown
{
    public function __construct(
        public int $subtotalInCents,
        public int $discountInCents,
        public int $taxInCents,
        public int $taxRatePercent,
    ) {
    }

    /**
     * Price after the coupon, before tax.
     */
    public function netInCents(): int
    {
        return $this->subtotalInCents - $this->discountInCents;
    }

    public function totalInCents(): int
    {
        return $this->netInCents() + $this->taxInCents;
    }
}
