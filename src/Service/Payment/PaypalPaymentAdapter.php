<?php

declare(strict_types=1);

namespace App\Service\Payment;

use App\Exception\PaymentFailed;
use App\ValueObject\Money;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Systemeio\TestForCandidates\PaymentProcessor\PaypalPaymentProcessor;

/**
 * Paypal bills in the smallest currency unit, so the amount passes through as
 * cents; only its way of failing, a bare \Exception, needs translating.
 */
#[AsTaggedItem('paypal')]
final class PaypalPaymentAdapter implements PaymentProcessorInterface
{
    public function __construct(
        private readonly PaypalPaymentProcessor $processor,
    ) {
    }

    public function pay(Money $amount): void
    {
        try {
            $this->processor->pay($amount->cents());
        } catch (\Exception $exception) {
            throw new PaymentFailed($exception->getMessage(), previous: $exception);
        }
    }
}
