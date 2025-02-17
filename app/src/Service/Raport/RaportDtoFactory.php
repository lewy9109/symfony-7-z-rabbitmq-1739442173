<?php

namespace App\Service\Raport;

class RaportDtoFactory
{
    public static function fromArray(array $data): RaportDto
    {
        $dto = new RaportDto(
            id: $data['id'] ?? '',
            filePath: $data['filePath'] ?? '',
            status: $data['status'] ?? '',
            created_at: $data['created_at'] ?? '',
            startTime: $data['startTime'] ?? ''
        );

        if (isset($data['endTime'])) {
            $dto->setEndTime($data['endTime']);
        }

        if (isset($data['duration'])) {
            $dto->setDuration($data['duration']);
        }

        if (isset($data['processedRows'])) {
            $dto->setProcessedRows($data['processedRows']);
        }

        if (isset($data['errors']) && is_array($data['errors'])) {
            $dto->setErrors($data['errors']);
        }

        return $dto;
    }
}