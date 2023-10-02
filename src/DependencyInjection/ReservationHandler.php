<?php

namespace App\DependencyInjection;

use App\Entity\Reservation;
use App\Entity\User;
use App\Entity\Vacancy;
use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class ReservationHandler
{
    private int $price;

    public function __construct(
        private EntityManagerInterface $manager,
        private LoggerInterface $logger
    ) {
        $this->price = 0;
    }

    public function reservate(?User $user, array $data): ?array
    {
        if (!$user) {
            $email = $data['email'] ?? '';

            /**
             * @var ?UserRepository $userRepository
             */
            $user = $this->manager->getRepository(User::class)
                ->findOneBy(['email' => $email]);

            if (!$user) {
                if (empty($email)) {
                    return null;
                }

                $user = new User();
                $user->setEmail($email);
                $user->setName($data['name'] ?? '');
                $this->manager->persist($user);
            }
        }

        $currentDate = new DateTimeImmutable();
        $startDate = new DateTimeImmutable($data['start_date'] ?? '');
        $endDate = new DateTimeImmutable($data['end_date'] ?? '');
        $bookedPlaces = $data['booked_places'] ?? 1;

        $reservation = new Reservation();
        $reservation->setUser($user);
        $reservation->setStartDate($startDate);
        $reservation->setEndDate($endDate);
        $reservation->setCreatedAt($currentDate);
        $reservation->setBookedPlaces($bookedPlaces);
        $reservation->setUpdatedAt($currentDate);
        $price = $this->handleVacanciesAfterBookReservation($startDate, $endDate, $bookedPlaces)
            ->getPrice();

        $reservation->setPrice($price);
        $this->manager->persist($reservation);

        $this->manager->flush();

        return $reservation->toArray();
    }

    public function handleVacanciesAfterBookReservation(
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate,
        int $bookedPlaces = 1
    ): self {
        $price = 0;

        /**
         * @var Vacancy[] $vacancies
         */
        $vacancies = $this->manager->getRepository(Vacancy::class)
            ->findByDateRange($startDate, $endDate);

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
        $result = false;

        try {
            $bookedPlaces = $reservation->getBookedPlaces();

            /**
             * @var Vacancy[] $vacancies
             */
            $vacancies = $this->manager->getRepository(Vacancy::class)
                ->findByDateRange($reservation->getStartDate(), $reservation->getEndDate());

            foreach ($vacancies as $vacancy) {
                $currentAvailableSlots = $vacancy->getFree();
                $vacancy->setFree($currentAvailableSlots + $bookedPlaces);
                $this->manager->persist($vacancy);
            }

            $this->manager->flush();
            $this->manager->remove($reservation);
            $result = true;
        } catch (\Throwable $e) {
            $this->logger->error($e);
        }

        return $result;
    }
}
