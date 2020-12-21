<?php

namespace App\DataFixtures;

use App\Entity\Episode;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker;
use Faker\Factory;

class EpisodeFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');
        for ($i = 0; $i < 100; $i++) {
            $episode = new Episode();
            $episode->setSeasonId($this->getReference('season_'.rand(1,19)));
            $episode->setNumber($faker->randomDigitNotNull);
            $episode->setTitle($faker->sentence(6, true));
            $episode->setSynopsis($faker->text(300));
            $manager->persist($episode);
        }

        $manager->flush();

    }

    public function getDependencies()
    {
        return [SeasonFixtures::class];
    }
}