<?php

declare(strict_types=1);

namespace App\Tests\Unit\Payment;

use App\Payment\PaymentFailedException;
use App\Payment\PaypalPaymentAdapter;
use PHPUnit\Framework\TestCase;
use Systemeio\TestForCandidates\PaymentProcessor\PaypalPaymentProcessor;

/**
 * The real processor is used rather than a double: it is the boundary this
 * adapter exists to cover, and its refusal threshold is what proves the amount
 * arrives in the unit Paypal expects.
 */
final class PaypalPaymentAdapterTest extends TestCase
{
    private PaypalPaymentAdapter $adapter;

    protected function setUp(): void
    {
        $this->adapter = new PaypalPaymentAdapter(new PaypalPaymentProcessor());
    }

    public function testIsSelectedByName(): void
    {
        self::assertSame('paypal', $this->adapter->getName());
    }

    public function testChargesAnAmountWithinTheLimit(): void
    {
        $this->expectNotToPerformAssertions();

        // Paypal refuses above 100000 cents, so this is the largest it accepts.
        $this->adapter->pay(100000);
    }

    public function testTranslatesARefusalIntoAPaymentFailure(): void
    {
        $this->expectException(PaymentFailedException::class);

        $this->adapter->pay(100001);
    }

    public function testKeepsTheOriginalFailureAsTheCause(): void
    {
        try {
            $this->adapter->pay(100001);
            self::fail('Expected the payment to be refused.');
        } catch (PaymentFailedException $exception) {
            self::assertInstanceOf(\Exception::class, $exception->getPrevious());
            self::assertStringContainsString('Too high price', $exception->getMessage());
        }
    }
}
