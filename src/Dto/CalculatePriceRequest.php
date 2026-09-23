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
 * Fields are nullable with a default so a missing one reaches the validator as
 * null and produces a proper error instead of failing to deserialize. The
 * constraints run in sequence: a field that is absent or malformed is reported
 * once, and the database is never queried for an id that cannot exist.
 */
final readonly class CalculatePriceRequest
{
    public function __construct(
        #[Assert\Sequentially([
            new Assert\NotNull(message: 'A product is required.'),
            new Assert\Positive(message: 'A product id must be a positive integer.'),
            new ExistingProduct(),
        ])]
        public ?int $product = null,

        #[Assert\Sequentially([
            new Assert\NotBlank(message: 'A tax number is required.'),
            new TaxNumber(),
        ])]
        public ?string $taxNumber = null,

        #[ExistingCoupon]
        public ?string $couponCode = null,
    ) {
    }

    public function getProductId(): int
    {
        return $this->product ?? throw new \LogicException('The request was read before it was validated.');
    }

    public function getTaxNumber(): string
    {
        return $this->taxNumber ?? throw new \LogicException('The request was read before it was validated.');
    }
}
