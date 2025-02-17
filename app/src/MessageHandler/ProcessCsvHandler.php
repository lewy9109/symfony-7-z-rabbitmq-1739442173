<?php

namespace App\MessageHandler;

use App\Message\ProcessCsvFile;
use App\Service\Normalize\CsvNormalizingProcessor;
use App\Service\RedisStorage\ReportStorage;
use Exception;
use Psr\Log\LoggerInterface;
use RedisException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ProcessCsvHandler
{
    public function __construct(
        private readonly CsvNormalizingProcessor $normalizing,
        private readonly ReportStorage $reportStorage,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @throws Exception
     */
    public function __invoke(ProcessCsvFile $message): void
    {
        try{
            $report = $this->reportStorage->getReport($message->getRaportId());
            $this->normalizing->process($report);
        }catch (Exception|RedisException $exception){
            $this->logger->error('Error while processing csv file',[
                'message' => $exception->getMessage(),
                'reportId' => $message->getRaportId()
            ]);
            throw $exception;
        }
    }
}