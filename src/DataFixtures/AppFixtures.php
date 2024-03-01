<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Geo;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\User;

class AppFixtures extends Fixture
{
    /**
     * @var Generator
     */
    private Generator $faker;

    /**
     * Password Hasher
     * @var UserPasswordHasherInterface
     */
    private UserPasswordHasherInterface $userPasswordHasher;
    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->faker = Factory::create('fr_FR');
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $publicUser = new User();
        $password = $this->faker->password(2,6);
        $publicUser
            ->setUsername($this->faker->userName()."@".$password)
            ->setPassword($this->userPasswordHasher->hashPassword($publicUser, $password))
            ->setRoles(["ROLE_PUBLIC"])
            ->setUuid($this->faker->uuid());
        $manager->persist($publicUser);

        for($i = 0; $i < 10; $i++){
            $user = new User();
            $password = $this->faker->password(2,6);
            $user
                ->setUsername($this->faker->userName()."@".$password)
                ->setPassword($this->userPasswordHasher->hashPassword($user, $password))
                ->setRoles(["ROLE_USER"])
                ->setUuid($this->faker->uuid());
            $manager->persist($user);
        }

        $adminUser = new User();
            $adminUser
                ->setUsername("admin")
                ->setPassword($this->userPasswordHasher->hashPassword($adminUser, "password"))
                ->setRoles(["ROLE_ADMIN"])
                ->setUuid($this->faker->uuid());
            $manager->persist($adminUser);
        

        for ($i = 0; $i < 100; $i++) { 
            $geo = new Geo();
            $faker = Factory::create('fr_FR');
            $geo->setCity($faker->city());
            $geo->setCountry($faker->country());
            $geo->setAddress($faker->address());
            $geo->setLongitude($faker->longitude());
            $geo->setLatitude($faker->latitude());
            $geo->setUpdatedAt(new \DateTime());
            $geo->setCreatedAt(new \DateTime());
            $geo->setStatus('on');

            $manager->persist($geo);
        }

        $manager->flush();
    }
}

