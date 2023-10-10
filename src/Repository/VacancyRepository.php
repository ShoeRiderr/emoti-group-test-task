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

    /**
     * @return Query
     */
    public function findByDateRange(?\DateTimeInterface $startDate, ?\DateTimeInterface $endDate)
    {
        return $this->createQueryBuilder('v')
            ->where('v.date >= :start')
            ->andWhere('v.date <= :end')
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate)
            ->orderBy('v.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery();
    }

    public function findByDateRangeAndAvailableFreePlacesWithPagination(
        #[Assert\Date, Assert\NotBlank(allowNull: true)] $startDate,
        #[Assert\Date, Assert\NotBlank(allowNull: true)] $endDate,
        ?int $freePlaces,
        int $page = 1
    ): Paginator {
        $firstResult = ($page - 1) * self::ITEMS_PER_PAGE;
        $queryBuilder = $this->createQueryBuilder('v');
        if ($startDate) {
            $queryBuilder->where('v.date >= :start')
                ->setParameter('start', $startDate);
        }
        if ($endDate) {
            $queryBuilder->andWhere('v.date <= :end')
                ->setParameter('end', $endDate);
        }
        if ($freePlaces) {
            $queryBuilder->andWhere('v.free >= :freePlaces')
                ->setParameter('freePlaces', $freePlaces);
        }

        $queryBuilder->orderBy('v.id', 'ASC');

        $criteria = Criteria::create()
            ->setFirstResult($firstResult)
            ->setMaxResults(self::ITEMS_PER_PAGE);
        $queryBuilder->addCriteria($criteria);


        $doctrinePaginator = new DoctrinePaginator($queryBuilder);
        $paginator = new Paginator($doctrinePaginator);

        return $paginator;
    }

    public function findByDateRangeAndAvailableFreePlaces(
        #[Assert\Date, Assert\NotBlank(allowNull: true)] $startDate,
        #[Assert\Date, Assert\NotBlank(allowNull: true)] $endDate,
        ?int $freePlaces
    ): Query {
        $queryBuilder = $this->createQueryBuilder('v');

        if ($startDate) {
            $queryBuilder->where('v.date >= :start')
                ->setParameter('start', $startDate);
        }

        if ($endDate) {
            $queryBuilder->andWhere('v.date <= :end')
                ->setParameter('end', $endDate);
        }

        if ($freePlaces) {
            $queryBuilder->andWhere('v.free >= :freePlaces')
                ->setParameter('freePlaces', $freePlaces);
        }

        return $queryBuilder->orderBy('v.id', 'ASC')
            ->getQuery();
    }

    //    public function findOneBySomeField($value): ?Vacancy
    //    {
    //        return $this->createQueryBuilder('v')
    //            ->andWhere('v.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
