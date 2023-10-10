<?php

namespace App\Tests\Functional;

use App\DataFixtures\ReservationFixtures;
use App\DataFixtures\VacancyFixtures;
use App\Entity\Reservation;
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

        $this->client->request('GET', '/api/reservations', [
            'headers' => ['X-API-TOKEN' => self::API_TOKEN]
        ]);

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
        $tomorrow = $currentDate->modify('+1 day');
        $dayAfterTomorrow = $currentDate->modify('+2 day');

        $data = [
            'email' => 'test@example.com',
            'start_date' => $tomorrow->format('Y-m-d'),
            'end_date' => $dayAfterTomorrow->format('Y-m-d'),
            'booked_places' => 1,
        ];

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
        $tomorrow = $currentDate->modify('+1 day');
        $dayAfterTomorrow = $currentDate->modify('+2 day');

        $data = [
            'email' => 'test@example.com',
            'start_date' => $tomorrow->format('Y-m-d'),
            'end_date' => $dayAfterTomorrow->format('Y-m-d'),
            'booked_places' => 10000,
        ];

        $this->client->request('POST', '/api/reservations', [
            'json' => $data,
            'headers' => [
                'X-API-TOKEN' => self::API_TOKEN,
            ],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY, 'Not enough available vacancies.');
    }

    public function testSuccessfullCancelationOfTheReservation(): void
    {
        $this->databaseTool->loadFixtures([
            ReservationFixtures::class
        ]);

        /**
         * @var Reservation $lastReservationBeforeCancel
         */
        $lastReservationBeforeCancel = $this->entityManager
            ->getRepository(Reservation::class)
            ->findOneBy([], ['id' => 'desc']);
        $lastReservationIdBeforeCancel = $lastReservationBeforeCancel->getId();

        $response = $this->client
            ->loginUser($lastReservationBeforeCancel->getUser())
            ->request('DELETE', '/api/reservations/' . $lastReservationIdBeforeCancel, [
            'headers' => [
                'X-API-TOKEN' => self::API_TOKEN,
            ],
        ]);

        /**
         * @var Reservation $lastReservationAfterCancel
         */
        $lastReservationAfterCancel = $this->entityManager
            ->getRepository(Reservation::class)
            ->findOneBy([], ['id' => 'desc']);

        $this->assertResponseIsSuccessful();

        $responseContent = json_decode($response->getContent())->data;

        $this->assertSame(true, $responseContent);

        $this->assertNotEquals($lastReservationIdBeforeCancel, $lastReservationAfterCancel->getId());
    }

    public function testCancelReservatiotionAsUnauthorizedUser(): void
    {
        $this->databaseTool->loadFixtures([
            ReservationFixtures::class
        ]);

        /**
         * @var Reservation $lastReservationBeforeCancel
         */
        $lastReservationBeforeCancel = $this->entityManager
            ->getRepository(Reservation::class)
            ->findOneBy([], ['id' => 'desc']);
        $lastReservationIdBeforeCancel = $lastReservationBeforeCancel->getId();

        $response = $this->client
            ->request('DELETE', '/api/reservations/' . $lastReservationIdBeforeCancel, [
            'headers' => [
                'X-API-TOKEN' => self::API_TOKEN,
            ],
        ]);

        /**
         * @var Reservation $lastReservationAfterCancel
         */
        $lastReservationAfterCancel = $this->entityManager
            ->getRepository(Reservation::class)
            ->findOneBy([], ['id' => 'desc']);

        $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);

        $this->assertEquals($lastReservationIdBeforeCancel, $lastReservationAfterCancel->getId());
    }
}
