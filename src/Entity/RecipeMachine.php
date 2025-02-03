<?php
namespace App\Entity;

use App\Repository\RecipeMachineRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RecipeMachineRepository::class)]
class RecipeMachine
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, Parts>
     */
    #[ORM\ManyToMany(targetEntity: Parts::class, inversedBy: 'recipeMachines')]
    private Collection $parts;

    /**
     * @var Collection<int, Product>
     */
    #[ORM\ManyToMany(targetEntity: Product::class, inversedBy: 'recipeMachines')]
    private Collection $products;

    public function __construct()
    {
        $this->parts = new ArrayCollection();
        $this->products = new ArrayCollection();
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

    /**
     * @return Collection<int, Parts>
     */
    public function getParts(): Collection
    {
        return $this->parts;
    }

    public function addPart(Parts $part): static
    {
        if (!$this->parts->contains($part)) {
            $this->parts->add($part);
            $part->addRecipeMachine($this);
        }

        return $this;
    }

    public function removePart(Parts $part): static
    {
        if ($this->parts->removeElement($part)) {
            $part->removeRecipeMachine($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Product>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): static
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
            $product->addRecipeMachine($this);
        }

        return $this;
    }

    public function removeProduct(Product $product): static
    {
        if ($this->products->removeElement($product)) {
            $product->removeRecipeMachine($this);
        }

        return $this;
    }
}