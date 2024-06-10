<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $uuid = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 48)]
    private ?string $username = null;

    #[ORM\OneToMany(mappedBy: 'users', targetEntity: Personna::class)]
    private Collection $personnas;

    #[ORM\Column]
    private ?int $avatarId = null;

    public function __construct()
    {
        $this->personnas = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): static
    {
        $this->uuid = $uuid;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->uuid;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return Collection<int, Personna>
     */
    public function getPersonnas(): Collection
    {
        return $this->personnas;
    }

    public function addPersonna(Personna $personna): static
    {
        if (!$this->personnas->contains($personna)) {
            $this->personnas->add($personna);
            $personna->setUsers($this);
        }

        return $this;
    }

    public function removePersonna(Personna $personna): static
    {
        if ($this->personnas->removeElement($personna)) {
            // set the owning side to null (unless already changed)
            if ($personna->getUsers() === $this) {
                $personna->setUsers(null);
            }
        }

        return $this;
    }

    public function getAvatarId(): ?int
    {
        return $this->avatarId;
    }

    public function setAvatarId(int $avatarId): static
    {
        $this->avatarId = $avatarId;

        return $this;
    }
}
