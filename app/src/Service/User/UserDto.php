<?php

namespace App\Service\User;

use Symfony\Component\Validator\Constraints as Assert;

class UserDto
{
    public function __construct(
        #[Assert\NotNull(message: "ID cannot be null.")]
        #[Assert\Positive(message: "ID must be a positive number.")]
        private ?int $id,

        #[Assert\NotBlank(message: "Full name cannot be empty.")]
        #[Assert\Length(
            min: 2,
            max: 100,
            minMessage: "Full name must be at least {{ limit }} characters long.",
            maxMessage: "Full name cannot exceed {{ limit }} characters."
        )]
        private ?string $fullName,

        #[Assert\NotBlank(message: "Email cannot be empty.")]
        #[Assert\Email(message: "Please provide a valid email address.")]
        private ?string $email,

        #[Assert\NotBlank(message: "City cannot be empty.")]
        #[Assert\Length(
            min: 2,
            max: 50,
            minMessage: "City name must be at least {{ limit }} characters long.",
            maxMessage: "City name cannot exceed {{ limit }} characters."
        )]
        private ?string $city,

        private ?string $reportId = null
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function getReportId(): ?string
    {
        return $this->reportId;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function setFullName(?string $fullName): self
    {
        $this->fullName = $fullName;
        return $this;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;
        return $this;
    }

    public function setReportId(?string $reportId): self
    {
        $this->reportId = $reportId;
        return $this;
    }

    /**
     * @return array<string, int|string|null>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'fullName' => $this->getFullName(),
            'email' => $this->getEmail(),
            'city' => $this->getCity(),
            'reportId' => $this->getReportId(),
        ];
    }
}