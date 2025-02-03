<?php

namespace App\Entity;

use App\Repository\CartRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CartRepository::class)]
class Cart
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'carts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    /**
     * @var Collection<int, CartProductOrder>
     */
    #[ORM\OneToMany(targetEntity: CartProductOrder::class, mappedBy: 'cart', orphanRemoval: true)]
    private Collection $cartProductOrders;

    public function __construct()
    {
        $this->cartProductOrders = new ArrayCollection();
    }

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

    /**
     * @return Collection<int, CartProductOrder>
     */
    public function getCartProductOrders(): Collection
    {
        return $this->cartProductOrders;
    }

    public function addCartProductOrder(CartProductOrder $cartProductOrder): static
    {
        if (!$this->cartProductOrders->contains($cartProductOrder)) {
            $this->cartProductOrders->add($cartProductOrder);
            $cartProductOrder->setCart($this);
        }

        return $this;
    }

    public function removeCartProductOrder(CartProductOrder $cartProductOrder): static
    {
        if ($this->cartProductOrders->removeElement($cartProductOrder)) {
            // set the owning side to null (unless already changed)
            if ($cartProductOrder->getCart() === $this) {
                $cartProductOrder->setCart(null);
            }
        }

        return $this;
    }
}
