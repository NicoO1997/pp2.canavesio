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
    #[Assert\NotBlank(message: "El nombre no puede estar vacío")]
    private ?string $machineryName = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "La marca no puede estar vacía")]
    private ?string $brand = null;

    #[ORM\Column]
    private ?int $yearsOld = 0;

    #[ORM\Column]
    private ?int $months = 0;

    #[ORM\Column]
    private ?int $hoursOfUse = 0;

    #[ORM\Column(type: 'date')]
    private ?\DateTimeInterface $lastService = null;

    #[ORM\Column]
    #[Assert\GreaterThan(0, message: "El precio debe ser mayor a 0")]
    private ?float $price = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "La categoría es obligatoria")]
    private ?string $category = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imageFilename = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isNew = false;

    public function getIsNew(): bool
    {
        return $this->isNew;
    }

    public function setIsNew(bool $isNew): self
    {
        $this->isNew = $isNew;
        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMachineryName(): ?string
    {
        return $this->machineryName;
    }

    public function setMachineryName(string $machineryName): self
    {
        $this->machineryName = $machineryName;
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

    public function getYearsOld(): ?int
    {
        return $this->yearsOld;
    }

    public function setYearsOld(int $yearsOld): self
    {
        $this->yearsOld = $yearsOld;
        return $this;
    }

    public function getMonths(): ?int
    {
        return $this->months;
    }

    public function setMonths(int $months): self
    {
        $this->months = $months;
        return $this;
    }

    public function getHoursOfUse(): ?int
    {
        return $this->hoursOfUse;
    }

    public function setHoursOfUse(int $hoursOfUse): self
    {
        $this->hoursOfUse = $hoursOfUse;
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

    public function getImageFilename(): ?string
    {
        return $this->imageFilename;
    }

    public function setImageFilename(?string $imageFilename): self
    {
        $this->imageFilename = $imageFilename;
        return $this;
    }

    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context, $payload)
    {
        if ($this->isNew) {
            return;
        }
        
        if (null === $this->yearsOld || ($this->yearsOld == 0 && $this->months == 0)) {
            $context->buildViolation('La maquinaria usada debe tener al menos 1 mes de antigüedad')
                ->atPath('yearsOld')
                ->addViolation();
        }
        
        if (null === $this->months || $this->months < 0 || $this->months > 11) {
            $context->buildViolation('Los meses deben estar entre 0 y 11')
                ->atPath('months')
                ->addViolation();
        }
        
        if (null === $this->hoursOfUse || $this->hoursOfUse < 0) {
            $context->buildViolation('Las horas de uso deben ser 0 o mayores')
                ->atPath('hoursOfUse')
                ->addViolation();
        }
        
        if (null === $this->lastService) {
            $context->buildViolation('La fecha de último servicio no puede estar vacía')
                ->atPath('lastService')
                ->addViolation();
        } elseif ($this->lastService > new \DateTime()) {
            $context->buildViolation('La fecha de último servicio no puede ser posterior al día de hoy')
                ->atPath('lastService')
                ->addViolation();
        }
    }
}