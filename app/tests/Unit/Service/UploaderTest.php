<?php

namespace App\Tests\Unit\Service;

use Symfony\Component\Filesystem\Filesystem;
use App\Service\Uploader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

class UploaderTest extends TestCase
{
    private Uploader $uploaded;
    private string $uploadDir;
    private FileSystem $fileSystem;

    protected function setUp(): void
    {
        $this->uploadDir = __DIR__ . "/csv/";
        $this->fileSystem = $this->createMock(FileSystem::class);
        $this->uploaded = new Uploader($this->uploadDir, $this->fileSystem);
    }

    public function testSaveChunk(): void
    {
        $filePath = __DIR__ . "/test_chunk.txt";
        file_put_contents($filePath, "Test chunk data");

        $uploadedFile = new UploadedFile(
            $filePath,
            "test.csv",
            "text/plain",
            null,
            true
        );

        $this->fileSystem->method("exists")->willReturn(true);

        $result = $this->uploaded->saveChunk($uploadedFile, "test.csv", 0, 1);

        $this->assertEquals(
            [
                "status" => "completed",
                "file" => $this->uploadDir . "test.csv",
            ],
            $result
        );
        $this->assertFileExists($this->uploadDir . "test.csv");

        unlink($this->uploadDir . "test.csv");
    }

    public function testWillThrowException(): void
    {
        $filePath = __DIR__ . "/test_chunk.txt";

        $uploadedFile = $this->createMock(UploadedFile::class);

        $this->fileSystem->method("exists")->willReturn(true);

        $uploadedFile
            ->method("move")
            ->willThrowException(new FileNotFoundException($filePath));

        $this->expectException(FileNotFoundException::class);
        $this->expectExceptionMessage($filePath);

        $this->uploaded->saveChunk($uploadedFile, "test.csv", 0, 1);

        unlink($this->uploadDir . "test.csv");
    }
}
