<?php

namespace App\Service;

use App\Entity\Product;
use App\Entity\ProductMovement;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use DateTime;
use DateTimeZone;

class ProductMovementService
{
    private $entityManager;
    private $security;

    public function __construct(
        EntityManagerInterface $entityManager,
        Security $security
    ) {
        $this->entityManager = $entityManager;
        $this->security = $security;
    }

    public function recordMovement(
        Product $product,
        string $movementType,
        int $quantityChange,
        int $previousStock,
        int $newStock,
        ?string $description = null,
        ?string $performedBy = null
    ): void {
        $argentinaTime = new DateTime('now', new DateTimeZone('America/Argentina/Buenos_Aires'));

        $movement = new ProductMovement();
        $movement->setProduct($product);
        $movement->setMovementType($movementType);
        $movement->setQuantity($quantityChange);
        $movement->setPreviousStock($previousStock);
        $movement->setNewStock($newStock);
        $movement->setPerformedBy($performedBy ?? 'NicoO1997');
        $movement->setDescription($description);
        $movement->setCreatedAt($argentinaTime);

        $this->entityManager->persist($movement);
        $this->entityManager->flush();
    }

    public function recordEntry(Product $product, int $quantity, ?string $description = null): void
    {
        $previousStock = $product->getQuantity();
        $newStock = $previousStock + $quantity;

        $this->recordMovement(
            $product,
            ProductMovement::TYPE_ENTRY,
            $quantity,
            $previousStock,
            $newStock,
            $description ?? sprintf('Entrada de stock: +%d unidades', $quantity)
        );
    }

    public function recordSale(Product $product, int $quantity, ?string $description = null): void
    {
        $previousStock = $product->getQuantity();
        $newStock = $previousStock - $quantity;

        $this->recordMovement(
            $product,
            ProductMovement::TYPE_SALE,
            -$quantity,
            $previousStock,
            $newStock,
            $description ?? sprintf('Venta de stock: -%d unidades', $quantity)
        );
    }

    public function recordDeletion(Product $product, ?string $description = null): void
    {
        $previousStock = $product->getQuantity();
        $newStock = 0;

        $this->recordMovement(
            $product,
            ProductMovement::TYPE_DELETION,
            -$previousStock,
            $previousStock,
            $newStock,
            $description ?? 'Eliminación del producto'
        );
    }

public function recordPermanentDeletion(Product $product, ?string $description = null): void
{
    $previousStock = $product->getQuantity();
    
    $this->recordMovement(
        $product,
        ProductMovement::TYPE_PERMANENT_DELETE,
        -$previousStock,
        $previousStock,
        0,
        $description ?? sprintf(
            'Eliminación permanente del producto %s realizada por %s',
            $product->getName(),
            'SantiAragon'
        ),
        'SantiAragon'
    );
}
    public function recordAdjustment(Product $product, int $newQuantity, ?string $description = null): void
    {
        $previousStock = $product->getQuantity();
        $quantityChange = $newQuantity - $previousStock;

        $this->recordMovement(
            $product,
            ProductMovement::TYPE_ADJUSTMENT,
            $quantityChange,
            $previousStock,
            $newQuantity,
            $description ?? sprintf('Ajuste de stock de %d a %d unidades', $previousStock, $newQuantity)
        );
    }

    // En ProductMovementService.php
    public function recordEdit(Product $product, int $newQuantity, ?string $description = null): void
    {
        $originalStock = $product->getQuantity();
        $difference = $newQuantity - $originalStock;

        $this->recordMovement(
            $product,
            ProductMovement::TYPE_EDIT,
            $difference,
            $originalStock,
            $newQuantity,
            $description ?? sprintf(
                'Modificación de stock: %s%d unidades. Stock anterior: %d, Stock nuevo: %d',
                $difference > 0 ? '+' : '',
                $difference,
                $originalStock,
                $newQuantity
            )
        );
    }
}