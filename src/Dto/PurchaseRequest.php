<?php

declare(strict_types=1);

namespace App\Dto;

use App\Validator\ExistingCoupon;
use App\Validator\ExistingProduct;
use App\Validator\SupportedPaymentProcessor;
use App\Validator\TaxNumber;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Body of POST /purchase: the price request plus the processor to charge
 * through.
 *
 * Kept separate from CalculatePriceRequest rather than extending it, so the
 * two endpoints are free to diverge.
 */
final readonly class PurchaseRequest
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

        #[Assert\Sequentially([
            new Assert\NotBlank(message: 'A payment processor is required.'),
            new SupportedPaymentProcessor(),
        ])]
        public ?string $paymentProcessor = null,
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

    public function getPaymentProcessor(): string
    {
        return $this->paymentProcessor ?? throw new \LogicException('The request was read before it was validated.');
    }
}
