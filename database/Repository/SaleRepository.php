<?php
/**
 * Created by PhpStorm.
 * User: Mateusz
 * Date: 07.11.2018
 * Time: 15:07
 */

namespace App\Repository;

use App\Entity\Sale;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bridge\Doctrine\RegistryInterface;

class SaleRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Sale::class);
    }

    public function findRooms($page = 1, $pageLimit = 10)
    {
        $query = $this->createQueryBuilder('s')
            ->orderBy('s.numersali', 'ASC')
            ->getQuery();

        $requestedPage = new Paginator($query);

        $requestedPage->getQuery()
            ->setFirstResult($pageLimit * ($page - 1))
            ->setMaxResults($pageLimit);

        return $requestedPage;
    }

    public function getPageCount($pageLimit = 10)
    {
        $query = $this->createQueryBuilder('s')
            ->select('count(s.id)')
            ->orderBy('s.numersali', 'ASC')
            ->getQuery();

        $count = $query->getSingleScalarResult();

        $pageCount = floor($count / $pageLimit);
        $rest = $count % $pageLimit;
        if ($rest != 0) {
            $pageCount = $pageCount + 1;
        }

        return $pageCount;
    }

}