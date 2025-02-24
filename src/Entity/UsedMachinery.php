<?php

namespace App\Entity;

use App\Repository\UsedMachineryRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Validator\Constraints as Assert;

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
    #[Assert\NotBlank(message: "Los años no pueden estar vacíos")]
    #[Assert\GreaterThan(0, message: "Los años deben ser mayores a 0")]
    private ?int $yearsOld = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "Los meses no pueden estar vacíos")]
    #[Assert\Range(
        min: 1,
        max: 11,
        notInRangeMessage: "Los meses deben estar entre {{ min }} y {{ max }}"
    )]
    private ?int $months = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "Las horas de uso no pueden estar vacías")]
    #[Assert\GreaterThanOrEqual(0, message: "Las horas de uso deben ser 0 o mayores")]
    private ?int $hoursOfUse = null;

    #[ORM\Column(type: 'date')]
    #[Assert\NotBlank(message: "La fecha de último servicio no puede estar vacía")]
    #[Assert\LessThanOrEqual("today", message: "La fecha de último servicio no puede ser posterior al día de hoy")]
    private ?\DateTimeInterface $lastService = null;

    #[ORM\Column(nullable: true)]
    #[Assert\GreaterThan(0, message: "El precio debe ser mayor a 0")]
    private ?float $price = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "La categoría es obligatoria")]
    private ?string $category = null;

    #[ORM\Column(nullable: true)]
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

    public function setLastService(\DateTimeInterface $lastService): self
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

    public function setImageFilename(string $imageFilename): self
    {
        $this->imageFilename = $imageFilename;
        return $this;
    }
}