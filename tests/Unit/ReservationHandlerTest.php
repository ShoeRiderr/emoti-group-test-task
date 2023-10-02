<?php

namespace App\Tests\Unit;

use App\DependencyInjection\ReservationHandler;
use App\Entity\Vacancy;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityManager;

class ReservationHandlerTest extends KernelTestCase
{
    private ?EntityManager $entityManager;
    private ReservationHandler $reservationHandler;

    protected function setUp(): void
    {
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->reservationHandler = self::getContainer()->get(ReservationHandler::class);
    }

    public function testSuccesfullReservationOfLoggedOutUser(): void
    {
        $currentDate = new DateTimeImmutable();
        $email = 'test@example.com';

        $tommorow = $currentDate->modify('+1 day');
        $dayAfterTommorow = $currentDate->modify('+2 day');

        $vacancy = new Vacancy();
        $vacancy->setDate($tommorow);
        $vacancy->setFree(10);
        $vacancy->setPrice(15);
        $vacancy->setUpdatedAt($currentDate);
        $vacancy->setCreatedAt($currentDate);
        $this->entityManager->persist($vacancy);

        $vacancy2 = new Vacancy();
        $vacancy2->setDate($dayAfterTommorow);
        $vacancy2->setFree(15);
        $vacancy2->setPrice(20);
        $vacancy2->setUpdatedAt($currentDate);
        $vacancy2->setCreatedAt($currentDate);
        $this->entityManager->persist($vacancy2);

        $this->entityManager->flush();

        $data = [
            'start_date' => $tommorow->format('Y-m-d'),
            'end_date' => $dayAfterTommorow->format('Y-m-d'),
            'booked_places' => 2,
            'email' => $email,
        ];

        $reservation = $this->reservationHandler->reservate(null, $data);

        $this->assertNotNull($reservation);
        // Check amount of available places in vacancy table
        $this->assertSame(8, $vacancy->getFree());
        $this->assertSame(13, $vacancy2->getFree());
        // Check reservation price
        $this->assertSame(70, $reservation->getPrice());
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // doing this is recommended to avoid memory leaks
        $this->entityManager->close();
        $this->entityManager = null;
    }
}
