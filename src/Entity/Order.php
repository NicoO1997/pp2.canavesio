<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var Collection<int, CartProductOrder>
     */
    #[ORM\OneToMany(targetEntity: CartProductOrder::class, mappedBy: 'orders', orphanRemoval: true)]
    private Collection $cartProductOrder;

    public function __construct()
    {
        $this->cartProductOrder = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, CartProductOrder>
     */
    public function getCartProductOrder(): Collection
    {
        return $this->cartProductOrder;
    }

    public function addCartProductOrder(CartProductOrder $cartProductOrder): static
    {
        if (!$this->cartProductOrder->contains($cartProductOrder)) {
            $this->cartProductOrder->add($cartProductOrder);
            $cartProductOrder->setOrders($this);
        }

        return $this;
    }

    public function removeCartProductOrder(CartProductOrder $cartProductOrder): static
    {
        if ($this->cartProductOrder->removeElement($cartProductOrder)) {
            // set the owning side to null (unless already changed)
            if ($cartProductOrder->getOrders() === $this) {
                $cartProductOrder->setOrders(null);
            }
        }

        return $this;
    }
}
