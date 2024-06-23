<?php

namespace App\Entity;

use App\Repository\SaveRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: SaveRepository::class)]
class Save
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['getAllSave'])]
    private ?int $id = null;

    #[Groups(['getAllSave'])]
    #[ORM\ManyToMany(targetEntity: PointOfInterest::class, inversedBy: 'saves')]
    private Collection $idPointOfInterest;

    #[Groups(['getAllSave'])]
    #[ORM\Column(length: 24)]
    private string $status;

    #[Groups(['getAllSave', 'excludeGeo'])]
    #[ORM\ManyToOne(inversedBy: 'saves')]
    private ?User $UserId = null;

    #[Groups(['getAllSave'])]
    #[ORM\Column(length: 255)]
    private ?string $name = null;

    public function __construct()
    {
        $this->idPointOfInterest = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, PointOfInterest>
     */
    public function getIdPointOfInterest(): Collection
    {
        return $this->idPointOfInterest;
    }

    public function addIdPointOfInterest(PointOfInterest $idPointOfInterest): static
    {
        if (!$this->idPointOfInterest->contains($idPointOfInterest)) {
            $this->idPointOfInterest->add($idPointOfInterest);
        }

        return $this;
    }

    public function removeIdPointOfInterest(PointOfInterest $idPointOfInterest): static
    {
        $this->idPointOfInterest->removeElement($idPointOfInterest);

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getUserId(): ?User
    {
        return $this->UserId;
    }

    public function setUserId(?User $UserId): static
    {
        $this->UserId = $UserId;

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
}
