<?php

namespace App\Controller\Vacancy;

use App\DependencyInjection\VacancyHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

class CreateController extends AbstractController
{
    public function __invoke(Request $request, VacancyHandler $vacancyHandler): Response
    {
        try {
            $result = $vacancyHandler->create($request->toArray());

            return $this->json([
                'data' => $result,
            ], Response::HTTP_OK);
        } catch (Throwable $e) {
            return $this->json([
                'data' => $result,
            ], $e->getCode());
        }
    }
}
