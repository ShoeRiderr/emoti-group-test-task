<?php

namespace App\Controller\Vacancy;

use App\DependencyInjection\VacancyHandler;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Throwable;

#[AsController]
class CreateController extends AbstractController
{
    public function __invoke(
        Request $request,
        VacancyHandler $vacancyHandler,
        LoggerInterface $logger
    ): Response {
        try {
            $result = $vacancyHandler->create($request->toArray());

            return $this->json([
                'data' => $result,
            ], Response::HTTP_OK);
        } catch (Throwable $error) {
            $logger->error($error);

            return $this->json([
                'error' => 'Something went wrong during creating new vacancy.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
