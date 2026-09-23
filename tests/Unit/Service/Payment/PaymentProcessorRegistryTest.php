<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Payment;

use App\Service\Payment\PaymentProcessorRegistry;
use App\Service\Payment\PaypalPaymentAdapter;
use App\Service\Payment\StripePaymentAdapter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Systemeio\TestForCandidates\PaymentProcessor\PaypalPaymentProcessor;
use Systemeio\TestForCandidates\PaymentProcessor\StripePaymentProcessor;

final class PaymentProcessorRegistryTest extends TestCase
{
    private PaymentProcessorRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = new PaymentProcessorRegistry(new ServiceLocator([
            'paypal' => static fn () => new PaypalPaymentAdapter(new PaypalPaymentProcessor()),
            'stripe' => static fn () => new StripePaymentAdapter(new StripePaymentProcessor()),
        ]));
    }

    public function testResolvesAProcessorByName(): void
    {
        self::assertInstanceOf(PaypalPaymentAdapter::class, $this->registry->get('paypal'));
        self::assertInstanceOf(StripePaymentAdapter::class, $this->registry->get('stripe'));
    }

    public function testKnowsWhichNamesItAccepts(): void
    {
        self::assertTrue($this->registry->has('paypal'));
        self::assertFalse($this->registry->has('bitcoin'));
        self::assertEqualsCanonicalizing(['paypal', 'stripe'], $this->registry->names());
    }

    public function testRefusesToResolveAnUnknownName(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->registry->get('bitcoin');
    }

    public function testBuildsOnlyTheProcessorItIsAskedFor(): void
    {
        $built = [];
        $registry = new PaymentProcessorRegistry(new ServiceLocator([
            'paypal' => static function () use (&$built) {
                $built[] = 'paypal';

                return new PaypalPaymentAdapter(new PaypalPaymentProcessor());
            },
            'stripe' => static function () use (&$built) {
                $built[] = 'stripe';

                return new StripePaymentAdapter(new StripePaymentProcessor());
            },
        ]));

        $registry->names();
        $registry->get('paypal');

        self::assertSame(['paypal'], $built);
    }
}
