<?php

namespace App\Entity;

use App\Repository\EssentialRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: EssentialRepository::class)]
class Essential
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getByCityOrCountry", "getAllEssential"])]
    private ?int $id = null;

    #[Groups(["getByCityOrCountry", "getAllEssential"])]
    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[Groups(["getByCityOrCountry", "getAllEssential"])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[Groups(["getByCityOrCountry", "getAllEssential"])]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updated_at = null;

    #[Groups(["getByCityOrCountry", "getAllEssential"])]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $created_at = null;

    #[Groups(["getAllEssential"])]
    #[ORM\ManyToMany(targetEntity: geo::class, inversedBy: 'essentials')]
    private Collection $idGeo;

    #[Groups(["getByCityOrCountry", "getAllEssential"])]
    #[ORM\Column(length: 24)]
    private ?string $status = null;

    #[Groups(["getByCityOrCountry", "getAllEssential"])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imagesLink = null;

    public function __construct()
    {
        $this->idGeo = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getImagesLink(): ?string
    {
        return $this->imagesLink;
    }

    public function setImagesLink(?string $imagesLink): static
    {
        $this->imagesLink = $imagesLink;

        return $this;
    }
}
