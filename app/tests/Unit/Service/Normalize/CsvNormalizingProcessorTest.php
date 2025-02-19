<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Normalize;

use App\Service\Exception\NormalizerException;
use App\Service\Normalize\CsvNormalizingProcessor;
use App\Service\Raport\RaportDto;
use App\Service\RedisStorage\ReportStorage;
use App\Service\RedisStorage\UserStorage;
use App\Service\User\UserDto;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

class CsvNormalizingProcessorTest extends TestCase
{
    private ReportStorage $reportStorage;
    private UserStorage $userStorage;
    private CsvNormalizingProcessor $processor;

    protected function setUp(): void
    {
        $this->reportStorage = $this->createMock(ReportStorage::class);
        $this->userStorage = $this->createMock(UserStorage::class);
        $this->processor = new CsvNormalizingProcessor($this->reportStorage, $this->userStorage);
    }

    public function testProcessValidCsv(): void
    {
        $csvContent = <<<CSV
            id,fullName,email,city
            1,John Doe,john.doe@example.com,New York
            2,Jane Smith,jane.smith@example.com,Los Angeles
            3,Bob Johnson,bob.johnson@example.com,Chicago
        CSV;

        $csvFilePath = sys_get_temp_dir() . '/test_valid.csv';
        file_put_contents($csvFilePath, $csvContent);

        $report = new RaportDto(
            id: 'test123',
            filePath: $csvFilePath,
            status: 'pending',
            created_at: '2025-02-17 12:00:00',
            startTime: '2025-02-17 12:01:00'
        );

        $this->userStorage
            ->expects($this->exactly(3))
            ->method('save')
            ->with($this->isInstanceOf(UserDto::class));

        $this->reportStorage
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(RaportDto::class));

        $this->processor->process($report);

        unlink($csvFilePath);
    }

    public function testProcessThrowsExceptionForMissingFile(): void
    {
        $report = new RaportDto(
            id: 'test123',
            filePath: '/non/existing/file.csv',
            status: 'pending',
            created_at: '2025-02-17 12:00:00',
            startTime: '2025-02-17 12:01:00'
        );

        $this->expectException(NormalizerException::class);
        $this->expectExceptionMessage('The given CSV file /non/existing/file.csv is empty or does not exist!');

        $this->processor->process($report);
    }

    public function testProcessThrowsExceptionForEmptyFile(): void
    {
        $emptyCsvFilePath = sys_get_temp_dir() . '/empty.csv';
        touch($emptyCsvFilePath);

        $report = new RaportDto(
            id: 'test123',
            filePath: $emptyCsvFilePath,
            status: 'pending',
            created_at: '2025-02-17 12:00:00',
            startTime: '2025-02-17 12:01:00'
        );

        $this->expectException(NormalizerException::class);
        $this->expectExceptionMessage(sprintf('The given CSV file %s is empty or does not exist!', $emptyCsvFilePath));

        $this->processor->process($report);

        unlink($emptyCsvFilePath);
    }

    public function testProcessHandlesBatching(): void
    {
        $csvContent = "id,fullName,email,city\n";
        for ($i = 1; $i <= 1000; $i++) {
            $csvContent .= "$i,User$i,user$i@example.com,City$i\n";
        }

        $csvFilePath = sys_get_temp_dir() . '/batch_test.csv';
        file_put_contents($csvFilePath, $csvContent);

        $report = new RaportDto(
            id: 'batchTest',
            filePath: $csvFilePath,
            status: 'pending',
            created_at: '2025-02-17 12:00:00',
            startTime: '2025-02-17 12:01:00'
        );

        $this->userStorage
            ->expects($this->exactly(1000))
            ->method('save')
            ->with($this->isInstanceOf(UserDto::class));

        $this->reportStorage
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(RaportDto::class));

        $this->processor->process($report);

        unlink($csvFilePath);
    }
}
