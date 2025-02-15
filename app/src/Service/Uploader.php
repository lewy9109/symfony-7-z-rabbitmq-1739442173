<?php

namespace App\Service;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Uploader
{
    public function __construct(
        private readonly string $uploadsPath,
        private readonly Filesystem $filesystem
    ) {
        if (!$this->filesystem->exists($this->uploadsPath)) {
            $this->filesystem->mkdir($this->uploadsPath, 777);
        }
    }

    public function saveChunk(
        UploadedFile $chunk,
        string $fileName,
        int $chunkIndex,
        int $totalChunks
    ): array {
        $chunk->move($this->uploadsPath, "{$fileName}_part_{$chunkIndex}");

        if ($chunkIndex === $totalChunks - 1) {
            return $this->mergeChunks($fileName, $totalChunks);
        }

        return ["status" => "saved"];
    }

    private function mergeChunks(string $fileName, int $totalChunks): array
    {
        $finalFile = $this->uploadsPath . $fileName;
        $output = fopen($finalFile, "wb");

        for ($i = 0; $i < $totalChunks; $i++) {
            $chunkPath = $this->uploadsPath . "{$fileName}_part_{$i}";

            if (!file_exists($chunkPath)) {
                return [
                    "status" => "error",
                    "message" => "Brakuje fragmentu {$i}",
                ];
            }

            $input = fopen($chunkPath, "rb");
            stream_copy_to_stream($input, $output);
            fclose($input);
            unlink($chunkPath);
        }

        fclose($output);

        return ["status" => "completed", "file" => $finalFile];
    }
}
