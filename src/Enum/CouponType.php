<?php

declare(strict_types=1);

namespace App\Enum;

enum CouponType: string
{
    /**
     * Discount of a fixed amount, stored in cents.
     */
    case Fixed = 'fixed';

    /**
     * Discount of a share of the price, stored in whole percent.
     */
    case Percentage = 'percentage';
}
