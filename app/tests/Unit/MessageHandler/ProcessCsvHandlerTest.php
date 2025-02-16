<?php

namespace App\Tests\Unit\MessageHandler;

use App\Message\ProcessCsvFile;
use App\MessageHandler\ProcessCsvHandler;
use App\Service\Normalize\CsvNormalizingProcessor;
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

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->normalizing = $this->createMock(CsvNormalizingProcessor::class);

        $this->handler = new ProcessCsvHandler($this->normalizing, $this->logger,);
    }


    public function testHandlerThrowException(): void
    {
        $this->normalizing->expects($this->once())->method('process');
        $this->logger->expects($this->never())->method('error');

        $this->handler->__invoke(new ProcessCsvFile("test.csv"));

    }

}
