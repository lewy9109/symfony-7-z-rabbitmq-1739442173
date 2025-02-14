<?php

namespace App\Service;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Uploader
{

    private string $uploadDir;
    private Filesystem $filesystem;

    public function __construct()
    {
        $this->uploadDir = __DIR__ . '/../../var/uploads/';
        $this->filesystem = new Filesystem();

        if (!$this->filesystem->exists($this->uploadDir)) {
            $this->filesystem->mkdir($this->uploadDir, 777);
        }
    }

    public function saveChunk(UploadedFile $chunk, string $fileName, int $chunkIndex, int $totalChunks): array
    {
        $chunkPath = $this->uploadDir . "{$fileName}_part_{$chunkIndex}";

        $chunk->move($this->uploadDir, "{$fileName}_part_{$chunkIndex}");

        if ($chunkIndex === $totalChunks - 1) {
            return $this->mergeChunks($fileName, $totalChunks);
        }

        return ['status' => 'saved'];
    }

    private function mergeChunks(string $fileName, int $totalChunks): array
    {
        $finalFile = $this->uploadDir . $fileName;
        $output = fopen($finalFile, 'wb');

        for ($i = 0; $i < $totalChunks; $i++) {
            $chunkPath = $this->uploadDir . "{$fileName}_part_{$i}";

            if (!file_exists($chunkPath)) {
                return ['status' => 'error', 'message' => "Brakuje fragmentu {$i}"];
            }

            $input = fopen($chunkPath, 'rb');
            stream_copy_to_stream($input, $output);
            fclose($input);
            unlink($chunkPath); // Usunięcie fragmentu po scaleniu
        }

        fclose($output);

        return ['status' => 'completed', 'file' => $finalFile];
    }

}