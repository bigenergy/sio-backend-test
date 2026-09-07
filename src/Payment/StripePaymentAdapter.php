<?php

declare(strict_types=1);

namespace App\Payment;

use App\Price\Money;
use Systemeio\TestForCandidates\PaymentProcessor\StripePaymentProcessor;

/**
 * Stripe bills in whole currency units and reports a refusal by returning
 * false, so this adapter converts the amount and turns the boolean into the
 * exception the rest of the application expects.
 */
final class StripePaymentAdapter implements PaymentProcessorInterface
{
    public function __construct(
        private readonly StripePaymentProcessor $processor,
    ) {
    }

    public function getName(): string
    {
        return 'stripe';
    }

    public function pay(int $priceInCents): void
    {
        if (!$this->processor->processPayment(Money::centsToEuros($priceInCents))) {
            throw new PaymentFailedException('Stripe rejected the payment.');
        }
    }
}
