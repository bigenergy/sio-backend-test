<?php

declare(strict_types=1);

namespace App\Payment;

/**
 * Raised when a processor refuses a payment, whichever way that processor
 * happens to signal it.
 */
final class PaymentFailedException extends \RuntimeException
{
}
