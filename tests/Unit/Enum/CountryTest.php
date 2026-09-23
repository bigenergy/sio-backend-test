<?php

declare(strict_types=1);

namespace App\Tests\Unit\Enum;

use App\Enum\Country;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CountryTest extends TestCase
{
    #[DataProvider('validTaxNumbers')]
    public function testResolvesCountryFromTaxNumber(string $taxNumber, Country $expected): void
    {
        self::assertSame($expected, Country::tryFromTaxNumber($taxNumber));
    }

    /**
     * @return iterable<string, array{string, Country}>
     */
    public static function validTaxNumbers(): iterable
    {
        yield 'Germany, nine digits' => ['DE123456789', Country::Germany];
        yield 'Italy, eleven digits' => ['IT12345678900', Country::Italy];
        yield 'Greece, nine digits' => ['GR123456789', Country::Greece];
        yield 'France, two letters then nine digits' => ['FRAB123456789', Country::France];
    }

    #[DataProvider('invalidTaxNumbers')]
    public function testRejectsMalformedTaxNumber(string $taxNumber): void
    {
        self::assertNull(Country::tryFromTaxNumber($taxNumber));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function invalidTaxNumbers(): iterable
    {
        yield 'empty' => [''];
        yield 'prefix only' => ['DE'];
        yield 'unsupported country' => ['XX123456789'];
        yield 'Germany, one digit short' => ['DE12345678'];
        yield 'Germany, one digit too many' => ['DE1234567890'];
        yield 'Italy with the German length' => ['IT123456789'];
        yield 'France without its letter block' => ['FR123456789'];
        yield 'France with digits where letters belong' => ['FR12123456789'];
        yield 'lowercase country prefix' => ['de123456789'];
        yield 'lowercase French letter block' => ['FRab123456789'];
        yield 'letter among the digits' => ['GR12345678A'];
        yield 'trailing whitespace' => ['DE123456789 '];
    }
}
