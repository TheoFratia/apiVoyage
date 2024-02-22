<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Geo;
use Faker\Factory;
use Faker\Generator;

class AppFixtures extends Fixture
{
    /**
     * @var Generator
     */
    private Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create('fr_FR');
    }
    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < 10; $i++) { // Change 10 to the number of entities you want to create
            $geo = new Geo();
            $faker = Factory::create('fr_FR');
            $geo->setCity($faker->city);
            $geo->setCountry($faker->country);
            $geo->setAddress($faker->address);
            $geo->setLongitude($faker->longitude);
            $geo->setLatitude($faker->latitude);
            $geo->setUpdatedAt(new \DateTime());
            $geo->setCreatedAt(new \DateTime());
            $geo->setStatus('active');

            $manager->persist($geo);
        }

        $manager->flush();
    }
}

