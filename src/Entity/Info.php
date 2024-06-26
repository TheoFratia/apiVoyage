<?php

namespace App\Entity;

use App\Repository\InfoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: InfoRepository::class)]
class Info
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getByCityOrCountry", "getAllInfo"])]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(["getByCityOrCountry", "getAllInfo"])]
    private ?string $description = null;

    #[Groups(["getByCityOrCountry", "getAllInfo"])]
    #[ORM\ManyToMany(targetEntity: geo::class, inversedBy: 'infos')]
    private Collection $idGeo;

    #[Groups(["getByCityOrCountry", "getAllInfo"])]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updated_at = null;

    #[Groups(["getByCityOrCountry", "getAllInfo"])]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $created_at = null;

    #[Groups(["getByCityOrCountry", "getAllInfo"])]
    #[ORM\Column(length: 24)]
    private ?string $status = null;

    #[Groups(["getByCityOrCountry", "getAllInfo"])]
    #[ORM\ManyToOne(inversedBy: 'infos')]
    private TypeInfo $idTypeInfo;

    #[Groups(["getByCityOrCountry", "getAllInfo"])]
    #[ORM\Column(length: 255)]
    private ?string $titre = null;

    #[Groups(["getByCityOrCountry", "getAllInfo"])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imageLink = null;

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

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;

        return $this;
    }

    public function getImageLink(): ?string
    {
        return $this->imageLink;
    }

    public function setImageLink(?string $imageLink): static
    {
        $this->imageLink = $imageLink;

        return $this;
    }
}
