<?php
/**
 * Created by PhpStorm.
 * User: Mateusz
 * Date: 11.11.2018
 * Time: 12:27
 */

namespace App\Repository;


use App\Entity\Rzedy;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class RzedyRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Rzedy::class);
    }

    public function getRows($saleId)
    {
        $query = $this->createQueryBuilder('r')
            ->where('r.sale = '.$saleId)
            ->orderBy('r.numerrzedu', 'ASC')
            ->getQuery();

        return $query->execute();
    }

    public function deleteRows($saleId)
    {
        $query = $this->createQueryBuilder('r')
            ->delete()
            ->where('r.sale = '.$saleId)
            ->getQuery();

        $query->execute();
    }

}