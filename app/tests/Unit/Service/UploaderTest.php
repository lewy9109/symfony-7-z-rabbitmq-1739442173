<?php

namespace App\Tests\Unit\Service;

use Symfony\Component\Filesystem\Filesystem;
use App\Service\Uploader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

class UploaderTest extends TestCase
{
    private Uploader $uploader;
    private string $uploadDir;
    private FileSystem $fileSystem;

    protected function setUp(): void
    {
        $this->uploadDir = __DIR__ . "/csv/";
        $this->fileSystem = $this->createMock(FileSystem::class);
        $this->uploader = new Uploader($this->uploadDir, $this->fileSystem);
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

        $result = $this->uploader->saveChunk($uploadedFile, "test.csv", 0, 1);

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

        $this->uploader->saveChunk($uploadedFile, "test.csv", 0, 1);

        unlink($this->uploadDir . "test.csv");
    }

    public function testMergeChunksFailsWhenChunkMissing(): void
    {
        $fileName = "testfile.txt";
        $totalChunks = 3;

        for ($i = 0; $i < 2; $i++) {
            file_put_contents(
                $this->uploadDir . "{$fileName}_part_{$i}",
                "Chunk {$i}"
            );
        }

        $result = $this->uploader->saveChunk(
            $this->createMock(UploadedFile::class),
            $fileName,
            2,
            $totalChunks
        );

        $this->assertEquals(
            [
                "status" => "error",
                "message" => "Brakuje fragmentu 2",
            ],
            $result
        );
    }
}
