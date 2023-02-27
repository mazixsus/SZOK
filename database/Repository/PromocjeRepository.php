<?php
/**
 * Created by PhpStorm.
 * User: gnowa
 * Date: 28.10.2018
 * Time: 17:33
 */

namespace App\Repository;


use App\Entity\Promocje;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bridge\Doctrine\RegistryInterface;


class PromocjeRepository extends ServiceEntityRepository
{

    /**
     * PromocjeRepository constructor.
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Promocje::class);
    }

    public function findActual($page = 1, $pageLimit = 10)
    {
        $query = $this->createQueryBuilder('p')
            ->andWhere('p.koniecpromocji >= :currentDate')
            ->setParameter('currentDate', date("Y-m-d"))
            ->orderBy('p.poczatekpromocji', 'ASC')
            ->getQuery();

        $requestedPage = new Paginator($query);

        $requestedPage->getQuery()
            ->setFirstResult($pageLimit * ($page - 1))
            ->setMaxResults($pageLimit);

        return $requestedPage;
    }

    public function getPageCountOfActual($pageLimit = 10)
    {
        $query = $this->createQueryBuilder('p')
            ->select('count(p.id)')
            ->andWhere('p.koniecpromocji >= :currentDate')
            ->setParameter('currentDate', date("Y-m-d"))
            ->orderBy('p.poczatekpromocji', 'ASC')
            ->getQuery();

        $count = $query->getSingleScalarResult();

        $pageCount = floor($count / $pageLimit);
        $rest = $count % $pageLimit;
        if($rest != 0) {
            $pageCount = $pageCount + 1;
        }

        return $pageCount;
    }

    public function findOld($page = 1, $pageLimit = 10)
    {
        $query = $this->createQueryBuilder('p')
            ->andWhere('p.koniecpromocji < :currentDate')
            ->setParameter('currentDate', date("Y-m-d"))
            ->orderBy('p.poczatekpromocji', 'ASC')
            ->getQuery();

        $requestedPage = new Paginator($query);

        $requestedPage->getQuery()
            ->setFirstResult($pageLimit * ($page - 1))
            ->setMaxResults($pageLimit);

        return $requestedPage;
    }

    public function getPageCountOfOld($pageLimit = 10)
    {
        $query = $this->createQueryBuilder('p')
            ->select('count(p.id)')
            ->andWhere('p.koniecpromocji < :currentDate')
            ->setParameter('currentDate', date("Y-m-d"))
            ->orderBy('p.poczatekpromocji', 'ASC')
            ->getQuery();

        $count = $query->getSingleScalarResult();

        $pageCount = floor($count / $pageLimit);
        $rest = $count % $pageLimit;
        if($rest != 0) {
            $pageCount = $pageCount + 1;
        }

        return $pageCount;
    }

    public function findCurrent($date = NULL)
    {
        if (!$date) $date = date("Y-m-d");
        $query = $this->createQueryBuilder('p')
            ->andWhere('p.koniecpromocji >= :currentDate AND p.poczatekpromocji <= :currentDate')
            ->setParameter('currentDate', $date)
            ->orderBy('p.poczatekpromocji', 'ASC')
            ->getQuery();

        return $query->execute();
    }

    public function getPromotionToCheck($promotionId)
    {
        $query = $this->createQueryBuilder('p')
            ->andWhere('p.koniecpromocji >= :currentDate AND p.poczatekpromocji <= :currentDate')
            ->andWhere('p.id = :promotionId')
            ->setParameter('promotionId', $promotionId)
            ->setParameter('currentDate', date("Y-m-d"))
            ->getQuery();

        return $query->execute();
    }

    public function findCurrentForUser($registrationDate, $ifWoman){
        $date = date("Y-m-d");
        $query = $this->createQueryBuilder('p')
            ->andWhere('p.koniecpromocji >= :currentDate AND p.poczatekpromocji <= :currentDate')
            ->andWhere('p.staz < :registrationDate OR p.staz IS NULL')
            ->andWhere('p.czykobieta LIKE :ifWoman OR p.czykobieta IS NULL')
            ->setParameter('registrationDate', $registrationDate)
            ->setParameter('ifWoman', $ifWoman)
            ->setParameter('currentDate', $date)
            ->orderBy('p.poczatekpromocji', 'ASC')
            ->getQuery();

        return $query->execute();
    }

    public function getPromotionToCheckForUser($registrationDate, $ifWoman, $promotionId){
        $date = date("Y-m-d");
        $query = $this->createQueryBuilder('p')
            ->andWhere('p.koniecpromocji >= :currentDate AND p.poczatekpromocji <= :currentDate')
            ->andWhere('p.staz < :registrationDate OR p.staz IS NULL')
            ->andWhere('p.czykobieta LIKE :ifWoman OR p.czykobieta IS NULL')
            ->andWhere('p.id = :promotionId')
            ->setParameter('promotionId', $promotionId)
            ->setParameter('registrationDate', $registrationDate)
            ->setParameter('ifWoman', $ifWoman)
            ->setParameter('currentDate', $date)
            ->orderBy('p.poczatekpromocji', 'ASC')
            ->getQuery();

        return $query->execute();
    }

    public function findCurrentForVisitor(){
        $date = date("Y-m-d");
        $query = $this->createQueryBuilder('p')
            ->andWhere('p.koniecpromocji >= :currentDate AND p.poczatekpromocji <= :currentDate')
            ->andWhere('p.staz IS NULL')
            ->andWhere('p.czykobieta IS NULL')
            ->setParameter('currentDate', $date)
            ->orderBy('p.poczatekpromocji', 'ASC')
            ->getQuery();

        return $query->execute();
    }

    public function getPromotionToCheckForVisitor($promotionId){
        $date = date("Y-m-d");
        $query = $this->createQueryBuilder('p')
            ->andWhere('p.koniecpromocji >= :currentDate AND p.poczatekpromocji <= :currentDate')
            ->andWhere('p.staz IS NULL')
            ->andWhere('p.czykobieta IS NULL')
            ->andWhere('p.id = :promotionId')
            ->setParameter('promotionId', $promotionId)
            ->setParameter('currentDate', $date)
            ->orderBy('p.poczatekpromocji', 'ASC')
            ->getQuery();

        return $query->execute();
    }
}