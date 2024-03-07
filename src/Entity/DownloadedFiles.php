<?php

namespace App\Entity;

use App\Repository\DownloadedFilesRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: DownloadedFilesRepository::class)]
#[Vich\Uploadable]
class DownloadedFiles
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getByCityOrCountry", "getAllInfo", "getAllEssential", "getAllPointOfInterest"])]
    private ?int $id = null;

    #[Groups(["getByCityOrCountry", "getAllInfo", "getAllEssential", "getAllPointOfInterest"])]
    #[ORM\Column(length: 255)]
    private ?string $realName = null;

    #[Groups(["getByCityOrCountry", "getAllInfo", "getAllEssential", "getAllPointOfInterest"])]
    #[ORM\Column(length: 255)]
    private ?string $realPath = null;

    #[Groups(["getByCityOrCountry", "getAllInfo", "getAllEssential", "getAllPointOfInterest"])]
    #[ORM\Column(length: 255)]
    private ?string $publicPath = null;

    #[Groups(["getByCityOrCountry", "getAllInfo", "getAllEssential", "getAllPointOfInterest"])]
    #[ORM\Column(length: 255)]
    private ?string $mimeType = null;

    #[Groups(["getByCityOrCountry", "getAllInfo", "getAllEssential", "getAllPointOfInterest"])]
    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[Groups(["getByCityOrCountry", "getAllInfo", "getAllEssential", "getAllPointOfInterest"])]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updatedAt = null;

    #[Groups(["getByCityOrCountry", "getAllInfo", "getAllEssential", "getAllPointOfInterest"])]
    #[ORM\Column(length: 24)]
    private ?string $status = null;

    #[Groups(["getByCityOrCountry", "getAllInfo", "getAllEssential", "getAllPointOfInterest"])]
    #[Vich\UploadableField(mapping: 'pictures', fileNameProperty: 'realPath')]
    private $file;

    #[Groups(["getByCityOrCountry", "getAllInfo", "getAllEssential", "getAllPointOfInterest"])]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'images')]
    private ?Essential $essential = null;

    #[ORM\ManyToOne(inversedBy: 'images')]
    private ?Info $info = null;

    #[ORM\ManyToOne(inversedBy: 'images')]
    private ?PointOfInterest $pointOfInterest = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRealName(): ?string
    {
        return $this->realName;
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile( ?File $file): DownloadedFiles
    {
        $this->file = $file;

        if ($file) {
            $this->updatedAt = new \DateTime('now');
        }

        return $this;
    }


    public function setRealName(string $realName): static
    {
        $this->realName = $realName;

        return $this;
    }

    public function getRealPath(): ?string
    {
        return $this->realPath;
    }

    public function setRealPath(string $realPath): static
    {
        $this->realPath = $realPath;

        return $this;
    }

    public function getPublicPath(): ?string
    {
        return $this->publicPath;
    }

    public function setPublicPath(string $publicPath): static
    {
        $this->publicPath = $publicPath;

        return $this;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(string $mimeType): static
    {
        $this->mimeType = $mimeType;

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

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

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

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getEssential(): ?Essential
    {
        return $this->essential;
    }

    public function setEssential(?Essential $essential): static
    {
        $this->essential = $essential;

        return $this;
    }

    public function getInfo(): ?Info
    {
        return $this->info;
    }

    public function setInfo(?Info $info): static
    {
        $this->info = $info;

        return $this;
    }

    public function getPointOfInterest(): ?PointOfInterest
    {
        return $this->pointOfInterest;
    }

    public function setPointOfInterest(?PointOfInterest $pointOfInterest): static
    {
        $this->pointOfInterest = $pointOfInterest;

        return $this;
    }
}
