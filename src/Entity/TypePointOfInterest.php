<?php

namespace App\Entity;

use App\Repository\TypePointOfInterestRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: TypePointOfInterestRepository::class)]
class TypePointOfInterest
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getAllTypePointOfInterest", "getAllPointOfInterest", "getAllSave"])]
    private ?int $id = null;

    #[Groups(["getByCityOrCountry", "getAllTypePointOfInterest", "getAllPointOfInterest", "getAllSave"])]
    #[ORM\Column(type: Types::TEXT)]
    private ?string $type = null;

    #[Groups(["getAllTypePointOfInterest", "getAllPointOfInterest", "getAllSave"])]
    #[ORM\Column(length: 24)]
    private ?string $status = null;

    #[ORM\ManyToMany(targetEntity: PointOfInterest::class, mappedBy: 'idIType')]
    private Collection $pointOfInterests;

    public function __construct()
    {
        $this->pointOfInterests = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

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

    /**
     * @return Collection<int, PointOfInterest>
     */
    public function getPointOfInterests(): Collection
    {
        return $this->pointOfInterests;
    }

    public function addPointOfInterest(PointOfInterest $pointOfInterest): static
    {
        if (!$this->pointOfInterests->contains($pointOfInterest)) {
            $this->pointOfInterests->add($pointOfInterest);
            $pointOfInterest->addIdIType($this);
        }

        return $this;
    }

    public function removePointOfInterest(PointOfInterest $pointOfInterest): static
    {
        if ($this->pointOfInterests->removeElement($pointOfInterest)) {
            $pointOfInterest->removeIdIType($this);
        }

        return $this;
    }
}
