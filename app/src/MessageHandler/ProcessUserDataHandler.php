<?php

namespace App\MessageHandler;

use App\Message\ProcessedUserData;
use App\Service\UserDto;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ProcessUserDataHandler
{
    public function __invoke(ProcessedUserData $userData)
    {
        $userData = new UserDto(
            $userData->getId(),
            $userData->getFullName(),
            $userData->getEmail(),
            $userData->getCity()
        );

    }

}