<?php
/**
 * Created by PhpStorm.
 * User: Piotr
 * Date: 02.12.2018
 * Time: 17:26
 */

namespace App\Repository;


use App\Entity\Uzytkownicy;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bridge\Doctrine\RegistryInterface;

class UzytkownicyRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Uzytkownicy::class);
    }

    public function getPageCountOfActive($pageLimit = 10)
    {
        $query = $this->createQueryBuilder('p')
            ->select('count(p.id)')
            ->andWhere('p.czyzablokowany IS NULL')
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
            ->andWhere('p.czyzablokowany IS NULL OR p.czyzablokowany = 0')
            ->orderBy('p.id', 'ASC')
            ->getQuery();

        $requestedPage = new Paginator($query);
        $requestedPage->getQuery()
            ->setFirstResult($pageLimit * ($page - 1))
            ->setMaxResults($pageLimit);

        return $requestedPage;
    }
    public function getPageCountOfDisable($pageLimit = 10)
    {
        $query = $this->createQueryBuilder('p')
            ->select('count(p.id)')
            ->andWhere('p.czyzablokowany = 1')
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

    public function findDisable($page = 1, $pageLimit = 10)
    {
        $query = $this->createQueryBuilder('p')
            ->andWhere('p.czyzablokowany = 1')
            ->orderBy('p.id', 'ASC')
            ->getQuery();

        $requestedPage = new Paginator($query);
        $requestedPage->getQuery()
            ->setFirstResult($pageLimit * ($page - 1))
            ->setMaxResults($pageLimit);

        return $requestedPage;
    }
}