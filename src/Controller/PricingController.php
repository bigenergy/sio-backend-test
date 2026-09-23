<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\CalculatePriceRequest;
use App\Dto\PurchaseRequest;
use App\Service\PriceQuoteService;
use App\Service\PurchaseService;
use App\ValueObject\Money;
use App\ValueObject\PriceQuote;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

/**
 * The two endpoints of the task. Pricing and charging live in the services;
 * what is left here is reading the request and shaping the response.
 */
final class PricingController extends AbstractController
{
    public function __construct(
        private readonly PriceQuoteService $quotes,
        private readonly PurchaseService $purchases,
    ) {
    }

    #[Route('/calculate-price', name: 'calculate_price', methods: ['POST'])]
    public function calculatePrice(#[MapRequestPayload] CalculatePriceRequest $request): JsonResponse
    {
        $quote = $this->quotes->quote(
            $request->getProductId(),
            $request->getTaxNumber(),
            $request->couponCode,
        );

        return $this->respond($this->describe($quote));
    }

    #[Route('/purchase', name: 'purchase', methods: ['POST'])]
    public function purchase(#[MapRequestPayload] PurchaseRequest $request): JsonResponse
    {
        $quote = $this->purchases->purchase(
            $request->getProductId(),
            $request->getTaxNumber(),
            $request->couponCode,
            $request->getPaymentProcessor(),
        );

        return $this->respond($this->describe($quote) + [
            'status' => 'paid',
            'paymentProcessor' => $request->getPaymentProcessor(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function describe(PriceQuote $quote): array
    {
        $breakdown = $quote->breakdown;

        return [
            'product' => $quote->product->getName(),
            'price' => $breakdown->total()->toEuros(),
            'currency' => Money::CURRENCY,
            'breakdown' => [
                'subtotal' => $breakdown->subtotal->toEuros(),
                'discount' => $breakdown->discount->toEuros(),
                'taxRate' => $breakdown->taxRatePercent,
                'tax' => $breakdown->tax->toEuros(),
            ],
        ];
    }

    /**
     * Amounts keep their fractional part even on a whole euro, so a client
     * always reads a price in the same shape: 124.0, not 124.
     *
     * @param array<string, mixed> $payload
     */
    private function respond(array $payload): JsonResponse
    {
        return $this->json($payload, context: ['json_encode_options' => \JSON_PRESERVE_ZERO_FRACTION]);
    }
}
