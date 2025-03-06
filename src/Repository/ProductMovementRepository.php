<?php

namespace App\Repository;

use App\Entity\ProductMovement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ProductMovementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductMovement::class);
    }

    public function findByDateRange(\DateTime $start, \DateTime $end)
    {
        return $this->createQueryBuilder('pm')
            ->where('pm.createdAt BETWEEN :start AND :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('pm.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByProduct(int $productId)
    {
        return $this->createQueryBuilder('pm')
            ->where('pm.product = :productId')
            ->setParameter('productId', $productId)
            ->orderBy('pm.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}