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
        int $quantity,
        ?string $description = null
    ): void {
        $previousStock = $product->getQuantity();
        $newStock = $previousStock;

        switch ($movementType) {
            case ProductMovement::TYPE_ENTRY:
                $newStock = $previousStock + $quantity;
                break;
            case ProductMovement::TYPE_SALE:
            case ProductMovement::TYPE_DELETION:
                $newStock = $previousStock - $quantity;
                break;
            case ProductMovement::TYPE_ADJUSTMENT:
                $newStock = $quantity;
                $quantity = $quantity - $previousStock;
                break;
        }

        $movement = new ProductMovement();
        $movement->setProduct($product);
        $movement->setMovementType($movementType);
        $movement->setQuantity($quantity);
        $movement->setPreviousStock($previousStock);
        $movement->setNewStock($newStock);
        $movement->setDescription($description);
        
        // Obtener el usuario actual
        $user = $this->security->getUser();
        $movement->setPerformedBy($user ? $user->getUserIdentifier() : 'NicoO1997');
        
        // Configurar la fecha y hora en Argentina
        $argentinaTime = new DateTime('now', new DateTimeZone('America/Argentina/Buenos_Aires'));
        $movement->setCreatedAt($argentinaTime);

        $this->entityManager->persist($movement);
        $this->entityManager->flush();
    }
}