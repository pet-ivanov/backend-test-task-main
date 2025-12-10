<?php

namespace App\DataFixtures;

use App\Entity\Coupon;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CouponFixtures extends Fixture
{
    const COUPONS = [
        ['SALE6PERCENT', 10, true],
        ['SALE6AMOUNT', 10, false]
    ];

    public function load(ObjectManager $manager): void
    {
        foreach(self::COUPONS as [$code, $discountAmount, $isPercentageDiscount]) {
            $coupon = new Coupon($code, $discountAmount, $isPercentageDiscount);
            $manager->persist($coupon);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [];
    }
}
