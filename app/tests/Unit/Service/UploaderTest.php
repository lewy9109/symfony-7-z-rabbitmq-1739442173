<?php

namespace App\Tests\Unit\Service;

use App\Service\Uploader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploaderTest extends TestCase
{
    private Uploader $uploaded;
    private string $uploadDir;

    protected function setUp(): void
    {
        $this->uploaded = new Uploader();
        $this->uploadDir = __DIR__ . '/../../var/uploads/';
    }

    public function testSaveChunk(): void
    {
        $filePath = __DIR__ . '/test_chunk.txt';
        file_put_contents($filePath, 'Test chunk data');

        $uploadedFile = new UploadedFile($filePath, 'test.csv', 'text/plain', null, true);

        $result = $this->uploaded->saveChunk($uploadedFile, 'test.csv', 0, 1);

        $this->assertEquals(['status' => 'completed', 'file' => $this->uploadDir . 'test.csv'], $result);
        $this->assertFileExists($this->uploadDir . 'test.csv');

        unlink($filePath);
        unlink($this->uploadDir . 'test.csv');
    }

}