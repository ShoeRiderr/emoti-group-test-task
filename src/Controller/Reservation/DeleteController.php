<?php

namespace App\Controller\Reservation;

use App\DependencyInjection\ReservationHandler;
use App\Entity\Reservation;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class DeleteController extends AbstractController
{
    public function __invoke(Reservation $reservation, ReservationHandler $reservationHandler): JsonResponse
    {
        $result = $reservationHandler->cancel($reservation);
        $status = $result ? Response::HTTP_OK : Response::HTTP_INTERNAL_SERVER_ERROR;

        return $this->json([
            'data' => $result,
        ], $status);
    }
}
