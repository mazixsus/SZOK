<?php
/**
 * Created by PhpStorm.
 * User: Piotr
 * Date: 29.10.2018
 * Time: 21:21
 */

namespace App\Repository;


use App\Entity\Pracownicy;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bridge\Doctrine\RegistryInterface;


class PracownicyRepository extends ServiceEntityRepository
{

    /**
     * PracownicyRepository constructor.
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Pracownicy::class);
    }

    public function getPageCountOfActive($pageLimit = 10)
    {
        $query = $this->createQueryBuilder('p')
            ->select('count(p.id)')
            ->andWhere('p.czyaktywny IS NULL')
            ->orderBy('p.id', 'ASC')
            ->getQuery();

        $count = $query->getSingleScalarResult();
        $pageCount = floor($count / $pageLimit);
        $rest = $count % $pageLimit;
        if($rest != 0) {
            $pageCount = $pageCount + 1;
        }
        return $pageCount;
    }

    public function findActive($page = 1, $pageLimit = 10)
    {
        $query = $this->createQueryBuilder('p')
            ->andWhere('p.czyaktywny IS NULL')
            ->orderBy('p.id', 'ASC')
            ->getQuery();

        $requestedPage = new Paginator($query);
        $requestedPage->getQuery()
            ->setFirstResult($pageLimit * ($page - 1))
            ->setMaxResults($pageLimit);

        return $requestedPage;
    }
}
