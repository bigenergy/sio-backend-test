<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Entity\Coupon;
use App\Service\PriceCalculator;
use App\ValueObject\Money;
use PHPUnit\Framework\TestCase;

final class PriceCalculatorTest extends TestCase
{
    private PriceCalculator $calculator;

    protected function setUp(): void
    {
        $this->calculator = new PriceCalculator();
    }

    /**
     * The task's worked example: an Iphone for a Greek customer costs 124 EUR,
     * and 116.56 EUR once a 6% coupon applies.
     */
    public function testTaxesTheFullPriceWithoutACoupon(): void
    {
        $breakdown = $this->calculator->calculate(Money::fromCents(10000), 24.0);

        self::assertSame(0, $breakdown->discount->cents());
        self::assertSame(2400, $breakdown->tax->cents());
        self::assertSame(12400, $breakdown->total()->cents());
    }

    public function testAppliesTheCouponBeforeTheTax(): void
    {
        $breakdown = $this->calculator->calculate(Money::fromCents(10000), 24.0, Coupon::percentage('P6', 6.0));

        self::assertSame(600, $breakdown->discount->cents());
        self::assertSame(9400, $breakdown->net()->cents());
        self::assertSame(2256, $breakdown->tax->cents());
        self::assertSame(11656, $breakdown->total()->cents());
    }

    public function testAppliesAFixedCoupon(): void
    {
        $breakdown = $this->calculator->calculate(
            Money::fromCents(10000),
            19.0,
            Coupon::fixed('D15', Money::fromCents(1500)),
        );

        self::assertSame(8500, $breakdown->net()->cents());
        self::assertSame(1615, $breakdown->tax->cents());
        self::assertSame(10115, $breakdown->total()->cents());
    }

    public function testHandlesAFractionalTaxRate(): void
    {
        // 8.1% of 100.00 EUR is 8.10 EUR.
        $breakdown = $this->calculator->calculate(Money::fromCents(10000), 8.1);

        self::assertSame(810, $breakdown->tax->cents());
        self::assertSame(10810, $breakdown->total()->cents());
    }

    public function testRoundsTheTaxToWholeCents(): void
    {
        // 19% of 19.99 EUR is 3.7981 EUR.
        $breakdown = $this->calculator->calculate(Money::fromCents(1999), 19.0);

        self::assertSame(380, $breakdown->tax->cents());
        self::assertSame(2379, $breakdown->total()->cents());
    }

    public function testAFullCouponLeavesNothingToPayAndNothingToTax(): void
    {
        $breakdown = $this->calculator->calculate(Money::fromCents(10000), 19.0, Coupon::percentage('P100', 100.0));

        self::assertSame(0, $breakdown->net()->cents());
        self::assertSame(0, $breakdown->tax->cents());
        self::assertSame(0, $breakdown->total()->cents());
    }

    public function testAnOversizedFixedCouponDoesNotDriveThePriceNegative(): void
    {
        $breakdown = $this->calculator->calculate(
            Money::fromCents(1000),
            19.0,
            Coupon::fixed('D15', Money::fromCents(1500)),
        );

        self::assertSame(1000, $breakdown->discount->cents());
        self::assertSame(0, $breakdown->total()->cents());
    }
}
