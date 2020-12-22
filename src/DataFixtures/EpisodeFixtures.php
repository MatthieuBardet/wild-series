<?php

namespace App\DataFixtures;

use App\Entity\Episode;
use App\Service\Slugify;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker;
use Faker\Factory;

class EpisodeFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * @var Slugify
     */
    private $slug;

    public function __construct(Slugify $slug)
    {
        $this->slug = $slug;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');
        for ($i = 0; $i < 1000; $i++) {
            $episodeTitle = $faker->sentence(6, true);
            $episode = new Episode();
            $episode->setSeasonId($this->getReference('season_' . rand(1, 19)));
            $episode->setNumber($faker->randomDigitNotNull);
            $episode->setTitle($episodeTitle);
            $episode->setSynopsis($faker->text(300));
            $episode->setSlug($this->slug->generate($episodeTitle));
            $manager->persist($episode);
        }

        $manager->flush();

    }

    public function getDependencies()
    {
        return [SeasonFixtures::class];
    }
}