<?php

namespace App\Controller;

use App\DependencyInjection\ReservationHandler;
use App\Requests\ReservationRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class CreateReservationController extends AbstractController
{
    public function __invoke(ReservationRequest $request, ReservationHandler $reservationHandler): JsonResponse
    {
        $request->validate();

        $result = $reservationHandler->reservate($this->getUser(), $request->toArray());
        $status = $result ? Response::HTTP_OK : Response::HTTP_INTERNAL_SERVER_ERROR;

        return $this->json([
            'data' => $result,
        ], $status);
    }
}
