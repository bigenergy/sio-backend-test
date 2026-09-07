<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_PARAMETER)]
final class TaxNumber extends Constraint
{
    public string $message = 'The tax number "{{ value }}" does not match the format of any supported country.';
}
