<?php

declare(strict_types=1);

namespace App\Payment;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * The contract the application charges through, hiding the fact that the
 * vendor processors disagree on both the unit of the amount and the way a
 * refusal is reported.
 *
 * Adding a processor means adding one class implementing this interface: the
 * attribute below tags it automatically, which is enough for the registry to
 * pick it up and for the request validation to start accepting its name.
 */
#[AutoconfigureTag]
interface PaymentProcessorInterface
{
    /**
     * Value accepted in the "paymentProcessor" field of a purchase request.
     */
    public function getName(): string;

    /**
     * @param int $priceInCents amount to charge
     *
     * @throws PaymentFailedException when the processor refuses the payment
     */
    public function pay(int $priceInCents): void;
}
