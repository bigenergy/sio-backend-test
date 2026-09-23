<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\PaymentFailed;
use App\Exception\PricingException;
use App\Service\Payment\PaymentProcessorRegistry;
use App\ValueObject\PriceQuote;

/**
 * Prices the purchase, then charges it through the requested processor.
 */
final class PurchaseService
{
    public function __construct(
        private readonly PriceQuoteService $quotes,
        private readonly PaymentProcessorRegistry $processors,
    ) {
    }

    /**
     * @throws PricingException when the purchase cannot be priced
     * @throws PaymentFailed    when the processor refuses the payment
     */
    public function purchase(
        int $productId,
        string $taxNumber,
        ?string $couponCode,
        string $processorName,
    ): PriceQuote {
        $quote = $this->quotes->quote($productId, $taxNumber, $couponCode);

        $this->processors->get($processorName)->pay($quote->breakdown->total());

        return $quote;
    }
}
