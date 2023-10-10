<?php

namespace App\DependencyInjection;

use App\Entity\Reservation;
use App\Entity\User;
use App\Entity\Vacancy;
use App\Exceptions\NotEnoughVacanciesException;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;

class ReservationHandler
{
    private int $price;

    public function __construct(
        private EntityManagerInterface $manager,
        private LoggerInterface $logger
    ) {
        $this->price = 0;
    }

    /**
     * @throw Exception
     */
    public function reservate(?User $user, array $data): ?array
    {
        $startDate = new DateTimeImmutable($data['start_date'] ?? '');
        $endDate = new DateTimeImmutable($data['end_date'] ?? '');
        $bookedPlaces = $data['booked_places'] ?? 1;

        /**
         * @var Vacancy[] $vacancies
         */
        $vacancies = $this->manager->getRepository(Vacancy::class)
            ->findByDateRangeAndAvailableFreePlaces($startDate, $endDate, $bookedPlaces)
            ->getResult();

        if (!$vacancies && empty($vacancies)) {
            throw new NotEnoughVacanciesException('Not enough available vacancies or no vacancies in given date range.', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (!$user) {
            if (!isset($data['email'])) {
                return null;
            }

            $email = $data['email'];

            /**
             * @var ?User $user
             */
            $user = $this->manager->getRepository(User::class)
                ->findOneBy(['email' => $email]);

            if (!$user) {
                $user = new User();
                $user->setEmail($email);
                $user->setName($data['name'] ?? '');
                $this->manager->persist($user);
            }
        }

        $currentDate = new DateTimeImmutable();

        $reservation = new Reservation();
        $reservation->setUser($user);
        $reservation->setStartDate($startDate);
        $reservation->setEndDate($endDate);
        $reservation->setCreatedAt($currentDate);
        $reservation->setBookedPlaces($bookedPlaces);
        $reservation->setUpdatedAt($currentDate);
        $price = $this->handleVacanciesAfterBookReservation($vacancies, $bookedPlaces)
            ->getPrice();

        $reservation->setPrice($price);
        $this->manager->persist($reservation);

        $this->manager->flush();

        return $reservation->toArray();
    }

    /**
     * @param Vacancy[] $vacancies
     */
    public function handleVacanciesAfterBookReservation(
        $vacancies,
        int $bookedPlaces = 1
    ): self {
        $price = 0;

        foreach ($vacancies as $vacancy) {
            $free = $vacancy->getFree() - $bookedPlaces;
            $price += $vacancy->getPrice() * $bookedPlaces;
            $vacancy->setFree($free);
            $this->manager->persist($vacancy);
        }

        $this->manager->flush();
        $this->price = $price;

        return $this;
    }

    public function getPrice(): int
    {
        $result = $this->price;
        $this->price = 0;

        return $result;
    }

    public function cancel(Reservation $reservation): bool
    {
        try {
            $bookedPlaces = $reservation->getBookedPlaces();

            /**
             * @var Vacancy[] $vacancies
             */
            $vacancies = $this->manager->getRepository(Vacancy::class)
                ->findByDateRange($reservation->getStartDate(), $reservation->getEndDate())
                ->getResult();

            foreach ($vacancies as $vacancy) {
                $currentAvailableSlots = $vacancy->getFree();
                $vacancy->setFree($currentAvailableSlots + $bookedPlaces);
                $this->manager->persist($vacancy);
            }

            $this->manager->remove($reservation);
            $this->manager->flush();
            return true;
        } catch (\Throwable $e) {
            $this->logger->error($e);

            return false;
        }
    }
}
