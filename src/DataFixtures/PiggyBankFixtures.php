<?php


namespace App\DataFixtures;

use App\Entity\PiggyBank;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class PiggyBankFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $piggybank = new PiggyBank();

        $manager->persist($piggybank);
        $manager->flush();
    }

}