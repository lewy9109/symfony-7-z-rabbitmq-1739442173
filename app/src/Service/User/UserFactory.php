<?php

namespace App\Service\User;

class UserFactory
{
    public static function fromArray(array $data): UserDto
    {
        $dto = new UserDto(
            id: (int)$data['id'] ?? '',
            fullName: $data['fullName'] ?? '',
            email: $data['email'] ?? '',
            city: $data['city'] ?? '',
            reportId: $data['reportId'] ?? '',
        );

        if (isset($data['fullName'])) {
            $dto->setFullName($data['fullName']);
        }

        if (isset($data['email'])) {
            $dto->setEmail($data['email']);
        }

        if (isset($data['city'])) {
            $dto->setCity($data['city']);
        }

        if (isset($data['reportId'])) {
            $dto->setReportId($data['reportId']);
        }

        return $dto;
    }
}