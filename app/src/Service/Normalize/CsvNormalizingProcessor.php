<?php

declare(strict_types=1);

namespace App\Service\Normalize;

use App\Message\ProcessedUserData;
use App\Service\Exception\NormalizerException;
use Exception;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;

class CsvNormalizingProcessor
{
    private const CHUNK_SIZE = 500;

    public function __construct(private readonly MessageBusInterface $messageBus)
    {
    }

    /**
     * @param string $sourceFeed
     *
     * @throws Exception
     */
    public function process(string $sourceFeed): void
    {
        try {
            if (!file_exists($sourceFeed) || filesize($sourceFeed) === 0) {
                throw new NormalizerException(sprintf('The given CSV file %s is empty or does not exist!', $sourceFeed));
            }

            $handle = fopen($sourceFeed, "r");
            if (!$handle) {
                throw new NormalizerException(sprintf('Failed to read CSV file: %s', $sourceFeed));
            }

            fgetcsv($handle);

            $batch = [];
            while (($row = fgetcsv($handle, 1000, ",")) !== false) {

                list($id, $fullName, $email, $city) = $row;
                $batch[] = new ProcessedUserData((int)$id, $fullName, $email, $city);

                if (count($batch) >= self::CHUNK_SIZE) {
                    $this->dispatchBatch($batch);
                    $batch = [];
                }
            }

            if (!empty($batch)) {
                $this->dispatchBatch($batch);
            }

            fclose($handle);
        } catch (Throwable $e) {
            throw new Exception(sprintf('Error: %s', $e->getMessage()));
        }
    }

    private function dispatchBatch(array $batch): void
    {
        foreach ($batch as $message) {
            $this->messageBus->dispatch($message);
        }

        gc_collect_cycles();
    }
}
