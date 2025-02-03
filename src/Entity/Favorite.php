<?php

namespace App\Entity;

use App\Repository\FavoriteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FavoriteRepository::class)]
class Favorite
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?bool $flag = null;

    /**
     * @var Collection<int, UserFavoriteProduct>
     */
    #[ORM\OneToMany(targetEntity: UserFavoriteProduct::class, mappedBy: 'favorite', orphanRemoval: true)]
    private Collection $userFavoriteProduct;

    public function __construct()
    {
        $this->userFavoriteProduct = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isFlag(): ?bool
    {
        return $this->flag;
    }

    public function setFlag(bool $flag): static
    {
        $this->flag = $flag;

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
            $userFavoriteProduct->setFavorite($this);
        }

        return $this;
    }

    public function removeUserFavoriteProduct(UserFavoriteProduct $userFavoriteProduct): static
    {
        if ($this->userFavoriteProduct->removeElement($userFavoriteProduct)) {
            // set the owning side to null (unless already changed)
            if ($userFavoriteProduct->getFavorite() === $this) {
                $userFavoriteProduct->setFavorite(null);
            }
        }

        return $this;
    }
}
