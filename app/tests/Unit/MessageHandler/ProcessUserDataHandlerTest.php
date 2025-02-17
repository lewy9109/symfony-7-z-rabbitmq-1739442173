<?php

namespace App\Tests\Unit\MessageHandler;

use App\Message\ProcessedUserData;
use App\MessageHandler\ProcessUserDataHandler;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ProcessUserDataHandlerTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private LoggerInterface $logger;
    private ProcessUserDataHandler $handler;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->handler = new ProcessUserDataHandler($this->entityManager, $this->logger);
    }

}
