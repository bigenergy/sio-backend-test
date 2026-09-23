<?php

declare(strict_types=1);

namespace App\Enum;

/**
 * Countries the shop bills, identified by the layout of their tax number.
 *
 * Only the format lives here: it is a parsing rule, fixed by the country. The
 * rate charged is data and lives in the tax_rate table, so it can change
 * without a release.
 */
enum Country: string
{
    case Germany = 'DE';
    case Italy = 'IT';
    case France = 'FR';
    case Greece = 'GR';

    /**
     * A two-letter country prefix followed by a country-specific number of
     * digits. France additionally carries a two-letter block in between.
     *
     * Letters must be uppercase, matching how EU VAT identifiers are written.
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
     * The country a tax number belongs to, or null when it matches no known
     * country's format.
     */
    public static function tryFromTaxNumber(string $taxNumber): ?self
    {
        $country = self::tryFrom(substr($taxNumber, 0, 2));

        if (null === $country || 1 !== preg_match($country->taxNumberPattern(), $taxNumber)) {
            return null;
        }

        return $country;
    }
}
