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

    public function findTopSellingProductsByMonth(int $year, int $month): array
    {
        // Asegurar que el mes sea válido (1-12)
        $validMonth = max(1, min(12, $month));
        
        // Construir fechas manualmente como strings
        $startStr = sprintf('%d-%02d-01 00:00:00', $year, $validMonth);
        
        // Determinar el último día del mes
        $lastDayOfMonth = cal_days_in_month(CAL_GREGORIAN, $validMonth, $year);
        $endStr = sprintf('%d-%02d-%02d 23:59:59', $year, $validMonth, $lastDayOfMonth);
        
        $qb = $this->createQueryBuilder('m')
            ->select('p.name AS product_name, SUM(m.quantity) AS total_quantity')
            ->leftJoin('m.product', 'p')
            ->where('m.movementType = :saleType')
            ->andWhere('m.createdAt BETWEEN :start AND :end')
            ->setParameter('saleType', 'sale')  // Usar string directo para evitar problemas
            ->setParameter('start', $startStr)
            ->setParameter('end', $endStr)
            ->groupBy('p.name')
            ->orderBy('total_quantity', 'DESC');

        return $qb->getQuery()->getResult();
    }

    public function findTotalAmountSoldByMonth(int $year, int $month): float
    {
        // Asegurar que el mes sea válido (1-12)
        $validMonth = max(1, min(12, $month));
        
        // Construir fechas manualmente como strings
        $startStr = sprintf('%d-%02d-01 00:00:00', $year, $validMonth);
        
        // Determinar el último día del mes
        $lastDayOfMonth = cal_days_in_month(CAL_GREGORIAN, $validMonth, $year);
        $endStr = sprintf('%d-%02d-%02d 23:59:59', $year, $validMonth, $lastDayOfMonth);
        
        $qb = $this->createQueryBuilder('m')
            ->select('COALESCE(SUM(m.quantity * p.price), 0) AS total_amount')
            ->leftJoin('m.product', 'p')
            ->where('m.movementType = :saleType')
            ->andWhere('m.createdAt BETWEEN :start AND :end')
            ->setParameter('saleType', 'sale')  // Usar string directo para evitar problemas
            ->setParameter('start', $startStr)
            ->setParameter('end', $endStr);

        $result = $qb->getQuery()->getSingleScalarResult();
        return is_numeric($result) ? (float) $result : 0.0;
    }
}