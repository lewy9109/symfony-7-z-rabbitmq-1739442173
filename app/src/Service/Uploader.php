<?php

namespace App\Service;

use App\Message\ProcessCsvFile;
use App\Service\Raport\RaportDto;
use App\Service\RedisStorage\ReportStorage;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Uid\Uuid;

class Uploader
{
    public function __construct(
        private readonly string $uploadsPath,
        private readonly Filesystem $filesystem,
        private readonly ReportStorage $storage,
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

        $raportId = $this->createRaport($finalFile);

        $this->messageBus->dispatch(new ProcessCsvFile($raportId));

        return ["status" => "completed", "file" => $finalFile, "raportId" => $raportId];
    }

    private function createRaport(string $finalFile): string
    {
        $report = new RaportDto(
            Uuid::v4()->toBase32(),
            $finalFile,
            'Create raport',
            (new \DateTime('now'))->format('Y-m-d H:i:s'),
            microtime(true)
        );

        $this->storage->saveReport($report);

        return $report->getId();
    }
}
