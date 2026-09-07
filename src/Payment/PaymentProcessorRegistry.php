<?php

declare(strict_types=1);

namespace App\Payment;

use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

/**
 * Looks up a processor by the name used in the API payload.
 *
 * The list is built from the service container, so a newly added processor is
 * available here — and in the validation that guards this lookup — without
 * this class changing.
 */
final class PaymentProcessorRegistry
{
    /**
     * @var array<string, PaymentProcessorInterface>
     */
    private array $processors = [];

    /**
     * @param iterable<PaymentProcessorInterface> $processors
     */
    public function __construct(
        #[AutowireIterator(PaymentProcessorInterface::class)]
        iterable $processors,
    ) {
        foreach ($processors as $processor) {
            $this->processors[$processor->getName()] = $processor;
        }
    }

    public function has(string $name): bool
    {
        return isset($this->processors[$name]);
    }

    public function get(string $name): PaymentProcessorInterface
    {
        return $this->processors[$name]
            ?? throw new \InvalidArgumentException(sprintf('Unknown payment processor "%s".', $name));
    }

    /**
     * @return list<string> names of every processor the API accepts
     */
    public function names(): array
    {
        return array_keys($this->processors);
    }
}
