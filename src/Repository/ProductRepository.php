<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query;

class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /**
     * @return Product[]
     */
    public function findEnabledProducts(): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.isEnabled = :val')
            ->setParameter('val', true)
            ->orderBy('p.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Query
     */
    public function findEnabledProductsQuery(): Query
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.isEnabled = :val')
            ->setParameter('val', true)
            ->orderBy('p.id', 'DESC')
            ->getQuery();
    }

    /**
     * @return Product[]
     */
    public function findBySearch(?string $search): array
    {
        $qb = $this->createQueryBuilder('p')
            ->andWhere('p.isEnabled = :enabled')
            ->setParameter('enabled', true);

        if ($search) {
            $qb->andWhere('p.name LIKE :search OR p.description LIKE :search OR p.brand LIKE :search OR p.partNumber LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        return $qb
            ->orderBy('p.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Product[]
     */
    public function findEnabledProductsByCategory(string $category): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.isEnabled = :enabled')
            ->andWhere('p.partType LIKE :category')
            ->setParameter('enabled', true)
            ->setParameter('category', $category . '%')
            ->orderBy('p.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Product[]
     */
    public function findLowStockProducts(): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.quantity <= p.minStock')
            ->orderBy('p.quantity', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findOneByPartNumber(string $partNumber): ?Product
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.partNumber = :partNumber')
            ->setParameter('partNumber', $partNumber)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return Product[]
     */
    public function findSimilarProducts(Product $product, int $limit = 4): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.id != :id')
            ->andWhere('p.isEnabled = :enabled')
            ->andWhere('p.partType = :partType')
            ->setParameter('id', $product->getId())
            ->setParameter('enabled', true)
            ->setParameter('partType', $product->getPartType())
            ->setMaxResults($limit)
            ->orderBy('RAND()')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array<string>
     */
    public function findAllBrands(): array
    {
        return $this->createQueryBuilder('p')
            ->select('DISTINCT p.brand')
            ->orderBy('p.brand', 'ASC')
            ->getQuery()
            ->getSingleColumnResult();
    }

    /**
     * @return array<string>
     */
    public function findAllPartTypes(): array
    {
        return $this->createQueryBuilder('p')
            ->select('DISTINCT p.partType')
            ->orderBy('p.partType', 'ASC')
            ->getQuery()
            ->getSingleColumnResult();
    }

    /**
     * @return Product[]
     */
    public function findByFilters(array $filters): array
    {
        $qb = $this->createQueryBuilder('p')
            ->andWhere('p.isEnabled = :enabled')
            ->setParameter('enabled', true);

        if (!empty($filters['search'])) {
            $qb->andWhere('p.name LIKE :search OR p.description LIKE :search OR p.brand LIKE :search OR p.partNumber LIKE :search')
                ->setParameter('search', '%' . $filters['search'] . '%');
        }

        if (!empty($filters['brand'])) {
            $qb->andWhere('p.brand = :brand')
                ->setParameter('brand', $filters['brand']);
        }

        if (!empty($filters['partType'])) {
            $qb->andWhere('p.partType LIKE :partType')
                ->setParameter('partType', $filters['partType'] . '%');
        }

        if (isset($filters['minPrice'])) {
            $qb->andWhere('p.price >= :minPrice')
                ->setParameter('minPrice', $filters['minPrice']);
        }

        if (isset($filters['maxPrice'])) {
            $qb->andWhere('p.price <= :maxPrice')
                ->setParameter('maxPrice', $filters['maxPrice']);
        }

        if (isset($filters['inStock'])) {
            $qb->andWhere('p.quantity > 0');
        }

        return $qb
            ->orderBy('p.id', 'DESC')
            ->getQuery()
            ->getResult();
    }
}