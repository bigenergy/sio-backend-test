<?php

declare(strict_types=1);

namespace App\Dto;

use App\Validator\ExistingCoupon;
use App\Validator\ExistingProduct;
use App\Validator\SupportedPaymentProcessor;
use App\Validator\TaxNumber;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Body of POST /purchase — the price request plus the processor to charge
 * through.
 *
 * Kept separate from CalculatePriceRequest rather than extending it: the two
 * endpoints are free to diverge, and repeating three fields costs less than
 * the coupling would.
 */
final readonly class PurchaseRequest
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

        #[Assert\NotBlank(message: 'A payment processor is required.')]
        #[SupportedPaymentProcessor]
        public ?string $paymentProcessor = null,
    ) {
    }
}
