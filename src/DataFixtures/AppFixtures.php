<?php

namespace App\DataFixtures;

use App\Factory\DeckFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {

        DeckFactory::createMany(30);

        $manager->flush();
    }
}
