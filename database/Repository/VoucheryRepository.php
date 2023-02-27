<?php
/**
 * Created by PhpStorm.
 * User: Mateusz
 * Date: 17.11.2018
 * Time: 18:29
 */

namespace App\Repository;

use App\Entity\Vouchery;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class VoucheryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vouchery::class);
    }

    public function findVoucherByCode(string $code)
    {
        if(strlen($code) < 28 or !Vouchery::verifyCode($code)) return NULL;
        $id = substr($code, 17, -1);
        $voucher = $this->find($id);
        if(!$voucher or $voucher->getCode() != $code) return NULL;
        return $voucher;
    }

    public function findPage($page = 1, $pageLimit = 10){
        $query = $this->createQueryBuilder('v')
            ->select('v.czaswygenerowania AS czaswygenerowania, v.czykwotowa AS czykwotowa, v.wartosc AS wartosc,
             v.poczatekpromocji AS poczatekpromocji, v.koniecpromocji AS koniecpromocji,
              COUNT(v.id) AS suma,
              SUM(v.czywykorzystany) AS uzyte')
            ->groupBy('v.czaswygenerowania')
            ->orderBy('v.czaswygenerowania', 'DESC')
            ->setFirstResult($pageLimit * ($page - 1))
            ->setMaxResults($pageLimit)
            ->getQuery();

        return $query->execute();
    }

    public function getPageCount($pageLimit = 10)
    {
        $query = $this->createQueryBuilder('v')
            ->select('count(DISTINCT v.czaswygenerowania)')
            ->orderBy('v.czaswygenerowania', 'DESC')
            ->getQuery();

        $count = $query->getSingleScalarResult();

        $pageCount = floor($count / $pageLimit);
        $rest = $count % $pageLimit;
        if($rest != 0) {
            $pageCount = $pageCount + 1;
        }

        return $pageCount;
    }
}