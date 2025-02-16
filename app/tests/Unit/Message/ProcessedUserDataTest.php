<?php

namespace App\Tests\Unit\Message;

use App\Message\ProcessedUserData;
use PHPUnit\Framework\TestCase;

class ProcessedUserDataTest extends TestCase
{
    public function testProcessedUserDataCanBeCreatedWithValidData(): void
    {
        $userData = new ProcessedUserData(1, "Test test", "test@example.com", "Kato");

        $this->assertSame(1, $userData->getId());
        $this->assertSame("Test test", $userData->getFullName());
        $this->assertSame("test@example.com", $userData->getEmail());
        $this->assertSame("Kato", $userData->getCity());
    }

    public function testProcessedUserDataAllowsNullValues(): void
    {
        $userData = new ProcessedUserData(null, null, null, null);

        $this->assertNull($userData->getId());
        $this->assertNull($userData->getFullName());
        $this->assertNull($userData->getEmail());
        $this->assertNull($userData->getCity());
    }

    public function testProcessedUserDataWithEmptyStrings(): void
    {
        $userData = new ProcessedUserData(2, "", "", "");

        $this->assertSame(2, $userData->getId());
        $this->assertSame("", $userData->getFullName());
        $this->assertSame("", $userData->getEmail());
        $this->assertSame("", $userData->getCity());
    }

}
