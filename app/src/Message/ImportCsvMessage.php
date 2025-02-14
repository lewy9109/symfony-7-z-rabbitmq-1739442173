<?php

namespace App\Message;

class ImportCsvMessage
{
    public function __construct(
        private string $importId,
        private string $filePath
    ) {}

    public function getImportId(): string
    {
        return $this->importId;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }
}