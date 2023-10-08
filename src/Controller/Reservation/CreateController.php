<?php

namespace App\Controller\Reservation;

use App\DependencyInjection\ReservationHandler;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Throwable;

#[AsController]
class CreateController extends AbstractController
{
    public function __invoke(Request $request, #[CurrentUser] User $user = null, ReservationHandler $reservationHandler): JsonResponse
    {
        try {
            $result = $reservationHandler->reservate($user, $request->toArray());

            return $this->json([
                'data' => $result,
            ], Response::HTTP_OK);
        } catch (Throwable $e) {
            return $this->json([
                'error' => 'Something went wrong durring saving the reservation.',
            ], $e->getCode());
        }
    }
}
