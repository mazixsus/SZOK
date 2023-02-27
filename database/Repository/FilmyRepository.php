<?php
/**
 * Created by PhpStorm.
 * User: gnowa
 * Date: 18.11.2018
 * Time: 14:04
 */

namespace App\Repository;

use App\Entity\Filmy;
use App\Entity\Kategoriewiekowe;
use App\Entity\Rodzajefilmow;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bridge\Doctrine\RegistryInterface;

class FilmyRepository extends ServiceEntityRepository
{

    /**
     * FilmyRepository constructor.
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Filmy::class);
    }

    public function findPage($page = 1, $pageLimit = 10, ?Rodzajefilmow $movieType = NULL)
    {
        $query = $this->createQueryBuilder('f')
            ->orderBy('f.datapremiery', 'DESC');

        if($movieType){
            $query->join('f.rodzajefilmow', 'mt')
                ->andWhere(':movieType = mt')
                ->setParameter('movieType', $movieType);
        }

        $query = $query->getQuery();

        $requestedPage = new Paginator($query);

        $requestedPage->getQuery()
            ->setFirstResult($pageLimit * ($page - 1))
            ->setMaxResults($pageLimit);

        return $requestedPage;
    }

    public function getPageCount($pageLimit = 10, ?Rodzajefilmow $movieType = NULL)
    {
        $query = $this->createQueryBuilder('f')
            ->select('count(f.id)')
            ->orderBy('f.datapremiery', 'DESC');

        if($movieType){
            $query->join('f.rodzajefilmow', 'mt')
                ->andWhere(':movieType = mt')
                ->setParameter('movieType', $movieType);
        }

        $query = $query->getQuery();

        $count = $query->getSingleScalarResult();

        $pageCount = floor($count / $pageLimit);
        $rest = $count % $pageLimit;
        if($rest != 0) {
            $pageCount = $pageCount + 1;
        }

        return $pageCount;
    }

    public function findPageOfActual($page = 1, $pageLimit = 10, ?Rodzajefilmow $movieType = NULL)
    {
        $query = $this->createQueryBuilder('f')
            ->select('DISTINCT f')
            ->leftJoin('f.seansMaFilmy', 'smf')
            ->leftJoin('smf.seanse', 's')
            ->andWhere('smf.id IS NULL OR s.poczatekseansu >= :date')
            ->setParameter('date', date('Y-m-d'))
            ->orderBy('f.datapremiery', 'DESC');

        if($movieType){
            $query->join('f.rodzajefilmow', 'mt')
                ->andWhere(':movieType = mt')
                ->setParameter('movieType', $movieType);
        }

        $query = $query->getQuery();

        $requestedPage = new Paginator($query);

        $requestedPage->getQuery()
            ->setFirstResult($pageLimit * ($page - 1))
            ->setMaxResults($pageLimit);

        return $requestedPage;
    }

    public function getPageCountOfActual($pageLimit = 10, ?Rodzajefilmow $movieType = NULL)
    {
        $query = $this->createQueryBuilder('f')
            ->select('count(DISTINCT f.id)')
            ->leftJoin('f.seansMaFilmy', 'smf')
            ->leftJoin('smf.seanse', 's')
            ->andWhere('smf.id IS NULL OR s.poczatekseansu >= :date')
            ->setParameter('date', date('Y-m-d'))
            ->orderBy('f.datapremiery', 'DESC');

        if($movieType){
            $query->join('f.rodzajefilmow', 'mt')
                ->andWhere(':movieType = mt')
                ->setParameter('movieType', $movieType);
        }

        $query = $query->getQuery();

        $count = $query->getSingleScalarResult();

        $pageCount = floor($count / $pageLimit);
        $rest = $count % $pageLimit;
        if($rest != 0) {
            $pageCount = $pageCount + 1;
        }

        return $pageCount;
    }
}