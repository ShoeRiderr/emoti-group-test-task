<?php

namespace App\Tests\Functional;

use App\DataFixtures\ReservationFixtures;
use App\DataFixtures\VacancyFixtures;
use App\Entity\Reservation;
use App\Entity\User;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Response;

class ReservationTest extends ApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->databaseTool->loadFixtures([
            VacancyFixtures::class
        ]);
    }

    public function testFetchingAllReservations(): void
    {
        $this->databaseTool->loadFixtures([
            ReservationFixtures::class
        ]);

        $this->client->request('GET', '/api/reservations');

        $this->assertResponseIsSuccessful();

        $this->assertResponseHeaderSame(
            'content-type',
            'application/ld+json; charset=utf-8'
        );

        $this->assertJsonContains([
            '@context'         => '/api/contexts/Reservation',
            '@id'              => '/api/reservations',
            '@type'            => 'hydra:Collection',
            'hydra:totalItems' => ReservationFixtures::RESERVATION_AMOUNT,
        ]);
    }

    public function testSuccessfullReservationForOneDayOfLoggedOutUser(): void
    {
        $currentDate = new DateTimeImmutable();
        $tommorow = $currentDate->modify('+1 day');
        $dayAfterTommorow = $currentDate->modify('+2 day');

        $data = [
            'email' => 'test@example.com',
            'start_date' => $tommorow->format('Y-m-d'),
            'end_date' => $dayAfterTommorow->format('Y-m-d'),
            'booked_places' => 1,
        ];

        $response = $this->client->request('POST', '/api/reservations', [
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

    public function testSuccessfullReservationForOneDayOfLoggedInUser(): void
    {
        $currentDate = new DateTimeImmutable();
        $tommorow = $currentDate->modify('+1 day');
        $dayAfterTommorow = $currentDate->modify('+2 day');

        $data = [
            'start_date' => $tommorow->format('Y-m-d'),
            'end_date' => $dayAfterTommorow->format('Y-m-d'),
            'booked_places' => 1,
        ];
// dd($this->user);
        $response = $this->client->request('POST', '/api/reservations', [
            'json' => $data,
            'headers' => ['X-API-TOKEN' => self::API_TOKEN]
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


    public function testReservationOfOneDayWithNotEnoughVacancies(): void
    {
        $currentDate = new DateTimeImmutable();
        $tommorow = $currentDate->modify('+1 day');
        $dayAfterTommorow = $currentDate->modify('+2 day');

        $data = [
            'email' => 'test@example.com',
            'start_date' => $tommorow->format('Y-m-d'),
            'end_date' => $dayAfterTommorow->format('Y-m-d'),
            'booked_places' => 10000,
        ];

        $this->client->request('POST', '/api/reservations', [
            'json' => $data
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY, 'Not enough available vacancies.');
    }
}
