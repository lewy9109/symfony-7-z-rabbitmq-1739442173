<?php

namespace App\Message;

use Symfony\Component\Validator\Constraints as Assert;

class ProcessedUserData
{
    public function __construct(
        #[Assert\NotNull(message: "ID nie może być puste")]
        #[Assert\Positive(message: "ID musi być liczbą dodatnią")]
        private readonly ?int $id,

        #[Assert\NotBlank(message: "Imię i nazwisko nie może być puste")]
        #[Assert\Length(
            min: 2,
            max: 100,
            minMessage: "Imię i nazwisko musi mieć co najmniej {{ limit }} znaki",
            maxMessage: "Imię i nazwisko nie może przekraczać {{ limit }} znaków"
        )]
        private readonly ?string $fullName,

        #[Assert\NotBlank(message: "E-mail nie może być pusty")]
        #[Assert\Email(message: "Podaj poprawny adres e-mail")]
        private readonly ?string $email,

        #[Assert\NotBlank(message: "Miasto nie może być puste")]
        #[Assert\Length(
            min: 2,
            max: 50,
            minMessage: "Nazwa miasta musi mieć co najmniej {{ limit }} znaki",
            maxMessage: "Nazwa miasta nie może przekraczać {{ limit }} znaków"
        )]
        private readonly ?string $city,
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
}