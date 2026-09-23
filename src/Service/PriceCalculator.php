<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Coupon;
use App\ValueObject\Money;
use App\ValueObject\PriceBreakdown;

/**
 * Applies the coupon first and taxes the remainder, the order the task's own
 * example implies: 100 EUR less 6% is 94 EUR, and 94 EUR plus the Greek 24%
 * comes to 116.56 EUR.
 */
final class PriceCalculator
{
    public function calculate(Money $price, float $taxRatePercent, ?Coupon $coupon = null): PriceBreakdown
    {
        $discount = $coupon?->discountFor($price) ?? Money::zero();

        return new PriceBreakdown(
            subtotal: $price,
            discount: $discount,
            tax: $price->minus($discount)->percentage($taxRatePercent),
            taxRatePercent: $taxRatePercent,
        );
    }
}
