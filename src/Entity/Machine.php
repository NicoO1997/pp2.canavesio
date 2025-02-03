<?php

namespace App\Entity;

use App\Repository\MachineRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MachineRepository::class)]
class Machine
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?int $quantity = null;

    #[ORM\Column(length: 255)]
    private ?string $brand = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $price = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $image = null;

    /**
     * @var Collection<int, ProductPartsMachine>
     */
    #[ORM\OneToMany(targetEntity: ProductPartsMachine::class, mappedBy: 'machine')]
    private Collection $productPartsMachine;

    /**
     * @var Collection<int, UserFavoriteProduct>
     */
    #[ORM\OneToMany(targetEntity: UserFavoriteProduct::class, mappedBy: 'machine')]
    private Collection $userFavoriteProduct;

   

    public function __construct()
    {
        $this->productPartsMachine = new ArrayCollection();
        $this->userFavoriteProduct = new ArrayCollection();
        
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(string $brand): static
    {
        $this->brand = $brand;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(?string $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): static
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @return Collection<int, ProductPartsMachine>
     */
    public function getProductPartsMachine(): Collection
    {
        return $this->productPartsMachine;
    }

    public function addProductPartsMachine(ProductPartsMachine $productPartsMachine): static
    {
        if (!$this->productPartsMachine->contains($productPartsMachine)) {
            $this->productPartsMachine->add($productPartsMachine);
            $productPartsMachine->setMachine($this);
        }

        return $this;
    }

    public function removeProductPartsMachine(ProductPartsMachine $productPartsMachine): static
    {
        if ($this->productPartsMachine->removeElement($productPartsMachine)) {
            // set the owning side to null (unless already changed)
            if ($productPartsMachine->getMachine() === $this) {
                $productPartsMachine->setMachine(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, UserFavoriteProduct>
     */
    public function getUserFavoriteProduct(): Collection
    {
        return $this->userFavoriteProduct;
    }

    public function addUserFavoriteProduct(UserFavoriteProduct $userFavoriteProduct): static
    {
        if (!$this->userFavoriteProduct->contains($userFavoriteProduct)) {
            $this->userFavoriteProduct->add($userFavoriteProduct);
            $userFavoriteProduct->setMachine($this);
        }

        return $this;
    }

    public function removeUserFavoriteProduct(UserFavoriteProduct $userFavoriteProduct): static
    {
        if ($this->userFavoriteProduct->removeElement($userFavoriteProduct)) {
            // set the owning side to null (unless already changed)
            if ($userFavoriteProduct->getMachine() === $this) {
                $userFavoriteProduct->setMachine(null);
            }
        }

        return $this;
    }

    
}
