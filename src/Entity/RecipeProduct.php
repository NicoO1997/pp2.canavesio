<?php
namespace App\Entity;

use App\Repository\RecipeProductRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RecipeProductRepository::class)]
class RecipeProduct
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: 'json')]
    private array $parts = [];

    public function __construct()
    {
        $this->parts = [];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getParts(): array
    {
        return $this->parts;
    }

    public function setParts(array $parts): self
    {
        $this->parts = $parts;

        return $this;
    }

    // Método para agregar una parte
    public function addPart(string $part): self
    {
        if (!in_array($part, $this->parts, true)) {
            $this->parts[] = $part;
        }

        return $this;
    }

    // Método para eliminar una parte
    public function removePart(string $part): self
    {
        $this->parts = array_filter($this->parts, fn($p) => $p !== $part);

        return $this;
    }
}
