<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_PARAMETER)]
final class SupportedPaymentProcessor extends Constraint
{
    public string $message = 'Payment processor "{{ value }}" is not supported. Available: {{ available }}.';
}
