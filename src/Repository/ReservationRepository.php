<?php

namespace App\Repository;

use App\Entity\Reservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use DateTime;

/**
 * @extends ServiceEntityRepository<Reservation>
 *
 * @method Reservation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Reservation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Reservation[]    findAll()
 * @method Reservation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    /**
     * Encuentra las reservas activas para un producto específico
     */
    public function findActiveReservationsForProduct($productId): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.product = :productId')
            ->andWhere('r.status = :status')
            ->andWhere('r.expiresAt > :now')
            ->setParameter('productId', $productId)
            ->setParameter('status', 'pending')
            ->setParameter('now', new DateTime())
            ->getQuery()
            ->getResult();
    }

    /**
     * Encuentra las reservas próximas a expirar
     */
    public function findExpiringReservations(): array
    {
        $twentyFourHoursFromNow = new DateTime('+24 hours');
        
        return $this->createQueryBuilder('r')
            ->andWhere('r.status = :status')
            ->andWhere('r.expiresAt <= :expiryDate')
            ->andWhere('r.expiresAt > :now')
            ->setParameter('status', 'pending')
            ->setParameter('expiryDate', $twentyFourHoursFromNow)
            ->setParameter('now', new DateTime())
            ->getQuery()
            ->getResult();
    }

    /**
     * Encuentra las reservas expiradas
     */
    public function findExpiredReservations(): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.status = :status')
            ->andWhere('r.expiresAt <= :now')
            ->setParameter('status', 'pending')
            ->setParameter('now', new DateTime())
            ->getQuery()
            ->getResult();
    }

    /**
     * Encuentra las reservas por cliente ordenadas por fecha
     */
    public function findByCustomerOrderByDate($customerId): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.customer = :customerId')
            ->setParameter('customerId', $customerId)
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Encuentra las reservas por vendedor ordenadas por fecha
     */
    public function findBySellerOrderByDate($sellerId): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.seller = :sellerId')
            ->setParameter('sellerId', $sellerId)
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Encuentra las reservas pendientes para un producto
     */
    public function findPendingQuantityForProduct($productId): int
    {
        $result = $this->createQueryBuilder('r')
            ->select('SUM(r.quantity)')
            ->andWhere('r.product = :productId')
            ->andWhere('r.status = :status')
            ->andWhere('r.expiresAt > :now')
            ->setParameter('productId', $productId)
            ->setParameter('status', 'pending')
            ->setParameter('now', new DateTime())
            ->getQuery()
            ->getSingleScalarResult();

        return $result ? (int)$result : 0;
    }

    /**
     * Guarda una nueva reserva
     */
    public function save(Reservation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Elimina una reserva
     */
    public function remove(Reservation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}