<?php

declare(strict_types=1);

namespace App\Validator;

use App\Repository\CouponRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Coupons are created by the seller, so a code the seller never issued has to
 * be refused rather than silently ignored.
 */
final class ExistingCouponValidator extends ConstraintValidator
{
    public function __construct(
        private readonly CouponRepository $coupons,
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof ExistingCoupon) {
            throw new UnexpectedTypeException($constraint, ExistingCoupon::class);
        }

        // The coupon is optional; an absent one simply means no discount.
        if ($value === null || $value === '') {
            return;
        }

        if (!\is_string($value) || $this->coupons->findOneByCode($value) !== null) {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->setParameter('{{ value }}', $value)
            ->addViolation();
    }
}
