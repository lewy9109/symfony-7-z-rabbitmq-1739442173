<?php

namespace App\Message;

class ProcessCsvFile
{
    public function __construct(private string $filePath)
    {}

    public function getFilePath(): string
    {
        return $this->filePath;
    }
}