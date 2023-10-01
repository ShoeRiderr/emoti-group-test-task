<?php

namespace App\Tests\Functional;

use App\Tests\ApiTestCase;
use App\Entity\Reservation;

class ReservationControllerTest extends ApiTestCase
{
    public function testSuccessfullReservationOfOneDay(): void
    {
        $payload = [
            'start_date' => '2023-10-15',
            'end_date' => '2023-10-15',
        ];

        $response = static::createClient()->request('POST', '/reservation', [
            'json' => $payload
        ]);

        $this->assertResponseIsSuccessful();
        $reservation = $this->entityManager
            ->getRepository(Reservation::class)
            ->findOneBy(['start_date' => 'DESC']);

        $this->assertSame($reservation->getStartDate(), $payload['start_date']);
        $this->assertSame($reservation->getEndDate(), $payload['end_date']);
    }
}
