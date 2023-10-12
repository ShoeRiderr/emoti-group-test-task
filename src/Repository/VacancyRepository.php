<?php

namespace App\Repository;

use ApiPlatform\Doctrine\Orm\Paginator;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;
use App\Entity\Vacancy;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Query;

/**
 * @extends ServiceEntityRepository<Vacancy>
 *
 * @method Vacancy|null find($id, $lockMode = null, $lockVersion = null)
 * @method Vacancy|null findOneBy(array $criteria, array $orderBy = null)
 * @method Vacancy[]    findAll()
 * @method Vacancy[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VacancyRepository extends ServiceEntityRepository
{
    const ITEMS_PER_PAGE = 10;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vacancy::class);
    }

    public function findByDateRange(?\DateTimeInterface $startDate, ?\DateTimeInterface $endDate): Query
    {
        $queryBuilder = $this->createQueryBuilder('v');

        if ($startDate) {
            $queryBuilder->where('v.date >= :start')
                ->setParameter('start', $startDate);
        }
        if ($endDate) {
            $queryBuilder->andWhere('v.date <= :end')
                ->setParameter('end', $endDate);
        }

        return $queryBuilder->orderBy('v.id', 'ASC')
            ->getQuery();
    }

    public function findWithPagination(
        bool $excludeNotAvailable = false,
        int $itemsPerPage = self::ITEMS_PER_PAGE,
        bool $excludePast = false,
        int $page = 1
    ): Paginator {
        $firstResult = ($page - 1) * self::ITEMS_PER_PAGE;

        $queryBuilder = $this->createQueryBuilder('v');

        if ($excludeNotAvailable) {
            $queryBuilder->where('v.free > 0');
        }

        if ($excludePast) {
            $queryBuilder->where('v.date >= CURRENT_DATE()');
        }

        $queryBuilder->orderBy('v.id', 'ASC');

        $criteria = Criteria::create()
            ->setFirstResult($firstResult)
            ->setMaxResults($itemsPerPage);
        $queryBuilder->addCriteria($criteria);

        $doctrinePaginator = new DoctrinePaginator($queryBuilder);
        $paginator = new Paginator($doctrinePaginator);

        return $paginator;
    }
}
