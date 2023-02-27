<?php
/**
 * Created by PhpStorm.
 * User: Piotr
 * Date: 19.11.2018
 * Time: 11:35
 */

namespace App\Repository;


use App\Entity\Rodzajebiletow;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;

class RodzajeBiletowRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Rodzajebiletow::class);
    }

    public function getPageCountOfActive($pageLimit = 10)
    {
        $query = $this->createQueryBuilder('d')
            ->select('count(d.id)')
            ->andWhere('d.usunieto IS NULL OR d.usunieto = 0')
            ->orderBy('d.id', 'ASC')
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
        $query = $this->createQueryBuilder('d')
            ->andWhere('d.usunieto IS NULL OR d.usunieto = 0')
            ->orderBy('d.id', 'ASC')
            ->getQuery();

        $requestedPage = new Paginator($query);
        $requestedPage->getQuery()
            ->setFirstResult($pageLimit * ($page - 1))
            ->setMaxResults($pageLimit);

        return $requestedPage;
    }

    public function getPageCountOfDeleted($pageLimit = 10)
    {
        $query = $this->createQueryBuilder('d')
            ->select('count(d.id)')
            ->andWhere('d.usunieto = 1')
            ->orderBy('d.id', 'ASC')
            ->getQuery();

        $count = $query->getSingleScalarResult();
        $pageCount = floor($count / $pageLimit);
        $rest = $count % $pageLimit;
        if($rest != 0) {
            $pageCount = $pageCount + 1;
        }
        return $pageCount;
    }

    public function findDeleted($page = 1, $pageLimit = 10)
    {
        $query = $this->createQueryBuilder('d')
            ->andWhere('d.usunieto = 1')
            ->orderBy('d.id', 'ASC')
            ->getQuery();

        $requestedPage = new Paginator($query);
        $requestedPage->getQuery()
            ->setFirstResult($pageLimit * ($page - 1))
            ->setMaxResults($pageLimit);

        return $requestedPage;
    }

    public function findAllActive()
    {
        $query = $this->createQueryBuilder('d')
            ->andWhere('d.usunieto IS NULL OR d.usunieto = 0')
            ->orderBy('d.id', 'ASC')
            ->getQuery();

        return $query->execute();
    }
}