<?php

declare(strict_types=1);

namespace App\Tests\Unit\Price;

use App\Entity\Coupon;
use App\Entity\Product;
use App\Enum\Country;
use App\Enum\CouponType;
use App\Price\PriceCalculator;
use PHPUnit\Framework\TestCase;

final class PriceCalculatorTest extends TestCase
{
    private PriceCalculator $calculator;

    protected function setUp(): void
    {
        $this->calculator = new PriceCalculator();
    }

    /**
     * The task's own worked example: an Iphone for a Greek customer costs
     * 124 EUR, and 116.56 EUR once a 6% coupon is applied.
     */
    public function testTaxesTheFullPriceWithoutACoupon(): void
    {
        $breakdown = $this->calculator->calculate(new Product('Iphone', 10000), Country::Greece);

        self::assertSame(0, $breakdown->discountInCents);
        self::assertSame(2400, $breakdown->taxInCents);
        self::assertSame(12400, $breakdown->totalInCents());
    }

    public function testAppliesTheCouponBeforeTheTax(): void
    {
        $breakdown = $this->calculator->calculate(
            new Product('Iphone', 10000),
            Country::Greece,
            new Coupon('P6', CouponType::Percentage, 6),
        );

        self::assertSame(600, $breakdown->discountInCents);
        self::assertSame(9400, $breakdown->netInCents());
        self::assertSame(2256, $breakdown->taxInCents);
        self::assertSame(11656, $breakdown->totalInCents());
    }

    public function testAppliesAFixedCoupon(): void
    {
        $breakdown = $this->calculator->calculate(
            new Product('Iphone', 10000),
            Country::Germany,
            new Coupon('D15', CouponType::Fixed, 1500),
        );

        self::assertSame(8500, $breakdown->netInCents());
        self::assertSame(1615, $breakdown->taxInCents);
        self::assertSame(10115, $breakdown->totalInCents());
    }

    public function testRoundsTheTaxToWholeCents(): void
    {
        // 19% of 19.99 EUR is 3.7981 EUR.
        $breakdown = $this->calculator->calculate(new Product('Наушники', 1999), Country::Germany);

        self::assertSame(380, $breakdown->taxInCents);
        self::assertSame(2379, $breakdown->totalInCents());
    }

    public function testAFullCouponLeavesNothingToPayAndNothingToTax(): void
    {
        $breakdown = $this->calculator->calculate(
            new Product('Iphone', 10000),
            Country::Germany,
            new Coupon('P100', CouponType::Percentage, 100),
        );

        self::assertSame(0, $breakdown->netInCents());
        self::assertSame(0, $breakdown->taxInCents);
        self::assertSame(0, $breakdown->totalInCents());
    }

    public function testAnOversizedFixedCouponDoesNotDriveThePriceNegative(): void
    {
        $breakdown = $this->calculator->calculate(
            new Product('Чехол', 1000),
            Country::Germany,
            new Coupon('D15', CouponType::Fixed, 1500),
        );

        self::assertSame(1000, $breakdown->discountInCents);
        self::assertSame(0, $breakdown->totalInCents());
    }
}
