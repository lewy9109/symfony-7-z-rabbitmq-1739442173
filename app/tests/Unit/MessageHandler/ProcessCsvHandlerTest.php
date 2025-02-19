<?php

namespace App\Tests\Unit\MessageHandler;

use App\Message\ProcessCsvFile;
use App\MessageHandler\ProcessCsvHandler;
use App\Service\Normalize\CsvNormalizingProcessor;
use App\Service\Raport\RaportDto;
use App\Service\RedisStorage\ReportStorage;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use PHPUnit\Framework\MockObject\MockObject;

class ProcessCsvHandlerTest extends TestCase
{
    /**
     * @var ProcessCsvHandler
     */
    private ProcessCsvHandler $handler;

    /**
     * @var MockObject&ProcessCsvHandler
     */
    private LoggerInterface $logger;

    /**
     * @var MockObject&CsvNormalizingProcessor
     */
    private CsvNormalizingProcessor $normalizing;

    private ReportStorage $reportStorage;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->normalizing = $this->createMock(CsvNormalizingProcessor::class);
        $this->reportStorage = $this->createMock(ReportStorage::class);

        $this->handler = new ProcessCsvHandler($this->normalizing, $this->reportStorage, $this->logger);
    }


    public function testHandlerThrowException(): void
    {
        $this->normalizing->expects($this->never())->method('process');
        $this->reportStorage
            ->expects($this->once())
            ->method('get')
            ->with('idstorage')
            ->willThrowException(new \RedisException('test'));

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with(
                'Error while processing csv file',
                [
                    'message' => 'test',
                    'reportId' => 'idstorage'
                ]
            );

        $this->expectException(\RedisException::class);
        $this->expectExceptionMessage('test');

        $this->handler->__invoke(new ProcessCsvFile("idstorage"));
    }

}
