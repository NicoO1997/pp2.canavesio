<?php

namespace App\Entity;

use App\Repository\UsedMachineryRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: UsedMachineryRepository::class)]
class UsedMachinery
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "El modelo no puede estar vacío")]
    private ?string $model = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "La marca no puede estar vacía")]
    private ?string $brand = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $manufacturingDate = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    #[Assert\NotBlank(message: "La capacidad del tanque no puede estar vacía")]
    #[Assert\GreaterThan(0, message: "La capacidad del tanque debe ser mayor a 0")]
    private ?float $fuelTankCapacity = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $technology = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?float $loadCapacity = null;

    #[ORM\Column(length: 20)]
    private ?string $transmissionSystem = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $lastService = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $hoursOfUse = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?float $price = null;

    #[ORM\ManyToOne(targetEntity: Category::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Category $category = null;
    
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "La localidad es obligatoria")]
    #[Assert\Regex(
        pattern: "/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/",
        message: "La localidad solo puede contener letras"
    )]
    private ?string $locality = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "La provincia es obligatoria")]
    #[Assert\Regex(
        pattern: "/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/",
        message: "La provincia solo puede contener letras"
    )]
private ?string $provincia = null;

    #[ORM\Column(type: 'json')]
    private array $imageFilenames = [];

    #[ORM\Column(type: 'boolean')]
    private bool $isNew = false;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "El tipo de contribuyente no puede estar vacío")]
    private ?string $taxpayerType = null;

    public function getTaxpayerType(): ?string
    {
        return $this->taxpayerType;
    }

    public function setTaxpayerType(string $taxpayerType): self
    {
        $this->taxpayerType = $taxpayerType;
        return $this;
    }

    public function getPriceWithVAT(): ?float
    {
        if ($this->price === null) {
            return null;
        }
    
        $vatRate = match($this->taxpayerType) {
            'responsable_inscripto' => 0.21,
            'consumidor_final' => 0.10,
            default => 0,
        };
    
        return $this->price * (1 + $vatRate);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(string $model): self
    {
        $this->model = $model;
        return $this;
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(string $brand): self
    {
        $this->brand = implode(' ', array_map(function($word) {
            return ucfirst(strtolower($word));
        }, explode(' ', $brand)));
        
        return $this;
    }
    public function getManufacturingDate(): ?\DateTimeInterface
    {
        return $this->manufacturingDate;
    }

    public function setManufacturingDate(?\DateTimeInterface $manufacturingDate): self
    {
        $this->manufacturingDate = $manufacturingDate;
        return $this;
    }

    public function getFuelTankCapacity(): ?float
    {
        return $this->fuelTankCapacity;
    }

    public function setFuelTankCapacity(float $fuelTankCapacity): self
    {
        $this->fuelTankCapacity = $fuelTankCapacity;
        return $this;
    }

    public function getTechnology(): ?string
    {
        return $this->technology;
    }

    public function setTechnology(?string $technology): self
    {
        $this->technology = $technology;
        return $this;
    }

    public function getLoadCapacity(): ?float
    {
        return $this->loadCapacity;
    }

    public function setLoadCapacity(?float $loadCapacity): self
    {
        $this->loadCapacity = $loadCapacity;
        return $this;
    }

    public function getTransmissionSystem(): ?string
    {
        return $this->transmissionSystem;
    }

    public function setTransmissionSystem(string $transmissionSystem): self
    {
        $this->transmissionSystem = $transmissionSystem;
        return $this;
    }

    public function getLastService(): ?\DateTimeInterface
    {
        return $this->lastService;
    }

    public function setLastService(?\DateTimeInterface $lastService): self
    {
        $this->lastService = $lastService;
        return $this;
    }

    public function getHoursOfUse(): ?int
    {
        return $this->hoursOfUse;
    }

    public function setHoursOfUse(?int $hoursOfUse): self
    {
        $this->hoursOfUse = $hoursOfUse;
        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(?float $price): self
    {
        $this->price = $price;
        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function getCategoryName(): string
    {
        return $this->category ? $this->category->getName() : 'Sin categoría';
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;
        return $this;
    }
    
    public function getLocality(): ?string
{
    return $this->locality;
}

public function setLocality(string $locality): self
{
    $this->locality = $locality;
    return $this;
}

public function getProvincia(): ?string
{
    return $this->provincia;
}

public function setProvincia(string $provincia): self
{
    $this->provincia = $provincia;
    return $this;
}

// Para mantener compatibilidad con código existente
public function getLocation(): string
{
    return $this->locality . ', ' . $this->provincia;
}
    
    public function getImageFilenames(): array
    {
        return is_array($this->imageFilenames) ? $this->imageFilenames : [];
    }
    
    public function setImageFilenames(array $imageFilenames): self
    {
        $this->imageFilenames = $imageFilenames;
        return $this;
    }
    
    public function addImageFilename(string $filename): self
    {
        if (!in_array($filename, $this->imageFilenames)) {
            $this->imageFilenames[] = $filename;
        }
        
        return $this;
    }
    
    public function getFirstImageFilename(): ?string
    {
        return !empty($this->imageFilenames) ? $this->imageFilenames[0] : 'default.jpg';
    }

    public function getIsNew(): bool
    {
        return $this->isNew;
    }

    public function setIsNew(bool $isNew): self
    {
        $this->isNew = $isNew;
        return $this;
    }

    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context, $payload)
    {
        if ($this->isNew) {
            return;
        }
        
        if (null === $this->hoursOfUse || $this->hoursOfUse <= 0) {
            $context->buildViolation('Las horas de uso deben ser mayores a 0 para maquinaria usada')
                ->atPath('hoursOfUse')
                ->addViolation();
        }
        
        if (null === $this->lastService) {
            $context->buildViolation('La fecha de último servicio no puede estar vacía para maquinaria usada')
                ->atPath('lastService')
                ->addViolation();
        } elseif ($this->lastService > new \DateTime()) {
            $context->buildViolation('La fecha de último servicio no puede ser posterior al día de hoy')
                ->atPath('lastService')
                ->addViolation();
        }

        if (null === $this->manufacturingDate) {
            $context->buildViolation('La fecha de fabricación no puede estar vacía para maquinaria usada')
                ->atPath('manufacturingDate')
                ->addViolation();
        } elseif ($this->manufacturingDate > new \DateTime()) {
            $context->buildViolation('La fecha de fabricación no puede ser posterior al día de hoy')
                ->atPath('manufacturingDate')
                ->addViolation();
        }
    }
}