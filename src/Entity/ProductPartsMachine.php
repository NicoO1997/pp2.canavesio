<?php

namespace App\Entity;

use App\Repository\ProductPartsMachineRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductPartsMachineRepository::class)]
class ProductPartsMachine
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?int $quantity = null;

    #[ORM\ManyToOne(inversedBy: 'productPartsMachines')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Product $product = null;

    #[ORM\ManyToOne(inversedBy: 'productPartsMachines')]
    private ?Parts $parts = null;

    #[ORM\ManyToOne(inversedBy: 'productPartsMachine')]
    private ?Machine $machine = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(?int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;

        return $this;
    }

    public function getParts(): ?Parts
    {
        return $this->parts;
    }

    public function setParts(?Parts $parts): static
    {
        $this->parts = $parts;

        return $this;
    }

    public function getMachine(): ?Machine
    {
        return $this->machine;
    }

    public function setMachine(?Machine $machine): static
    {
        $this->machine = $machine;

        return $this;
    }

    public function canCreateProduct(Product $product): bool
    {
        foreach ($product->getParts() as $part) {
            if ($part->getQuantity() <= 0) {
                return false;
            }
        }
        return true;
    }

    public function canCreateMachine(Machine $machine): bool
    {
        foreach ($machine->getProductPartsMachine() as $ppm) {
            if ($ppm->getParts()->getQuantity() <= 0 || $ppm->getProduct()->getQuantity() <= 0) {
                return false;
            }
        }
        return true;
    }


}
