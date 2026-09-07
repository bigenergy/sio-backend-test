<?php

declare(strict_types=1);

namespace App\Payment;

use Systemeio\TestForCandidates\PaymentProcessor\PaypalPaymentProcessor;

/**
 * Paypal already bills in the smallest currency unit, so the amount passes
 * through untouched; only its way of failing — a bare \Exception — needs
 * translating into the application's own failure type.
 */
final class PaypalPaymentAdapter implements PaymentProcessorInterface
{
    public function __construct(
        private readonly PaypalPaymentProcessor $processor,
    ) {
    }

    public function getName(): string
    {
        return 'paypal';
    }

    public function pay(int $priceInCents): void
    {
        try {
            $this->processor->pay($priceInCents);
        } catch (\Exception $exception) {
            throw new PaymentFailedException($exception->getMessage(), previous: $exception);
        }
    }
}
