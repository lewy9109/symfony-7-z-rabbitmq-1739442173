<?php

namespace App\Service;

use App\Message\ProcessCsvFile;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Messenger\MessageBusInterface;

class Uploader
{
    public function __construct(
        private readonly string $uploadsPath,
        private readonly Filesystem $filesystem,
        private readonly MessageBusInterface $messageBus
    ) {
        if (!$this->filesystem->exists($this->uploadsPath)) {
            $this->filesystem->mkdir($this->uploadsPath, 777);
        }
    }

    /**
     * @param UploadedFile $chunk
     * @param string       $fileName
     * @param int          $chunkIndex
     * @param int          $totalChunks
     *
     * @return array<string>
     */
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

    /**
     * @param string $fileName
     * @param int    $totalChunks
     *
     * @return string[]
     */
    private function mergeChunks(string $fileName, int $totalChunks): array
    {
        $finalFile = $this->uploadsPath . $fileName;
        $output = fopen($finalFile, "wb");

        if ($output === false) {
            return [
                "status" => "error",
                "message" => "Cannot open file",
            ];
        }

        for ($i = 0; $i < $totalChunks; $i++) {
            $chunkPath = $this->uploadsPath . "{$fileName}_part_{$i}";

            if (!file_exists($chunkPath)) {
                return [
                    "status" => "error",
                    "message" => "Missing chunk {$i}",
                ];
            }

            $input = fopen($chunkPath, "rb");
            if ($input === false) {
                fclose($output);
                return [
                    "status" => "error",
                    "message" => "Cannot open the chunk {$i}",
                ];
            }

            stream_copy_to_stream($input, $output);
            fclose($input);
            unlink($chunkPath);
        }

        fclose($output);

        $this->messageBus->dispatch(new ProcessCsvFile($finalFile));

        return ["status" => "completed", "file" => $finalFile];
    }
}
