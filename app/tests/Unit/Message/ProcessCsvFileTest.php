<?php

namespace App\Tests\Unit\Message;

use App\Message\ProcessCsvFile;
use PHPUnit\Framework\TestCase;

class ProcessCsvFileTest extends TestCase
{

    public function testCreatedProcessCsvFileMessage(): void
    {
        $path = "/path/to/file.csv";
        $processCsvFile = new ProcessCsvFile($path);

        $this->assertSame($path, $processCsvFile->getFilePath());
    }

}
