<?php

namespace App\DataFixtures;

use App\DependencyInjection\ReservationHandler;
use App\Entity\Reservation;
use App\Entity\Vacancy;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ReservationFixtures extends Fixture implements DependentFixtureInterface
{
    public const RESERVATION_AMOUNT = 10;

    public function __construct(private ReservationHandler $reservationHandler)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $currentDate = new DateTimeImmutable();

        for ($i = 1; $i <= self::RESERVATION_AMOUNT; $i++) {
            $startDate = $currentDate;
            $endDate = $currentDate;

            $startDate = $startDate->modify(sprintf("+%d day", $i));

            $randEnd = rand($i, (50 - $i));

            $endDate = $endDate->modify(sprintf("+%d day", $randEnd));
            $bookedPlaces = rand(1, 3);

            /**
             * @var Vacancy[] $vacancies
             */
            $vacancies = $manager->getRepository(Vacancy::class)
                ->findByDateRange($startDate, $endDate)
                ->getResult();

            $price = $this->reservationHandler
                ->handleVacanciesAfterBookReservation($vacancies, $bookedPlaces)
                ->getPrice();

            $reservation = new Reservation();
            $reservation->setStartDate($startDate);
            $reservation->setUser($this->getReference(UserFixtures::USER_REFERENCE));
            $reservation->setEndDate($endDate);
            $reservation->setBookedPlaces($bookedPlaces);
            $reservation->setUpdatedAt($currentDate);
            $reservation->setCreatedAt($currentDate);
            $reservation->setPrice($price);

            $manager->persist($reservation);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class,
            VacancyFixtures::class,
        ];
    }
}
