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

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "La categoría es obligatoria")]
    private ?string $category = null;
    
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "La ubicación es obligatoria")]
    #[Assert\Regex(
        pattern: "/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/",
        message: "La ubicación solo puede contener letras"
    )]
    private ?string $location = null;

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
        $this->brand = $brand;
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

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): self
    {
        $this->category = $category;
        return $this;
    }
    
    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(string $location): self
    {
        $this->location = $location;
        return $this;
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

        if (in_array($this->category, ['sembradora', 'pulverizadora', 'tolva']) && 
            (null === $this->loadCapacity || $this->loadCapacity <= 0)) {
            $context->buildViolation('La capacidad de carga debe ser mayor a 0 para esta categoría')
                ->atPath('loadCapacity')
                ->addViolation();
        }
    }
}