<?php

declare(strict_types=1);

namespace App\Tests\Unit\ValueObject;

use App\ValueObject\Money;
use PHPUnit\Framework\TestCase;

final class MoneyTest extends TestCase
{
    public function testAddsAndSubtracts(): void
    {
        $ten = Money::fromCents(1000);
        $three = Money::fromCents(300);

        self::assertSame(1300, $ten->plus($three)->cents());
        self::assertSame(700, $ten->minus($three)->cents());
    }

    public function testIsImmutable(): void
    {
        $ten = Money::fromCents(1000);
        $ten->plus(Money::fromCents(500));

        self::assertSame(1000, $ten->cents());
    }

    public function testTakesAWholePercentage(): void
    {
        self::assertSame(600, Money::fromCents(10000)->percentage(6.0)->cents());
    }

    public function testTakesAFractionalPercentage(): void
    {
        // 7.5% of 100.00 EUR is exactly 7.50 EUR.
        self::assertSame(750, Money::fromCents(10000)->percentage(7.5)->cents());
    }

    public function testRoundsAPercentageToWholeCents(): void
    {
        // 6% of 19.99 EUR is 1.1994 EUR.
        self::assertSame(120, Money::fromCents(1999)->percentage(6.0)->cents());
    }

    public function testCapsAtACeiling(): void
    {
        self::assertSame(1000, Money::fromCents(1500)->atMost(Money::fromCents(1000))->cents());
        self::assertSame(800, Money::fromCents(800)->atMost(Money::fromCents(1000))->cents());
    }

    public function testConvertsToEuros(): void
    {
        self::assertSame(116.56, Money::fromCents(11656)->toEuros());
        self::assertSame(124.0, Money::fromCents(12400)->toEuros());
    }

    public function testRendersWithTwoDecimals(): void
    {
        self::assertSame('124.00', (string) Money::fromCents(12400));
        self::assertSame('0.05', (string) Money::fromCents(5));
    }

    public function testComparesByAmount(): void
    {
        self::assertTrue(Money::fromCents(500)->equals(Money::fromCents(500)));
        self::assertFalse(Money::fromCents(500)->equals(Money::zero()));
    }
}
