<?php

namespace App\Entity;

use App\Repository\PartsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PartsRepository::class)]
class Parts
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?int $quantity = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, Product>
     */
    #[ORM\ManyToMany(targetEntity: Product::class, mappedBy: 'parts')]
    private Collection $product;

    /**
     * @var Collection<int, ProductPartsMachine>
     */
    #[ORM\OneToMany(targetEntity: ProductPartsMachine::class, mappedBy: 'parts', orphanRemoval: true)]
    private Collection $productPartsMachines;

    #[ORM\Column(length: 255)]
    private ?string $brand = null;

    /**
     * @var Collection<int, RecipeMachine>
     */
    #[ORM\ManyToMany(targetEntity: RecipeMachine::class, mappedBy: 'parts')]
    private Collection $recipeMachines;

    /**
     * @var Collection<int, RecipeProduct>
     */
    #[ORM\ManyToMany(targetEntity: RecipeProduct::class, mappedBy: 'parts')]
    private Collection $recipeProducts;

    public function __construct()
    {
        $this->product = new ArrayCollection();
        $this->productPartsMachines = new ArrayCollection();
        $this->recipeMachines = new ArrayCollection();
        $this->recipeProducts = new ArrayCollection();
    }

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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, Product>
     */
    public function getProduct(): Collection
    {
        return $this->product;
    }

    public function addProduct(Product $product): static
    {
        if (!$this->product->contains($product)) {
            $this->product->add($product);
        }

        return $this;
    }

    public function removeProduct(Product $product): static
    {
        $this->product->removeElement($product);

        return $this;
    }

    /**
     * @return Collection<int, ProductPartsMachine>
     */
    public function getProductPartsMachines(): Collection
    {
        return $this->productPartsMachines;
    }

    public function addProductPartsMachine(ProductPartsMachine $productPartsMachine): static
    {
        if (!$this->productPartsMachines->contains($productPartsMachine)) {
            $this->productPartsMachines->add($productPartsMachine);
            $productPartsMachine->setParts($this);
        }

        return $this;
    }

    public function removeProductPartsMachine(ProductPartsMachine $productPartsMachine): static
    {
        if ($this->productPartsMachines->removeElement($productPartsMachine)) {
            // Verificar que la referencia se elimina adecuadamente
            if ($productPartsMachine->getParts() === $this) {
                $productPartsMachine->setParts(null);
            }
        }

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

    /**
     * @return Collection<int, RecipeMachine>
     */
    public function getRecipeMachines(): Collection
    {
        return $this->recipeMachines;
    }

    public function addRecipeMachine(RecipeMachine $recipeMachine): static
    {
        if (!$this->recipeMachines->contains($recipeMachine)) {
            $this->recipeMachines->add($recipeMachine);
            // Sincronización inversa
            $recipeMachine->addPart($this);
        }

        return $this;
    }


    public function removeRecipeMachine(RecipeMachine $recipeMachine): static
    {
        if ($this->recipeMachines->removeElement($recipeMachine)) {
            // Sincronización inversa
            $recipeMachine->removePart($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, RecipeProduct>
     */
    public function getRecipeProducts(): Collection
    {
        return $this->recipeProducts;
    }

    public function addRecipeProduct(RecipeProduct $recipeProduct): static
    {
        if (!$this->recipeProducts->contains($recipeProduct)) {
            $this->recipeProducts->add($recipeProduct);
            $recipeProduct->addPart($this.$recipeProduct);
        }
        return $this;
    }

    public function removeRecipeProduct(RecipeProduct $recipeProduct): static
    {
        if ($this->recipeProducts->removeElement($recipeProduct)) {
            $recipeProduct->removePart($this.$recipeProduct);
        }
        return $this;
    }
}
