<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Controller\Reservation\CreateController;
use App\Controller\Reservation\DeleteController;
use App\Repository\ReservationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(
            paginationItemsPerPage: 10,
            stateless: false,
        ),
        new Get(stateless: false),
        new Post(
            name: 'reservations_create',
            uriTemplate: '/reservations',
            controller: CreateController::class,
            stateless: false
        ),
        new Delete(
            name: 'reservations_delete',
            uriTemplate: '/reservations/{id}',
            controller: DeleteController::class,
            stateless: false,
            security: 'is_granted("ROLE_ADMIN") or (object.user == user and previous_object.user == user)',
        ),
    ]
)]
class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank]
    #[Assert\Type('date')]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank]
    #[Assert\Type('date')]
    private ?\DateTimeInterface $endDate = null;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(
        options: [
            "default" => "CURRENT_TIMESTAMP"
        ]
    )]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(
        options: [
            "default" => "CURRENT_TIMESTAMP"
        ]
    )]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(nullable: true)]
    private ?int $price = null;

    #[ORM\Column(options: [
        "default" => 1
    ])]
    #[Assert\NotBlank]
    #[Assert\Type('int')]
    private ?int $bookedPlaces = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function getFormatedStartDate(): ?string
    {
        return $this->startDate->format('Y-m-d');
    }

    public function setStartDate(\DateTimeInterface $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function getFormatedEndDate(): ?string
    {
        return $this->endDate->format('Y-m-d');
    }

    public function setEndDate(\DateTimeInterface $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

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
            'user' => $this->getUser(),
            'startDate' => $this->getFormatedStartDate(),
            'endDate' => $this->getFormatedEndDate(),
            'price' => $this->getPrice(),
            'floatPrice' => $this->getFloatPrice(),
            'bookedPlaces' => $this->getBookedPlaces(),
            'createdAt' => $this->getFormatedCreatedAt(),
            'updatedAt' => $this->getFormatedUpdatedAt(),
        ];
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function getFloatPrice(): ?float
    {
        $price = $this->price;

        if (!$price) {
            return 0;
        }

        return $price  / 100;
    }

    public function setPrice(int $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getBookedPlaces(): ?int
    {
        return $this->bookedPlaces;
    }

    public function setBookedPlaces(int $bookedPlaces): static
    {
        $this->bookedPlaces = $bookedPlaces;

        return $this;
    }
}
