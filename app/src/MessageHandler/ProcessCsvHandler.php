<?php

namespace App\MessageHandler;

use App\Message\ProcessCsvFile;
use App\Service\Normalize\CsvNormalizingProcessor;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ProcessCsvHandler
{
    public function __construct(
        private readonly CsvNormalizingProcessor $normalizing,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @throws Exception
     */
    public function __invoke(ProcessCsvFile $message): void
    {
        try{
             $this->normalizing->process($message->getFilePath());
        }catch (Exception $exception){
            $this->logger->error('Error while processing csv file',[
                'message' => $exception->getMessage(),
                'file' => $message->getFilePath()
            ]);
            throw $exception;
        }
    }
}