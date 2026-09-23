<?php

declare(strict_types=1);

namespace App\Service\Payment;

use App\Exception\PaymentFailed;
use App\ValueObject\Money;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Systemeio\TestForCandidates\PaymentProcessor\StripePaymentProcessor;

/**
 * Stripe bills in whole currency units and reports a refusal by returning
 * false, so this adapter converts the amount and raises the exception the rest
 * of the application expects.
 */
#[AsTaggedItem('stripe')]
final class StripePaymentAdapter implements PaymentProcessorInterface
{
    public function __construct(
        private readonly StripePaymentProcessor $processor,
    ) {
    }

    public function pay(Money $amount): void
    {
        if (!$this->processor->processPayment($amount->toEuros())) {
            throw new PaymentFailed('Stripe rejected the payment.');
        }
    }
}
