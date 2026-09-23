<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Payment;

use App\Exception\PaymentFailed;
use App\Service\Payment\StripePaymentAdapter;
use App\ValueObject\Money;
use PHPUnit\Framework\TestCase;
use Systemeio\TestForCandidates\PaymentProcessor\StripePaymentProcessor;

/**
 * Stripe refuses anything below 100 EUR, which makes its threshold a precise
 * check that this adapter converts cents into euros instead of passing the
 * cent amount straight through.
 */
final class StripePaymentAdapterTest extends TestCase
{
    private StripePaymentAdapter $adapter;

    protected function setUp(): void
    {
        $this->adapter = new StripePaymentAdapter(new StripePaymentProcessor());
    }

    public function testChargesAnAmountOnTheThreshold(): void
    {
        $this->expectNotToPerformAssertions();

        // 10000 cents is exactly the 100 EUR Stripe still accepts.
        $this->adapter->pay(Money::fromCents(10000));
    }

    public function testTranslatesARefusalIntoAPaymentFailure(): void
    {
        $this->expectException(PaymentFailed::class);

        // One cent below the threshold, which only fails if cents became euros.
        $this->adapter->pay(Money::fromCents(9999));
    }
}
