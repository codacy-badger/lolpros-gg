<?php

namespace App\DataFixtures;

use App\Entity\Profile\Profile;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProfileFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $profile = new Profile();
        $profile->setName('Don Arts');
        $profile->setCountry('DE');
        $manager->persist($profile);

        $manager->flush();
    }
}
