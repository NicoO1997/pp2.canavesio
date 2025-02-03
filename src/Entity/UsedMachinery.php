<?php

namespace App\Entity;

use App\Repository\UsedMachineryRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;

#[ORM\Entity(repositoryClass: UsedMachineryRepository::class)]
class UsedMachinery
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $machineryName = null;

    #[ORM\Column(length: 255)]
    private ?string $brand = null;

    #[ORM\Column]
    private ?int $yearsOld = null;

    #[ORM\Column]
    private ?int $months = null;

    #[ORM\Column]
    private ?int $hoursOfUse = null;

    #[ORM\Column(type: 'date')]
    private ?\DateTimeInterface $lastService = null;

    #[ORM\Column(nullable: true)]
    private ?float $price = null;

    /**
     * @Assert\NotBlank(message="La categoría es obligatoria")
     */
    #[ORM\Column(length: 255)]
    private ?string $category = null;

    #[ORM\Column(nullable: true)]
    private ?string $imageFilename = null;

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
