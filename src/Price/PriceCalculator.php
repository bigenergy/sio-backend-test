<?php

declare(strict_types=1);

namespace App\Price;

use App\Entity\Coupon;
use App\Entity\Product;
use App\Enum\Country;

/**
 * Applies the coupon first and taxes the remainder, which is the order the
 * task's own example implies: 100 EUR less 6% is 94 EUR, and 94 EUR plus the
 * Greek 24% comes to 116.56 EUR.
 */
final class PriceCalculator
{
    public function calculate(Product $product, Country $country, ?Coupon $coupon = null): PriceBreakdown
    {
        $subtotalInCents = $product->getPriceInCents();
        $discountInCents = $coupon?->discountFor($subtotalInCents) ?? 0;
        $netInCents = $subtotalInCents - $discountInCents;
        $taxRatePercent = $country->taxRatePercent();

        return new PriceBreakdown(
            subtotalInCents: $subtotalInCents,
            discountInCents: $discountInCents,
            taxInCents: (int) round($netInCents * $taxRatePercent / 100),
            taxRatePercent: $taxRatePercent,
        );
    }
}
