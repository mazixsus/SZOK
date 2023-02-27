<?php
/**
 * Created by PhpStorm.
 * User: Piotr
 * Date: 20.11.2018
 * Time: 02:05
 */

namespace App\Repository;


use App\Entity\Pulebiletow;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;

class PuleRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Pulebiletow::class);
    }

    public function getSeancesTickets($id)
    {
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
            'SELECT DISTINCT
                pbmrb.id,
                pbmrb.cena,
                rb.nazwa
            FROM App\Entity\Pulebiletow pb
            JOIN pb.seanse se
            JOIN pb.pulaMaRodzajeBiletow pbmrb
            JOIN pbmrb.rodzajebiletow rb
            WHERE se.id = :id')
            ->setParameter('id', $id);

        return $query->execute();
    }

    public function getTicketToCheck($id, $ticketId)
    {
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
            'SELECT DISTINCT
                pbmrb.id
            FROM App\Entity\Pulebiletow pb
            JOIN pb.seanse se
            JOIN pb.pulaMaRodzajeBiletow pbmrb
            JOIN pbmrb.rodzajebiletow rb
            WHERE se.id = :id
            AND pbmrb.id = :ticketId')
            ->setParameter('id', $id)
            ->setParameter('ticketId', $ticketId);

        return $query->execute();
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
}