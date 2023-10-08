<?php

namespace App\DependencyInjection;

use App\Entity\Vacancy;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

class VacancyHandler
{
    public function __construct(private EntityManagerInterface $manager)
    {
    }

    public function create(array $data): ?Vacancy
    {
        $date = $data['date'];
        $free = $data['free'];
        $price = $data['price'];

        if (!isset($date) || !isset($free) || !isset($price)) {
            return null;
        }

        $currentDate = new DateTimeImmutable();
        $date = new DateTimeImmutable($date);

        /**
         * @var ?Vacancy $vacancy
         */
        $vacancy = $this->manager->getRepository(Vacancy::class)
            ->findOneBy(['date' => $date]);

        if (!$vacancy) {
            $vacancy = new Vacancy();
        }

        $vacancy->setDate($date);
        $vacancy->setFree($free);
        $vacancy->setPrice($price);
        $vacancy->setCreatedAt($currentDate);
        $vacancy->setUpdatedAt($currentDate);

        $this->manager->persist($vacancy);
        $this->manager->flush();

        return $vacancy;
    }
}
