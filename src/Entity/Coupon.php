<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\CouponType;
use App\Repository\CouponRepository;
use App\ValueObject\Money;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * A discount the seller issued.
 *
 * Each type has its own column rather than sharing one whose meaning depends
 * on the type, which also lets a percentage carry decimals.
 */
#[ORM\Entity(repositoryClass: CouponRepository::class)]
#[ORM\UniqueConstraint(name: 'uniq_coupon_code', columns: ['code'])]
class Coupon
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 32)]
    private string $code;

    #[ORM\Column(length: 16, enumType: CouponType::class)]
    private CouponType $type;

    #[ORM\Column(nullable: true)]
    private ?int $amountInCents = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    private ?string $percentage = null;

    private function __construct(string $code, CouponType $type)
    {
        $this->code = $code;
        $this->type = $type;
    }

    public static function fixed(string $code, Money $amount): self
    {
        $coupon = new self($code, CouponType::Fixed);
        $coupon->amountInCents = $amount->cents();

        return $coupon;
    }

    public static function percentage(string $code, float $percent): self
    {
        $coupon = new self($code, CouponType::Percentage);
        $coupon->percentage = (string) $percent;

        return $coupon;
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

    /**
     * What this coupon takes off the given price.
     *
     * Capped at the price itself, so a 100% or oversized coupon brings the
     * product down to zero rather than into negative territory.
     */
    public function discountFor(Money $price): Money
    {
        $discount = match ($this->type) {
            CouponType::Fixed => Money::fromCents(
                $this->amountInCents ?? throw new \LogicException('A fixed coupon carries no amount.'),
            ),
            CouponType::Percentage => $price->percentage(
                (float) ($this->percentage ?? throw new \LogicException('A percentage coupon carries no percentage.')),
            ),
        };

        return $discount->atMost($price);
    }
}
