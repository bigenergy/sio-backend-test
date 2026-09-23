<?php

declare(strict_types=1);

namespace App\Service;

use App\Enum\Country;
use App\Exception\PricingException;
use App\Exception\UnsupportedTaxNumber;
use App\Repository\CouponRepository;
use App\Repository\ProductRepository;
use App\Repository\TaxRateRepository;
use App\ValueObject\PriceQuote;

/**
 * Turns the fields of a request into a priced quote: resolves the product, the
 * coupon and the country's tax rate, then hands them to the calculator.
 */
final class PriceQuoteService
{
    public function __construct(
        private readonly ProductRepository $products,
        private readonly CouponRepository $coupons,
        private readonly TaxRateRepository $taxRates,
        private readonly PriceCalculator $calculator,
    ) {
    }

    /**
     * @throws PricingException when the product, coupon or country is unknown
     */
    public function quote(int $productId, string $taxNumber, ?string $couponCode = null): PriceQuote
    {
        $country = Country::tryFromTaxNumber($taxNumber) ?? throw UnsupportedTaxNumber::forNumber($taxNumber);

        $product = $this->products->getById($productId);
        $coupon = null !== $couponCode ? $this->coupons->getByCode($couponCode) : null;
        $taxRate = $this->taxRates->getByCountry($country);

        return new PriceQuote(
            $product,
            $country,
            $this->calculator->calculate($product->getPrice(), $taxRate->getPercent(), $coupon),
        );
    }
}
