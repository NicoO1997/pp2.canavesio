<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use DateTime;
use DateTimeZone;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'El nombre no puede estar vacío')]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'La descripción no puede estar vacía')]
    private ?string $description = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'El precio es obligatorio')]
    #[Assert\GreaterThanOrEqual(0, message: 'El precio no puede ser inferior a cero')]
    private ?float $price = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'La cantidad es obligatoria')]
    #[Assert\GreaterThanOrEqual(0, message: 'La cantidad no puede ser inferior a cero')]
    private ?int $quantity = null;

    #[ORM\Column]
    private ?bool $isEnabled = false;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'La marca no puede estar vacía')]
    private ?string $brand = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'El stock mínimo es obligatorio')]
    #[Assert\GreaterThanOrEqual(0, message: 'El stock mínimo no puede ser inferior a cero')]
    private ?int $minStock = null;

    // #[ORM\Column(type: Types::TEXT, nullable: true)]
    // private ?string $image = null;
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'El código de parte es obligatorio')]
    private ?string $partNumber = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'Debe especificar los modelos compatibles')]
    private ?string $compatibleModels = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $dimensions = null;

    #[ORM\Column(length: 255)]
    private ?string $material = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $weight = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $loadCapacity = null;

    #[ORM\Column(nullable: true)]
    private ?int $estimatedLifeHours = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $mountingType = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $installationRequirements = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $partType = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(length: 255)]
    private ?string $createdBy = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $updatedBy = null;

    #[ORM\OneToMany(targetEntity: CartProductOrder::class, mappedBy: 'product', orphanRemoval: true)]
    private Collection $cartProductOrder;

    #[ORM\Column(type: 'boolean')]
    private bool $isDeleted = false;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $deletedAt = null;

    public function __construct()
    {
        $this->cartProductOrder = new ArrayCollection();
{
        $this->createdAt = new DateTime();
}
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;
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

    public function isIsEnabled(): ?bool
    {
        return $this->isEnabled;
    }

    public function setIsEnabled(bool $isEnabled): self
    {
        $this->isEnabled = $isEnabled;
        return $this;
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(string $brand): self
    {
        $this->brand = $brand;
        return $this;
    }

    public function getMinStock(): ?int
    {
        return $this->minStock;
    }

    public function setMinStock(int $minStock): self
    {
        $this->minStock = $minStock;
        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;
        return $this;
    }

    public function getPartNumber(): ?string
    {
        return $this->partNumber;
    }

    public function setPartNumber(string $partNumber): self
    {
        $this->partNumber = $partNumber;
        return $this;
    }

    public function getCompatibleModels(): ?string
    {
        return $this->compatibleModels;
    }

    public function setCompatibleModels(string $compatibleModels): self
    {
        $this->compatibleModels = $compatibleModels;
        return $this;
    }

    public function getDimensions(): ?string
    {
        return $this->dimensions;
    }

    public function setDimensions(?string $dimensions): self
    {
        $this->dimensions = $dimensions;
        return $this;
    }

    public function getMaterial(): ?string
    {
        return $this->material;
    }

    public function setMaterial(string $material): self
    {
        $this->material = $material;
        return $this;
    }

    public function getWeight(): ?string
    {
        return $this->weight;
    }

    public function setWeight(?string $weight): self
    {
        $this->weight = $weight;
        return $this;
    }

    public function getLoadCapacity(): ?string
    {
        return $this->loadCapacity;
    }

    public function setLoadCapacity(?string $loadCapacity): self
    {
        $this->loadCapacity = $loadCapacity;
        return $this;
    }

    public function getEstimatedLifeHours(): ?int
    {
        return $this->estimatedLifeHours;
    }

    public function setEstimatedLifeHours(?int $estimatedLifeHours): self
    {
        $this->estimatedLifeHours = $estimatedLifeHours;
        return $this;
    }

    public function getMountingType(): ?string
    {
        return $this->mountingType;
    }

    public function setMountingType(?string $mountingType): self
    {
        $this->mountingType = $mountingType;
        return $this;
    }

    public function getInstallationRequirements(): ?string
    {
        return $this->installationRequirements;
    }

    public function setInstallationRequirements(?string $installationRequirements): self
    {
        $this->installationRequirements = $installationRequirements;
        return $this;
    }

    public function getPartType(): ?string
    {
        return $this->partType;
    }

    public function setPartType(string $partType): self
    {
        $this->partType = $partType;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): self
{
    $this->createdAt = $createdAt;
    return $this;
}

    public function getCreatedBy(): ?string
    {
        return $this->createdBy;
    }

    public function setCreatedBy(string $createdBy): self
    {
        $this->createdBy = $createdBy;
        return $this;
    }

     /**
     * Determina si el producto necesita reabastecimiento basado en el stock mínimo
     */
    public function needsRestock(): bool
    {
        return $this->quantity <= $this->minStock;
    }

    /**
     * Calcula el porcentaje de stock restante
     */
    public function getStockPercentage(): float
    {
        if ($this->minStock === 0) {
            return 100;
        }
        
        return ($this->quantity / $this->minStock) * 100;
    }

    /**
     * Obtiene el estado del stock como texto
     */
    public function getStockStatus(): string
    {
        if ($this->quantity <= 0) {
            return 'Sin stock';
        }
        
        if ($this->needsRestock()) {
            return 'Stock bajo';
        }
        
        return 'En stock';
    }

    /**
     * Obtiene el color del estado del stock para la UI
     */
    public function getStockStatusColor(): string
    {
        if ($this->quantity <= 0) {
            return 'danger';
        }
        
        if ($this->needsRestock()) {
            return 'warning';
        }
        
        return 'success';
    }

    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new DateTime('2025-03-03 15:30:19');
        $this->updatedBy = 'SantiAragon';
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getUpdatedBy(): ?string
    {
        return $this->updatedBy;
    }

    public function setUpdatedBy(?string $updatedBy): self
    {
        $this->updatedBy = $updatedBy;
        return $this;
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new DateTime();
    }

    /**
     * @return Collection<int, CartProductOrder>
     */
    public function getCartProductOrder(): Collection
    {
        return $this->cartProductOrder;
    }

    public function addCartProductOrder(CartProductOrder $cartProductOrder): self
    {
        if (!$this->cartProductOrder->contains($cartProductOrder)) {
            $this->cartProductOrder->add($cartProductOrder);
            $cartProductOrder->setProduct($this);
        }

        return $this;
    }

    public function removeCartProductOrder(CartProductOrder $cartProductOrder): self
    {
        if ($this->cartProductOrder->removeElement($cartProductOrder)) {
            // set the owning side to null (unless already changed)
            if ($cartProductOrder->getProduct() === $this) {
                $cartProductOrder->setProduct(null);
            }
        }

        return $this;
    }

    public function hasEnoughStock(): bool
    {
        return $this->quantity > 0;
    }

    #[ORM\PrePersist]
    public function setTimestamps(): void
    {
        $argentinaTime = new DateTime('now', new DateTimeZone('America/Argentina/Buenos_Aires'));
        $argentinaTime->setTimezone(new DateTimeZone('America/Argentina/Buenos_Aires'));
        
        $this->setCreatedAt($argentinaTime);
        $this->setCreatedBy('SantiAragon');
    }

    #[ORM\PreUpdate]
    public function updateTimestamps(): void
    {
        $argentinaTime = new DateTime('now', new DateTimeZone('America/Argentina/Buenos_Aires'));
        $argentinaTime->setTimezone(new DateTimeZone('America/Argentina/Buenos_Aires'));
        
        $this->setUpdatedAt($argentinaTime);
        $this->setUpdatedBy('SantiAragon');
    }

    public function isDeleted(): bool
    {
        return $this->isDeleted;
    }

    public function getDeletedAt(): ?\DateTimeInterface
    {
        return $this->deletedAt;
    }

    public function softDelete(): void
    {
        $this->isDeleted = true;
        $this->deletedAt = new DateTime();
    }

    public function restore(): void
    {
        $this->isDeleted = false;
        $this->deletedAt = null;
    }
}