<?php

namespace App\Controller\Vacancy;

use ApiPlatform\Doctrine\Orm\Paginator;
use ApiPlatform\Symfony\Validator\Exception\ValidationException;
use ApiPlatform\Validator\ValidatorInterface;
use App\Repository\VacancyRepository;
use App\Requests\VacancyGetCollectionRequest;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

#[AsController]
class GetCollectionController extends AbstractController
{
    #[Route('/vacancy/get/collection', name: 'app_vacancy_get_collection')]
    public function __invoke(
        Request $request,
        VacancyRepository $vacancyRepository,
        ValidatorInterface $validator,
        LoggerInterface $logger
    ): Paginator|Response {
        try {
            $page = (int) $request->query->get('page', 1);
            $startDate = $request->query->get('startDate');
            $endDate = $request->query->get('endDate');
            $free = $request->query->get('free');

            if ($free && is_numeric($free)) {
                $free = (int) $free;
            }

            $validator->validate(
                new VacancyGetCollectionRequest(
                    $startDate,
                    $endDate,
                    $free,
                )
            );

            return $vacancyRepository->findByDateRangeAndAvailableFreePlacesWithPagination(
                $startDate,
                $endDate,
                $free,
                $page
            );
        } catch (ValidationException $validationException) {
            return new JsonResponse(
                $validationException->getMessage(),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        } catch (Throwable $error) {
            $logger->error($error);

            return new JsonResponse('Somethong went wrong. Try again later.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
