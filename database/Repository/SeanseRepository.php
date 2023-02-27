<?php
/**
 * Created by PhpStorm.
 * User: Mateusz
 * Date: 13.11.2018
 * Time: 14:51
 */

namespace App\Repository;

use App\Entity\Seanse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bridge\Doctrine\RegistryInterface;

class SeanseRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Seanse::class);
    }

    public function checkSeancesForRooms($rooms)
    {
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
            'SELECT DISTINCT sa.id
            AS seanse
            FROM App\Entity\Seanse se
            JOIN se.sale sa
            WHERE se.czyodwolany = 0 OR se.czyodwolany IS NULL
            GROUP BY sa.id
            ORDER BY sa.numersali ASC'
        );
        $roomsInSeances = $query->execute();
        $checkRooms = array();
        foreach($rooms as $key => $room) {
            $checkRooms[$key] = !in_array(array('seanse' => $room->getId()), $roomsInSeances);
        }
        return $checkRooms;
    }

    public function getSeance($id)
    {
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
            'SELECT DISTINCT
                se.id,
                f.tytul,
                f.datapremiery, 
                DATE_FORMAT(se.poczatekseansu, \'%d.%m.%Y\') AS data,
                DATE_FORMAT(se.poczatekseansu, \'%H:%i\') AS godzina,
                sa.id AS salaid,
                sa.numersali,
                ts.nazwa
            FROM App\Entity\Seanse se
            JOIN se.sale sa
            JOIN se.typyseansow ts
            JOIN App\Entity\SeansMaFilmy smf
            JOIN smf.filmy f
            WHERE se.id = :id AND smf.seanse = se.id
            AND se.czyodwolany = 0 OR se.czyodwolany IS NULL')
            ->setParameter('id', $id);

        return $query->execute();
    }

    public function findSeancesForMovie(\App\Entity\Filmy $movie,\DateTime $date, $page = 1, $pageLimit = 5)
    {
        $from = new \DateTime($date->format("Y-m-d") . " 00:00:00");
        $to = new \DateTime($date->format("Y-m-d") . " 23:59:59");

        $query = $this->createQueryBuilder('s')
            ->select('s')
            ->join('s.seansMaFilmy', 'smf')
            ->andWhere('smf.filmy = :movie')
            ->andWhere('s.poczatekseansu BETWEEN :from AND :to')
            ->andWhere('s.czyodwolany = 0 OR s.czyodwolany IS NULL')
            ->setParameter('movie', $movie)
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->getQuery();

        $requestedPage = new Paginator($query);

        $requestedPage->getQuery()
            ->setFirstResult($pageLimit * ($page - 1))
            ->setMaxResults($pageLimit);

        return $requestedPage;
    }

    public function getPageCountForMovie(\App\Entity\Filmy $movie,\DateTime $date, $pageLimit = 5)
    {
        $from = new \DateTime($date->format("Y-m-d") . " 00:00:00");
        $to = new \DateTime($date->format("Y-m-d") . " 23:59:59");

        $query = $this->createQueryBuilder('s')
            ->select('count(DISTINCT s.id)')
            ->join('s.seansMaFilmy', 'smf')
            ->andWhere('smf.filmy = :movie')
            ->andWhere('s.poczatekseansu BETWEEN :from AND :to')
            ->andWhere('s.czyodwolany = 0 OR s.czyodwolany IS NULL')
            ->setParameter('movie', $movie)
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->getQuery();

        $count = $query->getSingleScalarResult();

        $pageCount = floor($count / $pageLimit);
        $rest = $count % $pageLimit;
        if($rest != 0) {
            $pageCount = $pageCount + 1;
        }

        return $pageCount;
    }

    public function checkSeancesForMovie(\App\Entity\Filmy $movie)
    {
        $query = $this->createQueryBuilder('s')
            ->select('count(s.id)')
            ->join('s.seansMaFilmy', 'smf')
            ->andWhere('smf.filmy = :movie')
            ->andWhere('s.czyodwolany = 0 OR s.czyodwolany IS NULL')
            ->setParameter('movie', $movie)
            ->getQuery();

        $count = $query->getSingleScalarResult();

        if($count > 0) return false;
        else return true;
    }

    public function endTimeIsInvalid(\DateTime $seanceStartDate, \DateTime $seanceEndDate, \App\Entity\Sale $room, $editedId = NULL)
    {
        $from = \DateTime::createFromFormat('Y-m-d',$seanceStartDate->format('Y-m-d'));
        $to = \DateTime::createFromFormat('Y-m-d',$seanceStartDate->format('Y-m-d'));

        $query = $this->createQueryBuilder('s')
            ->andWhere('s.poczatekseansu BETWEEN :from AND :to')
            ->andWhere('s.sale = :room')
            ->andWhere('s.czyodwolany = 0 OR s.czyodwolany IS NULL')
            ->setParameter('from', $from->sub(new \DateInterval('P1D')))
            ->setParameter('to', $to->add(new \DateInterval('P2D')))
            ->setParameter('room', $room)
            ->getQuery();

        $result = $query->getResult();

        if(!count($result)) return false;

        foreach($result AS $qSeance) {
            if ($editedId and $editedId == $qSeance->getId()) continue;
            $qStart = $qSeance->getPoczatekseansu();
            $qEnd = $qSeance->getSeanceEndTime();
            if(
                ($seanceStartDate <= $qEnd and $seanceStartDate >= $qStart)
                or ($seanceEndDate <= $qEnd and $seanceEndDate >= $qStart)
                or ($seanceStartDate <= $qStart and $seanceEndDate >= $qEnd)
            ) return $qSeance;
        }
        return false;
    }

    public function getProgram(\DateTime $date)
    {
        $from = clone $date->setTime(0,0,0);
        $to = clone $date->setTime(0,0,0);
        $query = $this->createQueryBuilder('s')
            ->select('s')
            ->join('s.seansMaFilmy', 'smf')
            ->join('smf.filmy','f')
            ->andWhere('s.poczatekseansu BETWEEN :from AND :to')
            ->andWhere('s.czyodwolany = 0 OR s.czyodwolany IS NULL')
            ->setParameter('from', $from)
            ->setParameter('to', $to->add(new \DateInterval('P1D')))
            ->orderBy('s.wydarzeniaspecjalne, f.tytul ,s.poczatekseansu' , 'ASC')
            ->getQuery()
            ->getResult();

        return $query;
    }

    public function getProgramRooms(\DateTime $date)
    {
        $from = clone $date->setTime(0,0,0);
        $to = clone $date->setTime(0,0,0);
        $query = $this->createQueryBuilder('s')
            ->select('s')
            ->join('s.seansMaFilmy', 'smf')
            ->join('smf.filmy','f')
            ->andWhere('s.poczatekseansu BETWEEN :from AND :to')
            ->andWhere('s.czyodwolany = 0 OR s.czyodwolany IS NULL')
            ->setParameter('from', $from)
            ->setParameter('to', $to->add(new \DateInterval('P1D')))
            ->orderBy('s.sale, s.poczatekseansu' , 'ASC')
            ->getQuery()
            ->getResult();

        return $query;
    }

    public function getProgramForRooms(\DateTime $date, \App\Entity\Sale $room)
    {
        $from = clone $date->setTime(0,0,0);
        $to = clone $date->setTime(0,0,0);
        $query = $this->createQueryBuilder('s')
            ->select('s')
            ->join('s.seansMaFilmy', 'smf')
            ->join('smf.filmy','f')
            ->andWhere('s.poczatekseansu BETWEEN :from AND :to')
            ->andWhere('s.sale = :room')
            ->andWhere('s.czyodwolany = 0 OR s.czyodwolany IS NULL')
            ->setParameter('from', $from)
            ->setParameter('to', $to->add(new \DateInterval('P1D')))
            ->setParameter('room', $room)
            ->orderBy('s.wydarzeniaspecjalne, f.tytul ,s.poczatekseansu' , 'ASC')
            ->getQuery()
            ->getResult();

        return $query;
    }

    public function getCanceledPage(\DateTime $fromDate, \DateTime $toDate, $page = 1, $pageLimit = 10){
        $query = $this->createQueryBuilder('s')
            ->andWhere('s.czyodwolany = 1')
            ->andWhere('s.poczatekseansu BETWEEN :from AND :to')
            ->setParameter('from', $fromDate)
            ->setParameter('to', $toDate)
            ->orderBy('s.poczatekseansu', 'DESC')
            ->getQuery();

        $requestedPage = new Paginator($query);

        $requestedPage->getQuery()
            ->setFirstResult($pageLimit * ($page - 1))
            ->setMaxResults($pageLimit);

        return $requestedPage;
    }

    public function getCanceledPageCount(\DateTime $fromDate, \DateTime $toDate, $pageLimit = 10){
        $query = $this->createQueryBuilder('s')
            ->select('count(s.id)')
            ->andWhere('s.czyodwolany = 1')
            ->andWhere('s.poczatekseansu BETWEEN :from AND :to')
            ->setParameter('from', $fromDate)
            ->setParameter('to', $toDate)
            ->getQuery();

        $count = $query->getSingleScalarResult();

        $pageCount = floor($count / $pageLimit);
        $rest = $count % $pageLimit;
        if($rest != 0) {
            $pageCount = $pageCount + 1;
        }

        return $pageCount;
    }

    public function getCanceledCount(\DateTime $fromDate, \DateTime $toDate){
        $query = $this->createQueryBuilder('s')
            ->select('count(s.id)')
            ->andWhere('s.czyodwolany = 1')
            ->andWhere('s.poczatekseansu BETWEEN :from AND :to')
            ->setParameter('from', $fromDate)
            ->setParameter('to', $toDate)
            ->getQuery();

        return $query->getSingleScalarResult();
    }
}