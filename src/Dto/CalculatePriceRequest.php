<?php

declare(strict_types=1);

namespace App\Dto;

use App\Validator\ExistingCoupon;
use App\Validator\ExistingProduct;
use App\Validator\TaxNumber;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Body of POST /calculate-price.
 *
 * Every field is nullable with a default so that a missing field reaches the
 * validator as null and produces a proper error, instead of failing to
 * deserialize at all.
 */
final readonly class CalculatePriceRequest
{
    public function __construct(
        #[Assert\NotNull(message: 'A product is required.')]
        #[Assert\Positive(message: 'A product id must be a positive integer.')]
        #[ExistingProduct]
        public ?int $product = null,

        #[Assert\NotBlank(message: 'A tax number is required.')]
        #[TaxNumber]
        public ?string $taxNumber = null,

        #[ExistingCoupon]
        public ?string $couponCode = null,
    ) {
    }
}
