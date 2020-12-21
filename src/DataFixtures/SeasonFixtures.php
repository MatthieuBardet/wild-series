<?php

namespace App\DataFixtures;

use App\Entity\Season;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker;
use Faker\Factory;

class SeasonFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');
        for ($i = 0; $i < 50; $i++) {
            $season = new Season();
            $season->setProgramId($this->getReference('program_'.rand(0,5)));
            $season->setNumber($faker->randomDigitNotNull);
            $season->setYear($faker->year('now'));
            $season->setDescription($faker->text(500));
            $manager->persist($season);
            $this->addReference('season_' . $i, $season);
        }

        $manager->flush();

    }

    public function getDependencies()
    {
        return [ProgramFixtures::class];
    }
}