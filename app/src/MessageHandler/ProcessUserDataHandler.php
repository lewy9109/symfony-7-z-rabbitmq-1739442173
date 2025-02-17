<?php

namespace App\MessageHandler;

use App\Message\ProcessedUserData;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ProcessUserDataHandler
{
    public function __construct(
        private readonly ReportStorage $reportStorage,
        private readonly LoggerInterface $logger
    )
    {
    }

    public function __invoke(ProcessedUserData $userProcessData): void
    {
        try{

            //TODO

        }catch (\Exception $exception){
            $this->logger->error($exception->getMessage());
        }
    }

}