<?php

declare(strict_types=1);

namespace App\Validator;

use App\Payment\PaymentProcessorRegistry;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Checks the requested processor against the registry rather than a hardcoded
 * list, so registering a new processor widens what this constraint accepts —
 * and what its error message advertises — on its own.
 */
final class SupportedPaymentProcessorValidator extends ConstraintValidator
{
    public function __construct(
        private readonly PaymentProcessorRegistry $processors,
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof SupportedPaymentProcessor) {
            throw new UnexpectedTypeException($constraint, SupportedPaymentProcessor::class);
        }

        if ($value === null || $value === '') {
            return;
        }

        if (!\is_string($value) || $this->processors->has($value)) {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->setParameter('{{ value }}', $value)
            ->setParameter('{{ available }}', implode(', ', $this->processors->names()))
            ->addViolation();
    }
}
