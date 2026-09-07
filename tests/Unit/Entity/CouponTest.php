<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Coupon;
use App\Enum\CouponType;
use PHPUnit\Framework\TestCase;

final class CouponTest extends TestCase
{
    public function testFixedCouponTakesOffItsFaceValue(): void
    {
        $coupon = new Coupon('D15', CouponType::Fixed, 1500);

        self::assertSame(1500, $coupon->discountFor(10000));
    }

    public function testPercentageCouponTakesOffAShareOfThePrice(): void
    {
        $coupon = new Coupon('P6', CouponType::Percentage, 6);

        self::assertSame(600, $coupon->discountFor(10000));
    }

    public function testPercentageDiscountIsRoundedToWholeCents(): void
    {
        $coupon = new Coupon('P6', CouponType::Percentage, 6);

        // 6% of 19.99 EUR is 1.1994 EUR.
        self::assertSame(120, $coupon->discountFor(1999));
    }

    public function testFixedCouponNeverExceedsThePrice(): void
    {
        $coupon = new Coupon('D15', CouponType::Fixed, 1500);

        self::assertSame(1000, $coupon->discountFor(1000));
    }

    public function testFullPercentageCouponTakesOffThePriceExactly(): void
    {
        $coupon = new Coupon('P100', CouponType::Percentage, 100);

        self::assertSame(10000, $coupon->discountFor(10000));
    }
}
