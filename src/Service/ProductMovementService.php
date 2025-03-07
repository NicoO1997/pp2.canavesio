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

    /**
     * Registra un movimiento de producto
     *
     * @param Product $product El producto afectado
     * @param string $movementType Tipo de movimiento (entrada, venta, etc.)
     * @param int $quantity Cantidad del movimiento
     * @param string|null $description Descripción opcional del movimiento
     * @param string|null $performedBy Usuario que realiza el movimiento (opcional)
     */
    public function recordMovement(
        Product $product,
        string $movementType,
        int $quantity,
        ?string $description = null,
        ?string $performedBy = null
    ): void {
        $previousStock = $product->getQuantity();
        $newStock = $previousStock;

        // Calcular el nuevo stock según el tipo de movimiento
        switch ($movementType) {
            case ProductMovement::TYPE_ENTRY:
                $newStock = $previousStock + abs($quantity);
                break;
            case ProductMovement::TYPE_SALE:
            case ProductMovement::TYPE_DELETION:
                $newStock = $previousStock - abs($quantity);
                break;
            case ProductMovement::TYPE_ADJUSTMENT:
                $newStock = $quantity;
                $quantity = $quantity - $previousStock;
                break;
            case ProductMovement::TYPE_EDIT:
                // Para ediciones, la cantidad ya viene calculada
                $newStock = $previousStock + $quantity;
                break;
        }

        // Crear el movimiento usando el método factory de la entidad
        $movement = ProductMovement::create(
            $product,
            $movementType,
            abs($quantity),
            $previousStock,
            $newStock,
            $performedBy ?? 'SantiAragon', // Usar el usuario proporcionado o el default
            $description
        );

        // Configurar la fecha en UTC
        $utcDateTime = new DateTime('now', new DateTimeZone('UTC'));
        $movement->setCreatedAt($utcDateTime);

        // Actualizar el stock del producto
        $product->setQuantity($newStock);

        // Persistir los cambios
        $this->entityManager->persist($movement);
        $this->entityManager->flush();
    }

    /**
     * Registra un movimiento de entrada
     */
    public function recordEntry(Product $product, int $quantity, ?string $description = null): void
    {
        $this->recordMovement($product, ProductMovement::TYPE_ENTRY, abs($quantity), $description);
    }

    /**
     * Registra un movimiento de venta
     */
    public function recordSale(Product $product, int $quantity, ?string $description = null): void
    {
        $this->recordMovement($product, ProductMovement::TYPE_SALE, abs($quantity), $description);
    }

    /**
     * Registra una eliminación
     */
    public function recordDeletion(Product $product, ?string $description = null): void
    {
        $this->recordMovement(
            $product, 
            ProductMovement::TYPE_DELETION, 
            $product->getQuantity(), 
            $description
        );
    }

    /**
     * Registra un ajuste de stock
     */
    public function recordAdjustment(Product $product, int $newQuantity, ?string $description = null): void
    {
        $this->recordMovement($product, ProductMovement::TYPE_ADJUSTMENT, $newQuantity, $description);
    }

    /**
     * Registra una edición de stock
     */
    public function recordEdit(Product $product, int $quantityChange, ?string $description = null): void
    {
        $this->recordMovement($product, ProductMovement::TYPE_EDIT, $quantityChange, $description);
    }
}