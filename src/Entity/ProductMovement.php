<?php

namespace App\Entity;

use App\Repository\ProductMovementRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductMovementRepository::class)]
class ProductMovement
{
    // Tipos de movimiento existentes
    public const TYPE_ENTRY = 'entry';
    public const TYPE_SALE = 'sale';
    public const TYPE_DELETION = 'deletion';
    public const TYPE_ADJUSTMENT = 'adjustment';
    public const TYPE_EDIT = 'edit';
    public const TYPE_PERMANENT_DELETE = 'permanent_delete';
    public const TYPE_DELIVERY = 'delivery';
    public const TYPE_RESERVATION = 'reservation';

    // Tipos de movimiento para el carrito
    public const TYPE_RESERVED = 'reserved';
    public const TYPE_RESERVATION_CANCELLED = 'reservation_cancelled';
    public const TYPE_RESERVATION_RESTORED = 'reservation_restored';
    public const TYPE_PURCHASE_COMPLETED = 'purchase_completed';
    public const TYPE_PURCHASE_CANCELLED = 'purchase_cancelled';
    public const TYPE_RESERVED_SALE = 'reserved_sale';

    /**
     * Mapa de tipos de movimiento a sus descripciones en español
     */
    public const TYPE_DESCRIPTIONS = [
        self::TYPE_ENTRY => 'Entrada',
        self::TYPE_SALE => 'Venta',
        self::TYPE_DELETION => 'Eliminación Lógica',
        self::TYPE_ADJUSTMENT => 'Ajuste',
        self::TYPE_EDIT => 'Editado',
        self::TYPE_PERMANENT_DELETE => 'Eliminación Permanente',
        self::TYPE_RESERVED => 'Reservado en Carrito',
        self::TYPE_RESERVATION_CANCELLED => 'Reserva Cancelada',
        self::TYPE_RESERVATION_RESTORED => 'Reserva Restaurada',
        self::TYPE_PURCHASE_COMPLETED => 'Compra Completada',
        self::TYPE_PURCHASE_CANCELLED => 'Compra Cancelada',
        self::TYPE_RESERVED_SALE => 'Venta de Producto Reservado',
        self::TYPE_DELIVERY => 'Entrega Física de Reserva',
        self::TYPE_RESERVATION => 'Repuestos reservados',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Product::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: "SET NULL")]  // Cambiar a nullable true y SET NULL
    private ?Product $product = null;

    #[ORM\Column(length: 25)]
    private ?string $movementType = null;

    #[ORM\Column]
    private ?int $quantity = null;

    #[ORM\Column]
    private ?int $previousStock = null;

    #[ORM\Column]
    private ?int $newStock = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    private ?string $performedBy = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true)]
    private ?string $orderId = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime('now', new \DateTimeZone('UTC'));
    }


    /**
     * Crea un nuevo movimiento de producto
     */
    public static function create(
        Product $product,
        string $movementType,
        int $quantity,
        int $previousStock,
        int $newStock,
        string $performedBy = 'SantiAragon',
        ?string $description = null,
        ?string $orderId = null
    ): self {
        $movement = new self();
        $movement->setProduct($product);
        $movement->setMovementType($movementType);
        $movement->setQuantity($quantity);
        $movement->setPreviousStock($previousStock);
        $movement->setNewStock($newStock);
        $movement->setPerformedBy($performedBy);
        $movement->setDescription($description);
        $movement->setOrderId($orderId);

        return $movement;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;
        return $this;
    }

    public function getMovementType(): ?string
    {
        return $this->movementType;
    }

    /**
     * Obtiene la descripción en español del tipo de movimiento
     */
    public function getMovementTypeDescription(): string
    {
        return self::TYPE_DESCRIPTIONS[$this->movementType] ?? 'Desconocido';
    }

    public function setMovementType(string $movementType): self
    {
        if (!isset(self::TYPE_DESCRIPTIONS[$movementType])) {
            throw new \InvalidArgumentException('Tipo de movimiento inválido');
        }
        $this->movementType = $movementType;
        return $this;
    }


    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function getPreviousStock(): ?int
    {
        return $this->previousStock;
    }

    public function setPreviousStock(int $previousStock): self
    {
        $this->previousStock = $previousStock;
        return $this;
    }

    public function getNewStock(): ?int
    {
        return $this->newStock;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getPerformedBy(): ?string
    {
        return $this->performedBy;
    }

    public function setPerformedBy(string $performedBy): self
    {
        $this->performedBy = $performedBy;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * Obtiene la fecha de creación formateada en UTC
     */
    public function getFormattedCreatedAt(): string
    {
        return $this->createdAt->format('Y-m-d H:i:s');
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * Calcula la diferencia de stock
     */
    public function getStockDifference(): int
    {
        return $this->newStock - $this->previousStock;
    }

    /**
     * Indica si el movimiento representa un incremento en el stock
     */
    public function isStockIncrease(): bool
    {
        return $this->getStockDifference() > 0;
    }

    /**
     * Indica si el movimiento representa un decremento en el stock
     */
    public function isStockDecrease(): bool
    {
        return $this->getStockDifference() < 0;
    }

    public function getOrderId(): ?string
    {
        return $this->orderId;
    }

    public function setOrderId(?string $orderId): self
    {
        $this->orderId = $orderId;
        return $this;
    }

    /**
     * Indica si el movimiento está relacionado con el carrito
     */
    /**
 * Indica si el movimiento está relacionado con el carrito
 */
public function isCartRelated(): bool
{
    return in_array($this->movementType, [
        self::TYPE_RESERVED,
        self::TYPE_RESERVATION_CANCELLED,
        self::TYPE_RESERVATION_RESTORED,
        self::TYPE_PURCHASE_COMPLETED,
        self::TYPE_PURCHASE_CANCELLED
    ]);
}

    /**
 * Obtiene el estado de la reserva
 */
public function getReservationStatus(): string
{
    switch ($this->movementType) {
        case self::TYPE_RESERVED:
            return 'Reservado';
        case self::TYPE_PURCHASE_COMPLETED:
            return 'Compra Completada';
        case self::TYPE_RESERVATION_CANCELLED:
            return 'Reserva Cancelada';
        case self::TYPE_PURCHASE_CANCELLED:
            return 'Compra Cancelada';
        case self::TYPE_RESERVATION_RESTORED:
            return 'Reserva Restaurada';
        default:
            return 'No Aplica';
    }
}

    public function setNewStock(int $newStock): self
    {
        $this->newStock = $newStock;
        return $this;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;
        return $this;
    }

    public function isPermanentDeletion(): bool
    {
        return $this->movementType === self::TYPE_PERMANENT_DELETE;
    }

    /**
     * Indica si el movimiento afecta al stock físico
     */
    public function affectsPhysicalStock(): bool
    {
        return in_array($this->movementType, [
            self::TYPE_ENTRY,
            self::TYPE_SALE,
            self::TYPE_ADJUSTMENT,
            self::TYPE_EDIT,
            self::TYPE_PERMANENT_DELETE
        ]);
    }

    /**
     * Obtiene un mensaje descriptivo del movimiento
     */
    public function getMovementDescription(): string
    {
        $baseDescription = $this->getMovementTypeDescription();

        if ($this->isPermanentDeletion()) {
            return sprintf(
                "%s - Producto: %s (Eliminado permanentemente por %s)",
                $baseDescription,
                $this->product->getName(),
                $this->performedBy
            );
        }

        return sprintf(
            "%s - Cantidad: %d - Stock anterior: %d - Nuevo stock: %d",
            $baseDescription,
            $this->quantity,
            $this->previousStock,
            $this->newStock
        );
    }
}