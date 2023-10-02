<?php

namespace App\DataFixtures;

use App\Entity\Vacancy;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class VacancyFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $currentDate = new DateTimeImmutable();

        for ($i = 1; $i <= 50; $i++) {
            $date = $currentDate;
            $newDate = $date->modify(sprintf("+%d day", $i));

            $vacancy = new Vacancy();
            $vacancy->setDate($newDate);
            $vacancy->setFree(rand(15, 50));
            $vacancy->setPrice(rand(10, 2000));
            $vacancy->setUpdatedAt($currentDate);
            $vacancy->setCreatedAt($currentDate);

            $manager->persist($vacancy);
        }

        $manager->flush();
    }
}
