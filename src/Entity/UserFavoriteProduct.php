<?php

namespace App\Entity;

use App\Repository\UserFavoriteProductRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserFavoriteProductRepository::class)]
class UserFavoriteProduct
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'userFavoriteProducts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'userFavoriteProduct')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Favorite $favorite = null;

    #[ORM\ManyToOne(inversedBy: 'userFavoriteProduct')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Product $product = null;

    #[ORM\ManyToOne(inversedBy: 'userFavoriteProduct')]
    private ?Machine $machine = null;

    

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getFavorite(): ?Favorite
    {
        return $this->favorite;
    }

    public function setFavorite(?Favorite $favorite): static
    {
        $this->favorite = $favorite;

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

    public function getMachine(): ?Machine
    {
        return $this->machine;
    }

    public function setMachine(?Machine $machine): static
    {
        $this->machine = $machine;

        return $this;
    }

   
}
