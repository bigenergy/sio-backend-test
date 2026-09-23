<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Payment;

use App\Exception\PaymentFailed;
use App\Service\Payment\PaypalPaymentAdapter;
use App\ValueObject\Money;
use PHPUnit\Framework\TestCase;
use Systemeio\TestForCandidates\PaymentProcessor\PaypalPaymentProcessor;

/**
 * The real processor is used rather than a double: it is the boundary this
 * adapter exists to cover, and its refusal threshold proves the amount arrives
 * in the unit Paypal expects.
 */
final class PaypalPaymentAdapterTest extends TestCase
{
    private PaypalPaymentAdapter $adapter;

    protected function setUp(): void
    {
        $this->adapter = new PaypalPaymentAdapter(new PaypalPaymentProcessor());
    }

    public function testChargesAnAmountWithinTheLimit(): void
    {
        $this->expectNotToPerformAssertions();

        // Paypal refuses above 100000 cents, so this is the largest it takes.
        $this->adapter->pay(Money::fromCents(100000));
    }

    public function testTranslatesARefusalIntoAPaymentFailure(): void
    {
        $this->expectException(PaymentFailed::class);

        $this->adapter->pay(Money::fromCents(100001));
    }

    public function testKeepsTheOriginalFailureAsTheCause(): void
    {
        try {
            $this->adapter->pay(Money::fromCents(100001));
            self::fail('Expected the payment to be refused.');
        } catch (PaymentFailed $exception) {
            self::assertInstanceOf(\Exception::class, $exception->getPrevious());
            self::assertStringContainsString('Too high price', $exception->getMessage());
        }
    }
}
