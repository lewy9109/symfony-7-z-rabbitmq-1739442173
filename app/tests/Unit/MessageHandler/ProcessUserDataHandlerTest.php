<?php

namespace App\Tests\Unit\MessageHandler;

use App\Entity\User;
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

    public function testHandleProcessedUserData(): void
    {
        $userData = new ProcessedUserData(1, "John Doe", "john.doe@example.com", "New York");

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(User::class));

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->logger
            ->expects($this->never())
            ->method('error');

        ($this->handler)($userData);
    }

    public function testHandleExceptionLogging(): void
    {
        $userData = new ProcessedUserData(1, "John Doe", "john.doe@example.com", "New York");

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->willThrowException(new \Exception("Database error"));

        $this->entityManager
            ->expects($this->never())
            ->method('flush');

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with($this->stringContains("Database error"));

        ($this->handler)($userData);
    }

}
