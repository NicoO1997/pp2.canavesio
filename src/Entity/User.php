<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'Esta email ya tiene una cuenta creada')]
#[UniqueEntity(fields: ['username'], message: 'El nombre de usuario ya está en uso.')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank(message: 'El email no puede estar vacío')]
    #[Assert\Email(message: "El email '{{ value }}' no es válido.")]
    #[Assert\Length(
        min: 11,
        max: 255,
        minMessage: 'El email debe tener al menos {{ limit }} caracteres',
        maxMessage: 'El email no puede superar los {{ limit }} caracteres'
    )]
    private ?string $email = null;

    #[ORM\Column(type: 'string', length: 255, unique: true, nullable: true)]
    #[Assert\NotBlank(message: 'El nombre de usuario no puede estar vacío', groups: ['registration'])]
    #[Assert\Type(type: 'string', message: 'El nombre de usuario debe ser texto')]
    #[Assert\Regex(
        pattern: '/^[a-zA-Z0-9]+$/',
        message: 'El nombre de usuario solo puede contener letras y números'
    )]
    private ?string $username = null;

    #[ORM\Column(type: 'string', length: 15, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\NotBlank(message: 'La pregunta de seguridad es requerida', groups: ['registration'])]
    private ?string $securityQuestion = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\NotBlank(message: 'La respuesta de seguridad es requerida', groups: ['registration'])]
    private ?string $securityAnswer = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    #[Assert\Length(max: 12, maxMessage: "La contraseña no puede superar los 255 caracteres.")]
    private ?string $password = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $resetToken = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isGuest = false;

    /**
     * @var Collection<int, Cart>
     */
    #[ORM\OneToMany(targetEntity: Cart::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $carts;

    /**
     * @var Collection<int, UserFavoriteProduct>
     */
    #[ORM\OneToMany(targetEntity: UserFavoriteProduct::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $userFavoriteProducts;

    public function __construct()
    {
        $this->carts = new ArrayCollection();
        $this->userFavoriteProducts = new ArrayCollection();
        $this->createdAt = new \DateTime('now');
        $this->isGuest = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        if ($this->isGuest) {
            $roles[] = 'ROLE_INVITADO';
        } else {
            $roles[] = 'ROLE_USER';
        }
        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return Collection<int, Cart>
     */
    public function getCarts(): Collection
    {
        return $this->carts;
    }

    public function addCart(Cart $cart): static
    {
        if (!$this->carts->contains($cart)) {
            $this->carts->add($cart);
            $cart->setUser($this);
        }
        return $this;
    }

    public function removeCart(Cart $cart): static
    {
        if ($this->carts->removeElement($cart)) {
            if ($cart->getUser() === $this) {
                $cart->setUser(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, UserFavoriteProduct>
     */
    public function getUserFavoriteProducts(): Collection
    {
        return $this->userFavoriteProducts;
    }

    public function addUserFavoriteProduct(UserFavoriteProduct $userFavoriteProduct): static
    {
        if (!$this->userFavoriteProducts->contains($userFavoriteProduct)) {
            $this->userFavoriteProducts->add($userFavoriteProduct);
            $userFavoriteProduct->setUser($this);
        }
        return $this;
    }

    public function removeUserFavoriteProduct(UserFavoriteProduct $userFavoriteProduct): static
    {
        if ($this->userFavoriteProducts->removeElement($userFavoriteProduct)) {
            if ($userFavoriteProduct->getUser() === $this) {
                $userFavoriteProduct->setUser(null);
            }
        }
        return $this;
    }

    public function getResetToken(): ?string
    {
        return $this->resetToken;
    }

    public function setResetToken(?string $resetToken): self
    {
        $this->resetToken = $resetToken;
        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): self
    {
        $this->username = $username;
        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;
        return $this;
    }

    public function getSecurityQuestion(): ?string
    {
        return $this->securityQuestion;
    }

    public function setSecurityQuestion(?string $securityQuestion): self
    {
        $this->securityQuestion = $securityQuestion;
        return $this;
    }

    public function getSecurityAnswer(): ?string
    {
        return $this->securityAnswer;
    }

    public function setSecurityAnswer(?string $securityAnswer): self
    {
        $this->securityAnswer = $securityAnswer;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function isGuest(): bool
    {
        return $this->isGuest;
    }

    public function setIsGuest(bool $isGuest): self
    {
        $this->isGuest = $isGuest;
        return $this;
    }
}