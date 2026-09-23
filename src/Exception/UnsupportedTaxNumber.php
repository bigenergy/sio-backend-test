<?php

declare(strict_types=1);

namespace App\Exception;

final class UnsupportedTaxNumber extends PricingException
{
    public static function forNumber(string $taxNumber): self
    {
        return new self(sprintf('The tax number "%s" matches no supported country.', $taxNumber));
    }

    public function field(): string
    {
        return 'taxNumber';
    }
}
