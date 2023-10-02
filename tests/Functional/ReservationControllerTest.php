<?php

namespace App\Tests\Functional;

use App\DataFixtures\VacancyFixtures;
use App\Tests\ApiTestCase;
use App\Entity\Reservation;
use DateTimeImmutable;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;

class ReservationControllerTest extends ApiTestCase
{
    protected AbstractDatabaseTool $databaseTool;

    protected function setUp(): void
    {
        parent::setUp();

        $this->databaseTool = static::getContainer()->get(DatabaseToolCollection::class)->get();

        $doctrine = static::getContainer()->get('doctrine');
        $this->entityManager = $doctrine->getManager();
    }

    public function testSuccessfullReservationOfOneDay(): void
    {
        $this->databaseTool->loadFixtures([
            VacancyFixtures::class
        ]);

        $currentDate = new DateTimeImmutable();
        $tommorow = $currentDate->modify('+1 day');
        $dayAfterTommorow = $currentDate->modify('+2 day');

        $data = [
            'email' => 'test@example.com',
            'start_date' => $tommorow->format('Y-m-d'),
            'end_date' => $dayAfterTommorow->format('Y-m-d'),
            'booked_places' => 1,
        ];

        $response = static::createClient()->request('POST', '/api/reservations', [
            'json' => $data
        ]);

        $this->assertResponseIsSuccessful();

        $responseContent = json_decode($response->getContent())->data;

        /**
         * @var Reservation $reservation
         */
        $reservation = $this->entityManager
            ->getRepository(Reservation::class)
            ->findOneBy([], ['id' => 'desc']);

        $this->assertNotNull($responseContent);
        $this->assertSame($reservation->getId(), $responseContent->id);
        $this->assertSame($reservation->getFormatedStartDate(), $responseContent->startDate);
        $this->assertSame($reservation->getFormatedEndDate(), $responseContent->endDate);
        $this->assertSame($reservation->getFormatedCreatedAt(), $responseContent->createdAt);
        $this->assertSame($reservation->getFormatedUpdatedAt(), $responseContent->updatedAt);
        $this->assertSame($reservation->getPrice(), $responseContent->price);
        $this->assertSame($reservation->getBookedPlaces(), $responseContent->bookedPlaces);
    }
}
