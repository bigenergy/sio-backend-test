<?php

declare(strict_types=1);

namespace App\Price;

/**
 * The application keeps every amount in cents; this is the single place where
 * they turn back into the euros that go out over the API.
 */
final class Money
{
    public const CURRENCY = 'EUR';

    public static function centsToEuros(int $cents): float
    {
        return round($cents / 100, 2);
    }
}
