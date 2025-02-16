<?php

namespace App\Service;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserDto
{
    /**
     * @var string[]
     */
    private array $warnings = [];

    #[Assert\NotNull(message: "ID cannot be null.")]
    #[Assert\Type(type: "integer", message: "ID must be an integer.")]
    #[Assert\Positive(message: "ID must be a positive integer.")]
    #[Assert\Unique(message: "User with this ID already exists.")]
    private ?int $id;

    #[Assert\NotBlank(message: "Full name cannot be blank.")]
    #[Assert\Length(
        min: 3,
        max: 100,
        minMessage: "Full name must be at least {{ limit }} characters long.",
        maxMessage: "Full name cannot be longer than {{ limit }} characters."
    )]
    private ?string $fullName;

    #[Assert\NotBlank(message: "Email cannot be blank.")]
    #[Assert\Email(
        message: "Invalid email format.",
        mode: "strict"
    )]
    private ?string $email;

    #[Assert\NotBlank(message: "City cannot be blank.")]
    #[Assert\Length(
        min: 3,
        max: 50,
        minMessage: "City name must be at least {{ limit }} characters long.",
        maxMessage: "City name cannot be longer than {{ limit }} characters."
    )]
    private ?string $city;

    public function __construct(
        ?int $id,
        ?string $fullName,
        ?string $email,
        ?string $city,
        private ?ValidatorInterface $validator = null
    ) {
        $this->id = $id;
        $this->fullName = $fullName;
        $this->email = $email;
        $this->city = $city;

        $this->warnings = $this->generateWarnings();
    }

    /**
     * @return string[]
     */
    private function generateWarnings(): array
    {
        if (!$this->validator) {
            return [];
        }

        $warnings = [];
        $errors = $this->validator->validate($this);

        foreach ($errors as $error) {
            $warnings[] = $error->getMessage();
        }

        return $warnings;
    }

    /**
     * @return string[]
     */
    public function getWarnings(): array
    {
        return $this->warnings;
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
}
