<?php

namespace App\Controller\Reservation;

use App\DependencyInjection\ReservationHandler;
use App\Entity\User;
use App\Exceptions\NotEnoughVacanciesException;
use App\Repository\ApiTokenRepository;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Throwable;

#[AsController]
class CreateController extends AbstractController
{
    public function __invoke(
        Request $request,
        ReservationHandler $reservationHandler,
        ApiTokenRepository $apiTokenRepository,
        LoggerInterface $logger
    ): JsonResponse {
        try {
            /**
             * @var null|string
             */
            $token = $request->headers->get('X-API-TOKEN');

            /**
             * @var User|null
             */
            $user = $apiTokenRepository->findOneBy(['token' => $token])?->getUser();

            $result = $reservationHandler->reservate($user, $request->toArray());

            return $this->json([
                'data' => $result,
            ], Response::HTTP_OK);
        }
        catch (NotEnoughVacanciesException $error) {
            return $this->json([
                'error' => $error->getMessage(),
            ], $error->getCode());
        }
        catch (Throwable $error) {
            $logger->error($error);

            return $this->json([
                'error' => 'Something went wrong durring saving the reservation.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
