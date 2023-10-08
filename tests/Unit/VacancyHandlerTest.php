<?php

namespace App\Tests\Unit;

use App\DependencyInjection\ReservationHandler;
use App\Entity\Reservation;
use App\Entity\Vacancy;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityManager;

class VacancyHandlerTest extends KernelTestCase
{
    private ?EntityManager $entityManager;
    private ReservationHandler $reservationHandler;
    private DateTimeImmutable $currentDate;
    private DateTimeImmutable $tommorow;
    private DateTimeImmutable $dayAfterTommorow;
    private int $bookedPlaces;

    protected function setUp(): void
    {
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->reservationHandler = self::getContainer()->get(ReservationHandler::class);

        $this->currentDate = new DateTimeImmutable();

        $this->tommorow = $this->currentDate->modify('+1 day');
        $this->dayAfterTommorow = $this->currentDate->modify('+2 day');
        $this->bookedPlaces = 2;
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // doing this is recommended to avoid memory leaks
        $this->entityManager->close();
        $this->entityManager = null;
    }

    public function testSuccessfulUseOfReservateMethod(): void
    {
        $email = 'test@example.com';

        /**
         * @var array<string,?Vacancy> $vacancies
         */
        $vacancies = $this->prepareVacancies();

        $data = array_merge(
            $this->prepareResarvationBasicPayload(),
            [
                'email' => $email,
            ]
        );

        /**
         * @var array|null $reservation
         */
        $reservation = $this->reservationHandler->reservate(null, $data);

        $this->assertNotNull($reservation);
        // Check amount of available places in vacancy table
        $this->assertSame(8, $vacancies['vacancy1']?->getFree());
        $this->assertSame(13, $vacancies['vacancy2']?->getFree());
        // Check reservation price
        $this->assertSame(70, $reservation['price'] ?? 0);
    }

    public function testFailUseOfReservateMethod(): void
    {
        /**
         * @var array<string,?Vacancy> $vacancies
         */
        $vacancies = $this->prepareVacancies();

        $data = $this->prepareResarvationBasicPayload();

        /**
         * @var array|null $reservation
         */
        $reservation = $this->reservationHandler->reservate(null, $data);

        $this->assertNotNull($reservation);
        // Check amount of available places in vacancy table
        $this->assertSame(8, $vacancies['vacancy1']?->getFree());
        $this->assertSame(13, $vacancies['vacancy2']?->getFree());
        // Check reservation price
        $this->assertSame(70, $reservation['price'] ?? 0);
    }

    public function testSuccessfulUseOfCancelMethod()
    {
        $email = 'test@example.com';

        $vacancies = $this->prepareVacancies();

        $freeVacanciesBeforeReservation = array_map(function ($vacancy) {
            return $vacancy->getFree();
        }, $vacancies);

        $data = array_merge(
            $this->prepareResarvationBasicPayload(),
            [
                'email' => $email,
            ]
        );

        /**
         * @var array|null $reservation
         */
        $reservation = $this->reservationHandler->reservate(null, $data);

        $reservationEntity = $this->entityManager->getRepository(Reservation::class)
            ->findOneBy(['id' => $reservation['id']]);

        $result = $this->reservationHandler->cancel($reservationEntity);

        $freeVacanciesAfterCancelation = array_map(function ($vacancy) {
            return $vacancy->getFree();
        }, $vacancies);

        $this->assertSame(true, $result);
        $this->assertSame($freeVacanciesBeforeReservation, $freeVacanciesAfterCancelation);
    }

    /**
     * @return array<string,?Vacancy>
     */
    private function prepareVacancies(): array
    {
        $vacancy = new Vacancy();
        $vacancy->setDate($this->tommorow);
        $vacancy->setFree(10);
        $vacancy->setPrice(15);
        $vacancy->setUpdatedAt($this->currentDate);
        $vacancy->setCreatedAt($this->currentDate);
        $this->entityManager->persist($vacancy);

        $vacancy2 = new Vacancy();
        $vacancy2->setDate($this->dayAfterTommorow);
        $vacancy2->setFree(15);
        $vacancy2->setPrice(20);
        $vacancy2->setUpdatedAt($this->currentDate);
        $vacancy2->setCreatedAt($this->currentDate);
        $this->entityManager->persist($vacancy2);

        $this->entityManager->flush();

        return [
            'vacancy1' => $vacancy,
            'vacancy2' => $vacancy2,
        ];
    }

    private function prepareResarvationBasicPayload(): array
    {
        return [
            'start_date' => $this->tommorow->format('Y-m-d'),
            'end_date' => $this->dayAfterTommorow->format('Y-m-d'),
            'booked_places' => $this->bookedPlaces,
        ];
    }
}
