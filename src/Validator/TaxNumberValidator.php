<?php

declare(strict_types=1);

namespace App\Validator;

use App\Enum\Country;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

/**
 * Accepts a tax number only when it matches the format of one of the countries
 * the shop bills — which is also what tells the calculation which VAT rate to
 * apply, so an unknown format has to be rejected here rather than defaulted.
 */
final class TaxNumberValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof TaxNumber) {
            throw new UnexpectedTypeException($constraint, TaxNumber::class);
        }

        // Emptiness is NotBlank's job, not this constraint's.
        if ($value === null || $value === '') {
            return;
        }

        if (!\is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        if (Country::tryFromTaxNumber($value) !== null) {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->setParameter('{{ value }}', $value)
            ->addViolation();
    }
}
