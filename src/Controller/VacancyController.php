<?php

namespace App\Controller;

use App\Entity\Vacancy;
use App\Repository\VacancyRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class VacancyController extends AbstractController
{
    #[Route('/vacancy', name: 'vacancy_list', methods: ['GET', 'HEAD'])]
    public function index(VacancyRepository $repository): JsonResponse
    {
        return $this->json([
            'data' => $repository->findAll(),
        ]);
    }

    #[Route('/vacancy', name: 'vacancy_create', methods: ['POST'])]
    public function store(Request $request): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/vacancyController.php',
        ]);
    }

    #[Route('/vacancy/{id}', name: 'vacancy_delete', methods: ['GET', 'HEAD'])]
    public function show(Vacancy $vacancy): JsonResponse
    {
        return $this->json([
            'data' => $vacancy,
        ]);
    }


    #[Route('/vacancy/{id}', name: 'vacancy_delete', methods: ['PUT', 'PATCH'])]
    public function update(Request $request, Vacancy $vacancy): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/vacancyController.php',
        ]);
    }

    #[Route('/vacancy/{id}', name: 'vacancy_delete', methods: ['DELETE'])]
    public function delete(Vacancy $vacancy): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/vacancyController.php',
        ]);
    }
}
