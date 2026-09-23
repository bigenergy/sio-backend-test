<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Coupon;
use App\Entity\Product;
use App\Entity\TaxRate;
use App\Enum\Country;
use App\ValueObject\Money;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Seed data: the three products from the task, the VAT rates, and a handful of
 * coupons.
 *
 * Coupon codes say their own type: "P<n>" takes n percent off, "D<n>" takes a
 * flat n euros off.
 */
class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Inserted first, so this is product 1 — the id used in requests.http.
        $manager->persist(new Product('Iphone', Money::fromCents(10000)));
        $manager->persist(new Product('Наушники', Money::fromCents(2000)));
        $manager->persist(new Product('Чехол', Money::fromCents(1000)));

        $manager->persist(new TaxRate(Country::Germany, 19.0));
        $manager->persist(new TaxRate(Country::Italy, 22.0));
        $manager->persist(new TaxRate(Country::France, 20.0));
        $manager->persist(new TaxRate(Country::Greece, 24.0));

        $manager->persist(Coupon::percentage('P6', 6.0));
        $manager->persist(Coupon::percentage('P10', 10.0));
        $manager->persist(Coupon::percentage('P100', 100.0));
        // Fractional percentages are representable, which a single shared
        // value column could not have held.
        $manager->persist(Coupon::percentage('P7H', 7.5));
        $manager->persist(Coupon::fixed('D15', Money::fromCents(1500)));
        $manager->persist(Coupon::fixed('D5', Money::fromCents(500)));

        $manager->flush();
    }
}
