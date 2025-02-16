<?php

namespace App\MessageHandler;

use App\Entity\User;
use App\Message\ProcessedUserData;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ProcessUserDataHandler
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger
    )
    {
    }

    public function __invoke(ProcessedUserData $userProcessData): void
    {
        try{
            $userData = new User();
            $userData
                ->setFullName($userProcessData->getFullName())
                ->setEmail($userProcessData->getEmail())
                ->setBusinessId($userProcessData->getId())
                ->setCity($userProcessData->getCity());

            $this->entityManager->persist($userData);
            $this->entityManager->flush();

        }catch (\Exception $exception){
            $this->logger->error($exception->getMessage());
        }
    }

}