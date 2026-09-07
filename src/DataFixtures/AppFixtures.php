<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Coupon;
use App\Entity\Product;
use App\Enum\CouponType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Seed data: the three products from the task plus a handful of coupons.
 *
 * Coupon codes follow a deliberate convention so the type is readable from the
 * code alone: "P<n>" takes n percent off, "D<n>" takes a flat n euros off.
 */
class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Inserted first, so this is product 1 — the id used in requests.http.
        $manager->persist(new Product('Iphone', 10000));
        $manager->persist(new Product('Наушники', 2000));
        $manager->persist(new Product('Чехол', 1000));

        $manager->persist(new Coupon('P6', CouponType::Percentage, 6));
        $manager->persist(new Coupon('P10', CouponType::Percentage, 10));
        $manager->persist(new Coupon('P100', CouponType::Percentage, 100));
        $manager->persist(new Coupon('D15', CouponType::Fixed, 1500));
        $manager->persist(new Coupon('D5', CouponType::Fixed, 500));

        $manager->flush();
    }
}
