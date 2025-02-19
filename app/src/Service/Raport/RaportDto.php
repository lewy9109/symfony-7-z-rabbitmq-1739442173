<?php

namespace App\Service\Raport;

class RaportDto
{
    private ?string $endTime = null;

    private ?string $duration = null;

    private ?int $processedRows = 0;

    /**
     * @var array<string, mixed>
     */
    private array $errors = [];

    public function __construct(
        private string $id,
        private string $filePath,
        private string $status,
        private string $created_at,
        private string $startTime,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getCreatedAt(): string
    {
        return $this->created_at;
    }

    public function setCreatedAt(string $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getStartTime(): string
    {
        return $this->startTime;
    }

    public function setStartTime(string $startTime): self
    {
        $this->startTime = $startTime;

        return $this;
    }

    public function getEndTime(): ?string
    {
        return $this->endTime;
    }

    public function setEndTime(?string $endTime): self
    {
        $this->endTime = $endTime;

        return $this;
    }

    public function getDuration(): ?string
    {
        return $this->duration;
    }

    public function setDuration(?string $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    public function getProcessedRows(): ?int
    {
        return $this->processedRows;
    }

    public function setProcessedRows(?int $processedRows): self
    {
        $this->processedRows = $processedRows;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @param array<string, mixed> $errors
     *
     * @return $this
     */
    public function setErrors(array $errors): self
    {
        $this->errors = $errors;

        return $this;
    }

    /**
     * @param array<string, mixed> $errors
     *
     * @return $this
     */
    public function addErrors(array $errors): self
    {
        $this->errors[] = $errors;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'filePath' => $this->filePath,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'startTime' => $this->startTime,
            'endTime' => $this->endTime,
            'duration' => $this->duration,
            'processedRows' => $this->processedRows,
            'errors' => $this->errors,
        ];
    }
}