<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\CouponType;
use App\Repository\CouponRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CouponRepository::class)]
#[ORM\UniqueConstraint(name: 'uniq_coupon_code', columns: ['code'])]
class Coupon
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    public function __construct(
        #[ORM\Column(length: 32)]
        private string $code,

        #[ORM\Column(length: 16, enumType: CouponType::class)]
        private CouponType $type,

        /**
         * Cents for a fixed coupon, whole percent for a percentage one.
         *
         * A single column is the simplest thing that covers both types. Splitting
         * it into two nullable columns, or into one subclass per type, would only
         * start paying off once coupons grew more rules than "how much off".
         */
        #[ORM\Column]
        private int $value,
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getType(): CouponType
    {
        return $this->type;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    /**
     * Discount this coupon grants on the given price, in cents.
     *
     * Capped at the price itself, so a 100% (or oversized fixed) coupon brings
     * the product down to zero rather than into negative territory.
     */
    public function discountFor(int $priceInCents): int
    {
        $discount = match ($this->type) {
            CouponType::Fixed => $this->value,
            CouponType::Percentage => (int) round($priceInCents * $this->value / 100),
        };

        return min($discount, $priceInCents);
    }
}
