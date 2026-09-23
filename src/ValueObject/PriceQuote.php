<?php

declare(strict_types=1);

namespace App\ValueObject;

use App\Entity\Product;
use App\Enum\Country;

/**
 * A priced product for one customer: what was priced, whose tax rules applied,
 * and how the total was reached.
 */
final readonly class PriceQuote
{
    public function __construct(
        public Product $product,
        public Country $country,
        public PriceBreakdown $breakdown,
    ) {
    }
}
