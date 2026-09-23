<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Coupon;
use App\Enum\CouponType;
use App\ValueObject\Money;
use PHPUnit\Framework\TestCase;

final class CouponTest extends TestCase
{
    public function testFixedCouponTakesOffItsFaceValue(): void
    {
        $coupon = Coupon::fixed('D15', Money::fromCents(1500));

        self::assertSame(CouponType::Fixed, $coupon->getType());
        self::assertSame(1500, $coupon->discountFor(Money::fromCents(10000))->cents());
    }

    public function testPercentageCouponTakesOffAShareOfThePrice(): void
    {
        $coupon = Coupon::percentage('P6', 6.0);

        self::assertSame(CouponType::Percentage, $coupon->getType());
        self::assertSame(600, $coupon->discountFor(Money::fromCents(10000))->cents());
    }

    public function testPercentageCouponAcceptsAFraction(): void
    {
        $coupon = Coupon::percentage('P7H', 7.5);

        self::assertSame(750, $coupon->discountFor(Money::fromCents(10000))->cents());
    }

    public function testFixedCouponNeverExceedsThePrice(): void
    {
        $coupon = Coupon::fixed('D15', Money::fromCents(1500));

        self::assertSame(1000, $coupon->discountFor(Money::fromCents(1000))->cents());
    }

    public function testFullPercentageCouponTakesOffThePriceExactly(): void
    {
        $coupon = Coupon::percentage('P100', 100.0);

        self::assertSame(10000, $coupon->discountFor(Money::fromCents(10000))->cents());
    }
}
