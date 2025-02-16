<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    /** @phpstan-ignore-next-line  */
    private ?int $id = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "ID cannot be null.")]
    #[Assert\Type(type: "integer", message: "ID must be an integer.")]
    #[Assert\Positive(message: "ID must be a positive integer.")]
    #[Assert\Unique(message: "User with this ID already exists.")]
    private ?int $businessId = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(
        min: 3,
        max: 100,
        minMessage: "Full name must be at least {{ limit }} characters long.",
        maxMessage: "Full name cannot be longer than {{ limit }} characters."
    )]
    private ?string $fullName = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\NotBlank(message: "Email cannot be blank.")]
    #[Assert\Email(
        message: "Invalid email format.",
        mode: "strict"
    )]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\NotBlank(message: "City cannot be blank.")]
    #[Assert\Length(
        min: 3,
        max: 50,
        minMessage: "City name must be at least {{ limit }} characters long.",
        maxMessage: "City name cannot be longer than {{ limit }} characters."
    )]
    private ?string $city = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBusinessId(): ?int
    {
        return $this->businessId;
    }

    public function setBusinessId(?int $businessId): static
    {
        $this->businessId = $businessId;

        return $this;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(?string $fullName): static
    {
        $this->fullName = $fullName;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): static
    {
        $this->city = $city;

        return $this;
    }
}
