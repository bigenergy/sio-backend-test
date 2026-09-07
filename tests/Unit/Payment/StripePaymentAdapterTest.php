<?php

declare(strict_types=1);

namespace App\Tests\Unit\Payment;

use App\Payment\PaymentFailedException;
use App\Payment\StripePaymentAdapter;
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

    public function testIsSelectedByName(): void
    {
        self::assertSame('stripe', $this->adapter->getName());
    }

    public function testChargesAnAmountOnTheThreshold(): void
    {
        $this->expectNotToPerformAssertions();

        // 10000 cents is exactly the 100 EUR Stripe still accepts.
        $this->adapter->pay(10000);
    }

    public function testTranslatesARefusalIntoAPaymentFailure(): void
    {
        $this->expectException(PaymentFailedException::class);

        // One cent below the threshold, which only fails if cents became euros.
        $this->adapter->pay(9999);
    }
}
