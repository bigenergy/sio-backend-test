<?php

declare(strict_types=1);

namespace App\Tests\Unit\Payment;

use App\Payment\PaymentProcessorRegistry;
use App\Payment\PaypalPaymentAdapter;
use App\Payment\StripePaymentAdapter;
use PHPUnit\Framework\TestCase;
use Systemeio\TestForCandidates\PaymentProcessor\PaypalPaymentProcessor;
use Systemeio\TestForCandidates\PaymentProcessor\StripePaymentProcessor;

final class PaymentProcessorRegistryTest extends TestCase
{
    private PaymentProcessorRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = new PaymentProcessorRegistry([
            new PaypalPaymentAdapter(new PaypalPaymentProcessor()),
            new StripePaymentAdapter(new StripePaymentProcessor()),
        ]);
    }

    public function testResolvesAProcessorByName(): void
    {
        self::assertInstanceOf(PaypalPaymentAdapter::class, $this->registry->get('paypal'));
        self::assertInstanceOf(StripePaymentAdapter::class, $this->registry->get('stripe'));
    }

    public function testKnowsWhichNamesItAccepts(): void
    {
        self::assertTrue($this->registry->has('paypal'));
        self::assertFalse($this->registry->has('bitcoin'));
        self::assertEqualsCanonicalizing(['paypal', 'stripe'], $this->registry->names());
    }

    public function testRefusesToResolveAnUnknownName(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->registry->get('bitcoin');
    }
}
