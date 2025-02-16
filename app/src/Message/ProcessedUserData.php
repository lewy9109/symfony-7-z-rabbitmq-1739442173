<?php

namespace App\Message;


class ProcessedUserData
{
    public function __construct(
        private readonly ?int $id,
        private readonly ?string $fullName,
        private readonly ?string $email,
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