<?php

declare(strict_types=1);

namespace App\Enum;

/**
 * Countries the shop bills, together with the VAT rate applied on top of the
 * discounted price and the tax number layout that identifies each of them.
 *
 * The rates are part of the task's fixed specification, so they live in code
 * rather than in the database. Turning them into an entity would be the
 * natural next step if they ever had to be editable per country.
 */
enum Country: string
{
    case Germany = 'DE';
    case Italy = 'IT';
    case France = 'FR';
    case Greece = 'GR';

    /**
     * VAT rate in whole percent.
     */
    public function taxRatePercent(): int
    {
        return match ($this) {
            self::Germany => 19,
            self::Italy => 22,
            self::France => 20,
            self::Greece => 24,
        };
    }

    /**
     * Tax number layout: a two-letter country prefix followed by a
     * country-specific number of digits. France additionally carries a
     * two-letter block between the prefix and the digits.
     *
     * Letters are required to be uppercase, matching how EU VAT identifiers
     * are written; accepting lowercase would mean normalising the input first.
     */
    public function taxNumberPattern(): string
    {
        return match ($this) {
            self::Germany => '/^DE\d{9}$/',
            self::Italy => '/^IT\d{11}$/',
            self::France => '/^FR[A-Z]{2}\d{9}$/',
            self::Greece => '/^GR\d{9}$/',
        };
    }

    /**
     * Resolves the country a tax number belongs to, or null when the number
     * matches no known country's format.
     */
    public static function tryFromTaxNumber(string $taxNumber): ?self
    {
        $country = self::tryFrom(substr($taxNumber, 0, 2));

        if ($country === null || preg_match($country->taxNumberPattern(), $taxNumber) !== 1) {
            return null;
        }

        return $country;
    }
}
