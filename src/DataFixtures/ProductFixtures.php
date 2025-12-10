<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProductFixtures extends Fixture
{
    const PRODUCTS = [
        ['Iphone', '100'],
        ['Наушники', '20'],
        ['Чехол', '10']
    ];

    public function load(ObjectManager $manager): void
    {
        foreach(self::PRODUCTS as [$title, $price]) {
            $product = new Product();
            $product->setTitle($title);
            $product->setPrice($price);
            $manager->persist($product);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [];
    }
}
