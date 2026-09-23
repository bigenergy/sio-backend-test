<?php

declare(strict_types=1);

namespace App\Validator;

use App\Repository\ProductRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class ExistingProductValidator extends ConstraintValidator
{
    public function __construct(
        private readonly ProductRepository $products,
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof ExistingProduct) {
            throw new UnexpectedTypeException($constraint, ExistingProduct::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!\is_int($value) || null !== $this->products->find($value)) {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->setParameter('{{ value }}', (string) $value)
            ->addViolation();
    }
}
