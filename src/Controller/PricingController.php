<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\CalculatePriceRequest;
use App\Dto\PurchaseRequest;
use App\Entity\Product;
use App\Enum\Country;
use App\Payment\PaymentProcessorRegistry;
use App\Price\Money;
use App\Price\PriceBreakdown;
use App\Price\PriceCalculator;
use App\Repository\CouponRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

/**
 * The two endpoints of the task. They share the same input, so they share the
 * step that turns that input into a price; splitting them across two
 * controllers would only move those four lines into a service.
 */
final class PricingController extends AbstractController
{
    public function __construct(
        private readonly ProductRepository $products,
        private readonly CouponRepository $coupons,
        private readonly PriceCalculator $calculator,
        private readonly PaymentProcessorRegistry $paymentProcessors,
    ) {
    }

    #[Route('/calculate-price', name: 'calculate_price', methods: ['POST'])]
    public function calculatePrice(#[MapRequestPayload] CalculatePriceRequest $request): JsonResponse
    {
        $product = $this->product($request->product);
        $breakdown = $this->quote($product, $request->taxNumber, $request->couponCode);

        return $this->json($this->describe($product, $breakdown));
    }

    #[Route('/purchase', name: 'purchase', methods: ['POST'])]
    public function purchase(#[MapRequestPayload] PurchaseRequest $request): JsonResponse
    {
        $product = $this->product($request->product);
        $breakdown = $this->quote($product, $request->taxNumber, $request->couponCode);

        // A refusal surfaces as PaymentFailedException and is turned into a 422
        // by App\EventListener\ApiExceptionListener.
        $this->paymentProcessors->get($request->paymentProcessor)->pay($breakdown->totalInCents());

        return $this->json($this->describe($product, $breakdown) + [
            'status' => 'paid',
            'paymentProcessor' => $request->paymentProcessor,
        ]);
    }

    /**
     * The request has already been validated, so the product is known to exist
     * and the tax number is known to belong to a supported country. The asserts
     * state that expectation without turning it into a runtime branch.
     */
    private function product(?int $id): Product
    {
        $product = $this->products->find($id);
        \assert($product instanceof Product);

        return $product;
    }

    private function quote(Product $product, ?string $taxNumber, ?string $couponCode): PriceBreakdown
    {
        $country = Country::tryFromTaxNumber((string) $taxNumber);
        \assert($country instanceof Country);

        $coupon = $couponCode !== null ? $this->coupons->findOneByCode($couponCode) : null;

        return $this->calculator->calculate($product, $country, $coupon);
    }

    /**
     * @return array<string, mixed>
     */
    private function describe(Product $product, PriceBreakdown $breakdown): array
    {
        return [
            'product' => $product->getName(),
            'price' => Money::centsToEuros($breakdown->totalInCents()),
            'currency' => Money::CURRENCY,
            'breakdown' => [
                'subtotal' => Money::centsToEuros($breakdown->subtotalInCents),
                'discount' => Money::centsToEuros($breakdown->discountInCents),
                'taxRate' => $breakdown->taxRatePercent,
                'tax' => Money::centsToEuros($breakdown->taxInCents),
            ],
        ];
    }
}
