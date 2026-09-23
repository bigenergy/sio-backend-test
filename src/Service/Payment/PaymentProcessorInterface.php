<?php

declare(strict_types=1);

namespace App\Service\Payment;

use App\Exception\PaymentFailed;
use App\ValueObject\Money;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * The contract the application charges through, hiding the fact that the
 * vendor processors disagree on both the unit of the amount and the way a
 * refusal is reported.
 *
 * Adding a processor means one class implementing this interface, named with
 * #[AsTaggedItem]. The registry and the request validation both pick it up
 * from the container, so neither has to change.
 */
#[AutoconfigureTag('app.payment_processor')]
interface PaymentProcessorInterface
{
    /**
     * @throws PaymentFailed when the processor refuses the payment
     */
    public function pay(Money $amount): void;
}
