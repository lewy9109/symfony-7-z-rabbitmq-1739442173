<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Normalize;

use App\Service\Exception\NormalizerException;
use App\Service\Normalize\CsvNormalizingProcessor;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class CsvNormalizingProcessorTest extends TestCase
{
    private MessageBusInterface $messageBus;
    private CsvNormalizingProcessor $processor;

    protected function setUp(): void
    {
        $this->messageBus = $this->createMock(MessageBusInterface::class);
        $this->processor = new CsvNormalizingProcessor($this->messageBus);
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

        $this->messageBus
            ->expects($this->exactly(3))
            ->method('dispatch')
            ->willReturnCallback(fn($message) => new Envelope($message));

        $this->processor->process($csvFilePath);

        unlink($csvFilePath);
    }

    public function testProcessThrowsExceptionForMissingFile(): void
    {
        $this->expectException(NormalizerException::class);
        $this->expectExceptionMessage('The given CSV file /non/existing/file.csv is empty or does not exist!');

        $this->processor->process('/non/existing/file.csv');
    }

    public function testProcessThrowsExceptionForEmptyFile(): void
    {
        $emptyCsvFilePath = sys_get_temp_dir() . '/empty.csv';
        touch($emptyCsvFilePath);

        $this->expectException(NormalizerException::class);
        $this->expectExceptionMessage(sprintf('The given CSV file %s is empty or does not exist!', $emptyCsvFilePath));

        $this->processor->process($emptyCsvFilePath);

        unlink($emptyCsvFilePath);
    }

    public function testProcessSkipsHeaderAndHandlesBatching(): void
    {
        $csvContent = "id,fullName,email,city\n";
        for ($i = 1; $i <= 1000; $i++) {
            $csvContent .= "$i,User$i,user$i@example.com,City$i\n";
        }

        $csvFilePath = sys_get_temp_dir() . '/batch_test.csv';
        file_put_contents($csvFilePath, $csvContent);

        $this->messageBus
            ->expects($this->exactly(1000))
            ->method('dispatch')
            ->willReturnCallback(fn($message) => new Envelope($message));

        $this->processor->process($csvFilePath);

        unlink($csvFilePath);
    }
}
