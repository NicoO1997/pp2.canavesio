<?php

namespace App\Entity;

use App\Repository\ReservationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use DateTime;
use DateTimeInterface;
use DateTimeZone;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $seller = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $customer = null;

    #[ORM\ManyToOne(targetEntity: Product::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?Product $product = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'La cantidad es obligatoria')]
    #[Assert\GreaterThan(value: 0, message: 'La cantidad debe ser mayor a 0')]
    private ?int $quantity = null;

    #[ORM\Column(type: 'datetime')]
    private ?DateTimeInterface $createdAt = null;

    #[ORM\Column(length: 255)]
    private ?string $createdBy = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?DateTimeInterface $updatedAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $updatedBy = null;

    #[ORM\Column(type: 'datetime')]
    private ?DateTimeInterface $expiresAt = null;

    #[ORM\Column(length: 255)]
    private ?string $status = 'pending';

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    public function __construct()
    {
        $this->setTimestamps();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSeller(): ?User
    {
        return $this->seller;
    }

    public function setSeller(?User $seller): self
    {
        $this->seller = $seller;
        return $this;
    }

    public function getCustomer(): ?User
    {
        return $this->customer;
    }

    public function setCustomer(?User $customer): self
    {
        $this->customer = $customer;
        return $this;
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

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;
        return $this;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?DateTimeInterface $createdAt): self
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

    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?DateTimeInterface $updatedAt): self
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

    public function getExpiresAt(): ?DateTimeInterface
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(?DateTimeInterface $expiresAt): self
    {
        $this->expiresAt = $expiresAt;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;
        return $this;
    }

    public function isExpired(): bool
    {
        if (!$this->expiresAt) {
            return false;
        }
        return $this->expiresAt < new DateTime();
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    #[ORM\PrePersist]
    public function setTimestamps(): void
    {
        $argentinaTime = new DateTime('now', new DateTimeZone('America/Argentina/Buenos_Aires'));
        
        $this->createdAt = $argentinaTime;
        $this->createdBy = 'NicoO1997';
        
        // La reserva expira en 48 horas
        $this->expiresAt = (clone $argentinaTime)->modify('+48 hours');
    }

    #[ORM\PreUpdate]
    public function updateTimestamps(): void
    {
        $argentinaTime = new DateTime('now', new DateTimeZone('America/Argentina/Buenos_Aires'));
        
        $this->updatedAt = $argentinaTime;
        $this->updatedBy = 'NicoO1997';
    }

    /**
     * Cancela la reserva y devuelve el stock
     */
    public function cancel(): void
    {
        if ($this->status === 'pending') {
            $this->status = 'cancelled';
            
            // Devolver el stock al producto
            if ($this->product) {
                $this->product->setQuantity(
                    $this->product->getQuantity() + $this->quantity
                );
            }
        }
    }

    /**
     * Completa la reserva
     */
    public function complete(): void
    {
        if ($this->status === 'pending') {
            $this->status = 'completed';
        }
    }

    /**
     * Obtiene el tiempo restante hasta la expiración en formato legible
     */
    public function getTimeRemaining(): string
    {
        if (!$this->expiresAt) {
            return 'Sin fecha de expiración';
        }

        $now = new DateTime();
        $interval = $now->diff($this->expiresAt);

        if ($this->isExpired()) {
            return 'Expirada';
        }

        if ($interval->days > 0) {
            return $interval->format('%d días y %h horas');
        }

        if ($interval->h > 0) {
            return $interval->format('%h horas y %i minutos');
        }

        return $interval->format('%i minutos');
    }

    /**
     * Obtiene el color del estado para la UI
     */
    public function getStatusColor(): string
    {
        return match($this->status) {
            'pending' => 'warning',
            'completed' => 'success',
            'cancelled' => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Obtiene el texto del estado en español
     */
    public function getStatusText(): string
    {
        return match($this->status) {
            'pending' => 'Pendiente',
            'completed' => 'Completada',
            'cancelled' => 'Cancelada',
            default => 'Desconocido',
        };
    }
}