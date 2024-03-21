<?php

namespace App\Entity;

use App\Repository\GeoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: GeoRepository::class)]
class Geo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getAllCountryAndCity", "getByCityOrCountry", "getAllInfo", "getAllEssential", "getAllPointOfInterest", "getAllSave"])]
    private ?int $id = null;

    #[ORM\Column(length: 124)]
    #[Groups(["getAllCountryAndCity", "getByCityOrCountry", "getAllInfo", "getAllEssential", "getAllPointOfInterest", "getAllSave"])]
    #[Assert\Length(
        min: 2,
        max: 50,
        minMessage: 'le nom de la ville doit faire au moins {{ limit }} caractères de long',
        maxMessage: 'le nom de la ville doit faire au maximum {{ limit }} caractères de long',
    )]
    private ?string $city = null;

    #[ORM\Column(length: 200)]
    #[Groups(["getAllCountryAndCity", "getByCityOrCountry", "getAllInfo", "getAllEssential", "getAllPointOfInterest", "getAllSave"])]
    private ?string $country = null;

    #[Groups(["getByCityOrCountry", "getAllInfo", "getAllEssential", "getAllPointOfInterest", "getAllSave"])]
    #[ORM\Column(type: Types::TEXT)]
    private ?string $address = null;

    #[Groups(["getByCityOrCountry", "getAllInfo", "getAllEssential", "getAllPointOfInterest", "getAllSave"])]
    #[ORM\Column(type: Types::TEXT)]
    private ?string $longitude = null;

    #[Groups(["getByCityOrCountry", "getAllInfo", "getAllEssential", "getAllPointOfInterest", "getAllSave"])]
    #[ORM\Column(type: Types::TEXT)]
    private ?string $latitude = null;

    #[Groups(["getByCityOrCountry", "getAllInfo", "getAllEssential", "getAllPointOfInterest", "getAllSave"])]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updated_at = null;

    #[Groups(["getByCityOrCountry", "getAllInfo", "getAllEssential", "getAllPointOfInterest", "getAllSave"])]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $created_at = null;

    #[Groups(["getByCityOrCountry", "getAllInfo", "getAllEssential", "getAllPointOfInterest", "getAllSave"])]
    #[ORM\Column(length: 24)]
    private ?string $status = null;

    //#[Groups(["getByCityOrCountry"])]
    #[ORM\ManyToMany(targetEntity: Essential::class, mappedBy: 'idGeo')]
    private Collection $essentials;

    //#[Groups(["getByCityOrCountry"])]
    #[ORM\ManyToMany(targetEntity: Info::class, mappedBy: 'idGeo')]
    private Collection $infos;

    #[Groups(["getByCityOrCountry"])]
    #[ORM\OneToMany(mappedBy: 'idGeo', targetEntity: PointOfInterest::class)]
    private Collection $pointOfInterests;

    #[ORM\OneToMany(mappedBy: 'idGeo', targetEntity: Save::class)]
    private Collection $saves;

    #[Groups(["getByCityOrCountry", "getAllInfo", "getAllEssential", "getAllPointOfInterest", "getAllSave"])]
    #[ORM\Column(length: 24)]
    private ?string $zipCode = null;

    public function __construct()
    {
        $this->essentials = new ArrayCollection();
        $this->infos = new ArrayCollection();
        $this->pointOfInterests = new ArrayCollection();
        $this->saves = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): static
    {
        $this->country = $country;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    public function setLongitude(string $longitude): static
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    public function setLatitude(string $latitude): static
    {
        $this->latitude = $latitude;

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

    /**
     * @return Collection<int, Essential>
     */
    public function getEssentials(): Collection
    {
        return $this->essentials;
    }

    public function addEssential(Essential $essential): static
    {
        if (!$this->essentials->contains($essential)) {
            $this->essentials->add($essential);
            $essential->addIdGeo($this);
        }

        return $this;
    }

    public function removeEssential(Essential $essential): static
    {
        if ($this->essentials->removeElement($essential)) {
            $essential->removeIdGeo($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Info>
     */
    public function getInfos(): Collection
    {
        return $this->infos;
    }

    public function addInfo(Info $info): static
    {
        if (!$this->infos->contains($info)) {
            $this->infos->add($info);
            $info->addIdGeo($this);
        }

        return $this;
    }

    public function removeInfo(Info $info): static
    {
        if ($this->infos->removeElement($info)) {
            $info->removeIdGeo($this);
        }

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
            $pointOfInterest->setIdGeo($this);
        }

        return $this;
    }

    public function removePointOfInterest(PointOfInterest $pointOfInterest): static
    {
        if ($this->pointOfInterests->removeElement($pointOfInterest)) {
            // set the owning side to null (unless already changed)
            if ($pointOfInterest->getIdGeo() === $this) {
                $pointOfInterest->setIdGeo(null);
            }
        }

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
            $save->setIdGeo($this);
        }

        return $this;
    }

    public function removeSave(Save $save): static
    {
        if ($this->saves->removeElement($save)) {
            // set the owning side to null (unless already changed)
            if ($save->getIdGeo() === $this) {
                $save->setIdGeo(null);
            }
        }

        return $this;
    }

    public function getZipCode(): ?string
    {
        return $this->zipCode;
    }

    public function setZipCode(string $zipCode): static
    {
        $this->zipCode = $zipCode;

        return $this;
    }
}
