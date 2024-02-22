<?php

namespace App\Entity;

use App\Repository\SaveRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SaveRepository::class)]
class Save
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;


    #[ORM\ManyToMany(targetEntity: PointOfInterest::class, inversedBy: 'saves')]
    private Collection $idPointOfInterest;

    #[ORM\ManyToOne(inversedBy: 'saves')]
    private ?geo $idGeo = null;

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

    public function getIdGeo(): ?geo
    {
        return $this->idGeo;
    }

    public function setIdGeo(?geo $idGeo): static
    {
        $this->idGeo = $idGeo;

        return $this;
    }
}
