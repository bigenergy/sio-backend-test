<?php

declare(strict_types=1);

namespace App\Service\Payment;

use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Contracts\Service\ServiceProviderInterface;

/**
 * Resolves a processor by the name used in the API payload.
 *
 * A locator rather than an iterator: only the processor actually asked for is
 * instantiated, while the names of all of them stay readable for the
 * validation that guards this lookup.
 */
final class PaymentProcessorRegistry
{
    /**
     * @param ServiceProviderInterface<PaymentProcessorInterface> $processors
     */
    public function __construct(
        #[AutowireLocator('app.payment_processor')]
        private readonly ServiceProviderInterface $processors,
    ) {
    }

    public function has(string $name): bool
    {
        return $this->processors->has($name);
    }

    public function get(string $name): PaymentProcessorInterface
    {
        if (!$this->processors->has($name)) {
            throw new \InvalidArgumentException(sprintf('Unknown payment processor "%s".', $name));
        }

        return $this->processors->get($name);
    }

    /**
     * @return list<string> every name the API accepts
     */
    public function names(): array
    {
        return array_keys($this->processors->getProvidedServices());
    }
}
