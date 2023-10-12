<?php

namespace App\Controller\Vacancy;

use ApiPlatform\Doctrine\Orm\Paginator;
use ApiPlatform\Symfony\Validator\Exception\ValidationException;
use ApiPlatform\Validator\ValidatorInterface;
use App\DependencyInjection\VacancyHandler;
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
        LoggerInterface $logger,
        VacancyHandler $vacancyHandler
    ): Paginator|Response {
        try {
            $page = (int) $request->query->get('page', 1);
            $startDate = $request->query->get('startDate');
            $endDate = $request->query->get('endDate');
            $free = $request->query->get('free');
            $excludeNotAvailable = filter_var($request->query->get('excludeNotAvailable', false), FILTER_VALIDATE_BOOLEAN);
            $excludePast = filter_var($request->query->get('excludePast', false), FILTER_VALIDATE_BOOLEAN);
            $itemsPerPage = $request->query->get('itemsPerPage', 10);

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

            if (!$startDate && !$endDate && !$free) {
                return $vacancyRepository->findWithPagination($excludeNotAvailable, $itemsPerPage, $excludePast, $page);
            }

            $vacancies = $vacancyHandler->getByDateRangeAndFreePlaces([
                'startDate' => $startDate,
                'endDate' => $endDate,
                'bookedPlaces' => $free,
            ]);

            $vacancies = array_map(fn ($vacancy) => $vacancy->toArray(), $vacancies);
            $totalPrice = array_sum(array_column($vacancies, 'price')) * $free;
            $totalPriceFormatted = $totalPrice / 100;

            $result['data'] = $vacancies;
            $result['totalPrice'] = $totalPrice;
            $result['totalPriceFormatted'] = $totalPriceFormatted;

            return new JsonResponse($result);
        } catch (ValidationException $validationException) {
            return new JsonResponse(
                $validationException->getMessage(),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        } catch (Throwable $error) {
            $logger->error($error);

            return new JsonResponse('Something went wrong. Try again later.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
