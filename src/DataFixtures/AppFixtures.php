<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Geo;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\User;
use App\Entity\Personna;
use App\Entity\PointOfInterest;
use App\Entity\TypePointOfInterest;
use App\Entity\Info;
use App\Entity\TypeInfo;
use App\Entity\Save;
use App\Entity\DownloadedFiles;
use App\Entity\Essential;

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
        $allPersonna = [];
        $allPointOfInterest = [];
        $allGeo = [];
        $allTypeInfo = [];

        $publicUser = new User();
        $password = $this->faker->password(2, 6);
        $publicUser
            ->setUsername($this->faker->userName() . "@" . $password)
            ->setPassword($this->userPasswordHasher->hashPassword($publicUser, $password))
            ->setRoles(["ROLE_PUBLIC"])
            ->setUuid($this->faker->uuid());
        $manager->persist($publicUser);

        $allUser = [];
        for ($i = 0; $i < 10; $i++) {
            $user = new User();
            $password = $this->faker->password(2, 6);
            $user
                ->setUsername($this->faker->userName() . "@" . $password)
                ->setPassword($this->userPasswordHasher->hashPassword($user, $password))
                ->setRoles(["ROLE_USER"])
                ->setUuid($this->faker->uuid());
            $manager->persist($user);
            array_push($allUser ,$user);
        }

        $adminUser = new User();
        $adminUser
            ->setUsername("admin")
            ->setPassword($this->userPasswordHasher->hashPassword($adminUser, "password"))
            ->setRoles(["ROLE_ADMIN"])
            ->setUuid($this->faker->uuid());
        $manager->persist($adminUser);


        for ($i = 0; $i < 100; $i++) {

            // Create Geo entities
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
            array_push($allGeo, $geo);

            // Create PointOfInterest entities
            $pointOfInterest = new PointOfInterest();
            $pointOfInterest->setDescription($this->faker->text());
            $pointOfInterest->setLink($this->faker->url());
            $pointOfInterest->setPrice($this->faker->randomFloat(2, 0, 100));
            $pointOfInterest->setUpdatedAt(new \DateTime());
            $pointOfInterest->setCreatedAt(new \DateTime());
            $pointOfInterest->setIdGeo($geo);
            $pointOfInterest->setStatus('on');
            array_push($allPointOfInterest, $pointOfInterest);

            // Create TypePointOfInterest entities and associate with PointOfInterest
            $typePointOfInterest = new TypePointOfInterest();
            $typePointOfInterest->setType($this->faker->word());
            $typePointOfInterest->setStatus('on');
            $manager->persist($typePointOfInterest);
            $pointOfInterest->addIdIType($typePointOfInterest);
            $manager->persist($pointOfInterest);

            // Create TypeInfo entities and associate with Info
            $typeInfo = new TypeInfo();
            $typeInfo->setType($this->faker->word());
            $typeInfo->setStatus('on');
            $manager->persist($typeInfo);
            array_push($allTypeInfo, $typeInfo);

            // Create Info entities
            $info = new Info();
            $info->setDescription($this->faker->text());
            $info->setUpdatedAt(new \DateTime());
            $info->setCreatedAt(new \DateTime());
            $info->setStatus('on');
            $typeInfo = $allTypeInfo[array_rand($allTypeInfo)];
            $info->setIdTypeInfo($typeInfo);
            $geo = $allGeo[array_rand($allGeo)];
            $info->addIdGeo($geo);
            $manager->persist($info);

            $save = new Save();
            $numPointOfInterestsToAssociate = random_int(0, count($allPointOfInterest) -1);
            for ($j = 0; $j < $numPointOfInterestsToAssociate; $j++) {
                $pointOfInterest = $allPointOfInterest[array_rand($allPointOfInterest)];
                $save->addIdPointOfInterest($pointOfInterest);
            }
            $save->setIdGeo($geo);
            $manager->persist($save);

            $essential = new Essential();
            $essential->setTitle($faker->word());
            $essential->setDescription($faker->paragraph(2));
            $essential->setCreatedAt(new \DateTime());
            $essential->setUpdatedAt(new \DateTime());
            $essential->setStatus('on');

            $numGeo = random_int(0, count($allGeo) - 1);
            for ($j = 0; $j < $numGeo; $j++) {
                $essential->addIdGeo($allGeo[array_rand($allGeo)]);
            }
            $manager->persist($essential);

            // Create Personna entities
            $personna = new Personna();
            $personna->setName($this->faker->name());
            $personna->setFirstName($this->faker->firstName());
            $personna->setEmail($this->faker->email());
            $personna->setPhone($this->faker->phoneNumber());
            $personna->setGender($this->faker->randomElement(['male', 'female']));
            $personna->setBirthday($this->faker->dateTimeBetween('-30 years', '-18 years'));
            $personna->setAddress($this->faker->address());
            array_push($allPersonna, $personna);

            // Create DownloadedFiles entities
            $image = $faker->filePath();
            $downloadedFiles = new DownloadedFiles();
            $downloadedFiles->setRealName($faker->word());
            $downloadedFiles->setUpdatedAt(new \DateTime());
            $downloadedFiles->setCreatedAt(new \DateTime());
            $downloadedFiles->setRealPath($image);
            $downloadedFiles->setMimeType($faker->mimeType());
            $downloadedFiles->setName($faker->word());
            $downloadedFiles->setPublicPath("/public/medias/pictures");
            $downloadedFiles->setStatus('on');
            $manager->persist($downloadedFiles);

            // Associate Personna with PointOfInterest
            $pointOfInterest = $allPointOfInterest[array_rand($allPointOfInterest)];
            $personna->setPointOfInterest($pointOfInterest);
            $pointOfInterest->addPersonna($personna);

            // Associate User and Personna entities
            $user = $allUser[array_rand($allUser)];
            $personna->setUsers($user);
            $user->addPersonna($personna);
            $manager->persist($personna);
        }
        $manager->flush();
    }
}
