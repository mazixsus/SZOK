<?php
/**
 * Created by PhpStorm.
 * User: Mateusz
 * Date: 11.11.2018
 * Time: 12:40
 */

namespace App\Repository;


use App\Entity\Miejsca;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bridge\Doctrine\RegistryInterface;

class MiejscaRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Miejsca::class);
    }

    public function getSeats($rowId)
    {
        $query = $this->createQueryBuilder('m')
            ->where('m.rzedy = ' . $rowId)
            ->orderBy('m.pozycja', 'ASC')
            ->getQuery();

        return $query->execute();
    }

    public function deleteSeat($rowId)
    {
        $query = $this->createQueryBuilder('m')
            ->delete()
            ->where('m.rzedy = ' . $rowId)
            ->getQuery();

        $query->execute();
    }

    public function getSeatsCount($page = 1, $pageLimit = 10)
    {
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
            'SELECT COUNT(m.id)
            AS liczba
            FROM App\Entity\Miejsca m
            JOIN m.rzedy r
            JOIN r.sale s
            WHERE m.numermiejsca != 0
            GROUP BY s.id
            ORDER BY s.numersali ASC'
        )->setFirstResult($pageLimit * ($page - 1))
            ->setMaxResults($pageLimit);

        return $query->execute();
    }

    public function getSeatsCountOfCurrent($roomId)
    {
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
            'SELECT COUNT(m.id)
            AS liczba
            FROM App\Entity\Miejsca m
            JOIN m.rzedy r
            JOIN r.sale s
            WHERE m.numermiejsca != 0
            AND s.id = :id'
        )->setParameter('id', $roomId);

        return $query->getSingleScalarResult();
    }

    public function getRoom($idRoom, $idSeance)
    {
        set_time_limit(300);
        $query = $this->createQueryBuilder('m')
            ->select('m AS miejsca, 
                            SUM(CASE WHEN se.id != :idSeance THEN 0 ELSE re.id END) AS rezerwacje,
                            SUM(CASE WHEN se2.id != :idSeance THEN 0 ELSE t.id END) AS tranzakcje')
            ->join('m.rzedy', 'r')
            ->join('r.sale', 's')
            ->leftJoin('m.rezerwacje', 're')
            ->leftJoin('re.seanse', 'se')
            ->leftJoin('m.bilety', 'b')
            ->leftjoin('b.tranzakcje', 't')
            ->leftJoin('t.seanse', 'se2')
            ->andWhere('s.id = :idRoom')
            ->setParameter('idRoom', $idRoom)
            ->setParameter('idSeance', $idSeance)
            ->orderBy('r.numerrzedu, m.pozycja', 'ASC')
            ->groupBy('m')
            ->getQuery();

        return $query->execute();
    }

    public function getReservedSeat($idRoom, $idSeance){

        $query = $this->createQueryBuilder('m')
            ->select('m.id')
            ->join('m.rzedy', 'r')
            ->join('m.rezerwacje', 're')
            ->join('re.seanse', 'se')
            ->andWhere('se.id = :idSeance')
            ->setParameter('idSeance', $idSeance)
            ->orderBy('r.numerrzedu, m.pozycja', 'ASC')
            ->groupBy('m')
            ->getQuery();

        return $query->execute();
    }

    public function getSoldSeat($idRoom, $idSeance){

        $query = $this->createQueryBuilder('m')
            ->select('m.id')
            ->join('m.rzedy', 'r')
            ->leftJoin('m.bilety', 'b')
            ->leftjoin('b.tranzakcje', 't')
            ->leftJoin('t.seanse', 'se')
            ->andWhere('se.id = :idSeance')
            ->setParameter('idSeance', $idSeance)
            ->orderBy('r.numerrzedu, m.pozycja', 'ASC')
            ->groupBy('m')
            ->getQuery();

        return $query->execute();
    }

    public function getSeatToCheck($idRoom, $idSeance, $idSeat)
    {
        $query = $this->createQueryBuilder('m')
            ->select('tr.id AS typrzedu,
                            SUM(CASE WHEN se.id != :idSeance THEN 0 ELSE re.id END) AS rezerwacje,
                            SUM(CASE WHEN se2.id != :idSeance THEN 0 ELSE t.id END) AS tranzakcje')
            ->join('m.rzedy', 'r')
            ->join('r.sale', 's')
            ->join('r.typyrzedow', 'tr')
            ->leftJoin('m.rezerwacje', 're')
            ->leftJoin('re.seanse', 'se')
            ->leftJoin('m.bilety', 'b')
            ->leftjoin('b.tranzakcje', 't')
            ->leftJoin('t.seanse', 'se2')
            ->andWhere('s.id = :idRoom')
            ->andWhere('m.id = :idSeat')
            ->setParameter('idSeat', $idSeat)
            ->setParameter('idRoom', $idRoom)
            ->setParameter('idSeance', $idSeance)
            ->orderBy('r.numerrzedu, m.pozycja', 'ASC')
            ->groupBy('m')
            ->getQuery();

        return $query->execute();
    }

    public function getSeatsForReservation($id){
        $query = $this->createQueryBuilder('m')
            ->select('m')
            ->join('m.rzedy', 'rz')
            ->join('m.rezerwacje', 're')
            ->andWhere('re.id = :id')
            ->setParameter('id', $id)
            ->orderBy('rz.numerrzedu, m.numermiejsca', 'ASC')
            ->getQuery();
        return $query->execute();
    }
}