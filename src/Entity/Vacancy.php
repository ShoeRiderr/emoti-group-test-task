<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\VacancyRepository;
use Doctrine\DBAL\Types\DecimalType;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VacancyRepository::class)]
#[ApiResource]
class Vacancy
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, unique: true)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column]
    private ?int $free = null;

    #[ORM\Column]
    private ?int $price = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function getFormatedDate(): ?string
    {
        return $this->date->format('Y-m-d');
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getFree(): ?int
    {
        return $this->free;
    }

    public function setFree(int $free): static
    {
        $this->free = $free;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function getFloatPrice(): ?float
    {
        return $this->price / 100;
    }

    public function setPrice(int $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getFormatedCreatedAt(): ?string
    {
        return $this->createdAt->format('Y-m-d');
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getFormatedUpdatedAt(): ?string
    {
        return $this->updatedAt->format('Y-m-d');
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'date' => $this->getFormatedDate(),
            'free' => $this->getFree(),
            'price' => $this->getPrice(),
            'floatPrice' => $this->getFloatPrice(),
            'createdAt' => $this->getFormatedCreatedAt(),
            'updatedAt' => $this->getFormatedUpdatedAt(),
        ];
    }
}
