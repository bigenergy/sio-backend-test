<?php

declare(strict_types=1);

namespace App\Exception;

/**
 * Raised when a request names something the shop cannot price.
 *
 * Request validation normally rejects these cases first; the services check
 * again because a product or coupon can disappear between the two, and because
 * a service must hold on its own when it is called from somewhere else.
 */
abstract class PricingException extends \RuntimeException
{
    /**
     * Request field the failure belongs to, used to report it like a
     * validation error.
     */
    abstract public function field(): string;
}
