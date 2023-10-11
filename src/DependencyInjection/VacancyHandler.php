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

    /**
     * @param Vacancy[]|array $data
     */
    public function getByDateRangeAndFreePlaces(
        $data,
        int $bookedPlaces = 1
    ): array {
        if (!$this->validateIfArrayElementsAreTypeOfVacancy($data)) {
            $startDate = new DateTimeImmutable($data['startDate'] ?? '');
            $endDate = new DateTimeImmutable($data['endDate'] ?? '');
            $bookedPlaces = $data['bookedPlaces'] ?? 1;

            /**
             * @var Vacancy[] $vacancies
             */
            $data = $this->manager->getRepository(Vacancy::class)
                ->findByDateRange($startDate, $endDate)
                ->getResult();
        }

        foreach ($data as $vacancy) {
            if ($vacancy->getFree() < $bookedPlaces) {
                return [];
            }
        }

        return $data;
    }

    private function validateIfArrayElementsAreTypeOfVacancy(array $data): bool
    {
        $result = array_map(
            function ($item) {
                if (!($item instanceof Vacancy)) {
                    return 0;
                }

                return 1;
            },
            $data
        );

        $result = array_values($result);

        // Check if all values inside the result have the same value
        // This means that all values are instance of Vacancy entity
        return count(array_flip($result)) === 1 && end($result) === 1;
    }
}
