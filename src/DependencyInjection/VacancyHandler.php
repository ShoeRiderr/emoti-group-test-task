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

    public function create(array $data): string
    {
        $message = 'Vacancy with given date alredy exists. Record with choosen date is updated successfully.';
        $date = new DateTimeImmutable($data['date'] ?? '');
        $free = $data['free'] ?? '';
        $price = $data['price'] ?? '';

        /**
         * @var ?Vacancy $vacancy
         */
        $vacancy = $this->manager->getRepository(Vacancy::class)
            ->findByOne(['date' => $data['date'] ?? '']);

        if (!$vacancy) {
            $message = 'Vacancy created successfully.';
            $vacancy = new Vacancy();
        }

        $vacancy->setDate($date);
        $vacancy->setFree($free);
        $vacancy->setPrice($price);

        $this->manager->persist($vacancy);
        $this->manager->flush();

        return $message;
    }
}
