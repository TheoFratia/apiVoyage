<?php

namespace App\Entity;

use App\Repository\PointOfInterestRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PointOfInterestRepository::class)]
class PointOfInterest
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getByCityOrCountry", "getAllPointOfInterest", "getAllSave"])]
    private ?int $id = null;

    #[Groups(["getByCityOrCountry", "getAllPointOfInterest", "getAllSave"])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[Groups(["getByCityOrCountry", "getAllPointOfInterest", "getAllSave"])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $link = null;

    #[Groups(["getByCityOrCountry", "getAllPointOfInterest", "getAllSave"])]
    #[ORM\Column]
    private ?float $price = null;

    #[Groups(["getByCityOrCountry", "getAllPointOfInterest", "getAllSave"])]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updated_at = null;

    #[Groups(["getByCityOrCountry", "getAllPointOfInterest", "getAllSave"])]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $created_at = null;

    #[Groups(["getAllPointOfInterest", "getAllSave"])]
    #[ORM\ManyToOne(inversedBy: 'pointOfInterests')]
    private geo $idGeo;

    #[Groups(["getByCityOrCountry", "getAllPointOfInterest", "getAllSave"])]
    #[ORM\ManyToMany(targetEntity: TypePointOfInterest::class, inversedBy: 'pointOfInterests')]
    private Collection $idIType;

    #[Groups(["getByCityOrCountry", "getAllPointOfInterest", "getAllSave"])]
    #[ORM\Column(length: 24)]
    private ?string $status = null;

    #[ORM\ManyToMany(targetEntity: Save::class, mappedBy: 'idPointOfInterest')]
    private Collection $saves;

    #[Groups(["getAllPointOfInterest", "getAllSave", "getByCityOrCountry"])]
    #[ORM\OneToMany(mappedBy: 'pointOfInterest', targetEntity: DownloadedFiles::class)]
    private Collection $images;

    //"getByCityOrCountry"
    #[Groups(["getAllPointOfInterest", "getAllSave"])]
    #[ORM\OneToMany(mappedBy: 'pointOfInterest', targetEntity: Personna::class)]
    private Collection $personnas;

    #[Groups(["getByCityOrCountry", "getAllPointOfInterest", "getAllSave"])]
    #[ORM\Column(length: 255)]
    private ?string $titre = null;

    public function __construct()
    {
        $this->idIType = new ArrayCollection();
        $this->saves = new ArrayCollection();
        $this->images = new ArrayCollection();
        $this->personnas = new ArrayCollection();
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

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(?string $link): static
    {
        $this->link = $link;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

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

    public function getIdGeo(): ?geo
    {
        return $this->idGeo;
    }

    public function setIdGeo(?geo $idGeo): static
    {
        $this->idGeo = $idGeo;

        return $this;
    }

    /**
     * @return Collection<int, TypePointOfInterest>
     */
    public function getIdIType(): Collection
    {
        return $this->idIType;
    }

    public function addIdIType(TypePointOfInterest $idIType): static
    {
        if (!$this->idIType->contains($idIType)) {
            $this->idIType->add($idIType);
        }

        return $this;
    }

    public function removeIdIType(TypePointOfInterest $idIType): static
    {
        $this->idIType->removeElement($idIType);

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
     * @return Collection<int, Save>
     */
    public function getSaves(): Collection
    {
        return $this->saves;
    }

    public function addSave(Save $save): static
    {
        if (!$this->saves->contains($save)) {
            $this->saves->add($save);
            $save->addIdPointOfInterest($this);
        }

        return $this;
    }

    public function removeSave(Save $save): static
    {
        if ($this->saves->removeElement($save)) {
            $save->removeIdPointOfInterest($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, DownloadedFiles>
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(DownloadedFiles $image): static
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
            $image->setPointOfInterest($this);
        }

        return $this;
    }

    public function removeImage(DownloadedFiles $image): static
    {
        if ($this->images->removeElement($image)) {
            // set the owning side to null (unless already changed)
            if ($image->getPointOfInterest() === $this) {
                $image->setPointOfInterest(null);
            }
        }

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
            $personna->setPointOfInterest($this);
        }

        return $this;
    }

    public function removePersonna(Personna $personna): static
    {
        if ($this->personnas->removeElement($personna)) {
            // set the owning side to null (unless already changed)
            if ($personna->getPointOfInterest() === $this) {
                $personna->setPointOfInterest(null);
            }
        }

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
}
