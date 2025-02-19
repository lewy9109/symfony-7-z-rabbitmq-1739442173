<?php

namespace App\Message;

use App\Service\Raport\RaportDto;

class ProcessCsvFile
{
    public function __construct(private string $raportId)
    {}

    public function getRaportId(): string
    {
        return $this->raportId;
    }
}