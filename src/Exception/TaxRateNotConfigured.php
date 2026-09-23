<?php

declare(strict_types=1);

namespace App\Exception;

use App\Enum\Country;

/**
 * A country the shop bills has no rate in the database. That is a broken
 * install rather than a bad request, so it is deliberately not turned into a
 * client error.
 */
final class TaxRateNotConfigured extends \RuntimeException
{
    public static function forCountry(Country $country): self
    {
        return new self(sprintf('No tax rate is configured for "%s".', $country->value));
    }
}
