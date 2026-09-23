<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Exception\CouponNotFound;
use App\Exception\ProductNotFound;
use App\Exception\UnsupportedTaxNumber;
use App\Service\PriceQuoteService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * The service holds on its own, without leaning on the request validation that
 * normally runs first. These are real checks, not assertions: they still fire
 * when the code is called from anywhere else, and in a production build.
 *
 * Requires the test database to be prepared; `make test` does that first.
 */
final class PriceQuoteServiceTest extends KernelTestCase
{
    private PriceQuoteService $quotes;

    protected function setUp(): void
    {
        self::bootKernel();

        $quotes = self::getContainer()->get(PriceQuoteService::class);
        self::assertInstanceOf(PriceQuoteService::class, $quotes);

        $this->quotes = $quotes;
    }

    public function testPricesAProductWithTheRateStoredForItsCountry(): void
    {
        $quote = $this->quotes->quote(1, 'GR123456789');

        self::assertSame('Iphone', $quote->product->getName());
        self::assertSame(24.0, $quote->breakdown->taxRatePercent);
        self::assertSame(12400, $quote->breakdown->total()->cents());
    }

    public function testAppliesAFractionalPercentageCoupon(): void
    {
        // 100 EUR less 7.5% is 92.50 EUR, plus the German 19% is 110.08 EUR.
        $quote = $this->quotes->quote(1, 'DE123456789', 'P7H');

        self::assertSame(750, $quote->breakdown->discount->cents());
        self::assertSame(11008, $quote->breakdown->total()->cents());
    }

    public function testRefusesAnUnknownProduct(): void
    {
        $this->expectException(ProductNotFound::class);

        $this->quotes->quote(999, 'DE123456789');
    }

    public function testRefusesAnUnknownCoupon(): void
    {
        $this->expectException(CouponNotFound::class);

        $this->quotes->quote(1, 'DE123456789', 'P50');
    }

    public function testRefusesATaxNumberOfNoSupportedCountry(): void
    {
        $this->expectException(UnsupportedTaxNumber::class);

        $this->quotes->quote(1, 'XX123456789');
    }
}
