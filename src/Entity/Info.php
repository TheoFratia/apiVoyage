<?php

namespace App\Entity;

use App\Repository\InfoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InfoRepository::class)]
class Info
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToMany(targetEntity: geo::class, inversedBy: 'infos')]
    private Collection $idGeo;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updated_at = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $created_at = null;

    #[ORM\Column(length: 24)]
    private ?string $status = null;

    #[ORM\ManyToOne(inversedBy: 'infos')]
    private ?TypeInfo $idTypeInfo = null;

    public function __construct()
    {
        $this->idGeo = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection<int, geo>
     */
    public function getIdGeo(): Collection
    {
        return $this->idGeo;
    }

    public function addIdGeo(geo $idGeo): static
    {
        if (!$this->idGeo->contains($idGeo)) {
            $this->idGeo->add($idGeo);
        }

        return $this;
    }

    public function removeIdGeo(geo $idGeo): static
    {
        $this->idGeo->removeElement($idGeo);

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeInterface $updated_at): static
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): static
    {
        $this->created_at = $created_at;

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

    public function getIdTypeInfo(): ?TypeInfo
    {
        return $this->idTypeInfo;
    }

    public function setIdTypeInfo(?TypeInfo $idTypeInfo): static
    {
        $this->idTypeInfo = $idTypeInfo;

        return $this;
    }
}