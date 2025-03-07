<?php

namespace App\Entity;

use App\Repository\ProductMovementRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductMovementRepository::class)]
class ProductMovement
{
    public const TYPE_ENTRY = 'entry';
    public const TYPE_SALE = 'sale';
    public const TYPE_DELETION = 'deletion';
    public const TYPE_ADJUSTMENT = 'adjustment';
    public const TYPE_EDIT = 'edit';

    /**
     * Mapa de tipos de movimiento a sus descripciones en español
     */
    public const TYPE_DESCRIPTIONS = [
        self::TYPE_ENTRY => 'Entrada',
        self::TYPE_SALE => 'Venta',
        self::TYPE_DELETION => 'Eliminación',
        self::TYPE_ADJUSTMENT => 'Ajuste',
        self::TYPE_EDIT => 'Editado'
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Product::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Product $product = null;

    #[ORM\Column(length: 20)]
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

    public function __construct()
    {
        // Establecer la fecha actual en UTC
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
        ?string $description = null
    ): self {
        $movement = new self();
        $movement->setProduct($product);
        $movement->setMovementType($movementType);
        $movement->setQuantity($quantity);
        $movement->setPreviousStock($previousStock);
        $movement->setNewStock($newStock);
        $movement->setPerformedBy($performedBy);
        $movement->setDescription($description);
        
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

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;
        return $this;
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

    public function setNewStock(int $newStock): self
    {
        $this->newStock = $newStock;
        return $this;
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
}