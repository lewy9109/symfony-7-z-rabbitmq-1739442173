<?php

declare(strict_types=1);

namespace App\Service\Normalize;

use App\Message\ProcessedUserData;
use App\Service\Exception\NormalizerException;
use App\Service\Raport\RaportDto;
use App\Service\RedisStorage\ReportStorage;
use Exception;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Throwable;

class CsvNormalizingProcessor
{
    private const CHUNK_SIZE = 500;

    private ValidatorInterface $validator;

    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly ReportStorage $reportStorage
    )
    {
        $this->validator = Validation::createValidator();
    }

    /**
     * @param RaportDto $report
     *
     * @throws Exception
     */
    public function process(RaportDto $report): void
    {
        try {
            $sourceFeed = $report->getFilePath();
            if (!file_exists($sourceFeed) || filesize($sourceFeed) === 0) {
                throw new NormalizerException(sprintf('The given CSV file %s is empty or does not exist!', $sourceFeed));
            }

            $handle = fopen($sourceFeed, "r");
            if (!$handle) {
                throw new NormalizerException(sprintf('Failed to read CSV file: %s', $sourceFeed));
            }

            fgetcsv($handle);

            $batch = [];
            $processedRows = 0;
            while (($row = fgetcsv($handle, 1000, ",")) !== false) {

                list($id, $fullName, $email, $city) = $row;
                $processData = new ProcessedUserData((int)$id, $fullName, $email, $city);

                $violations = $this->validator->validate($processData);

                if (count($violations) > 0) {
                    $err = [];
                    foreach ($violations as $violation) {
                        $err[$id] = [
                            $violation->getMessage()
                        ];
                    }

                    $report->addErrors($err);
                } else {
                    $batch[] = new ProcessedUserData((int)$id, $fullName, $email, $city);
                }


                if (count($batch) >= self::CHUNK_SIZE) {
                    $this->dispatchBatch($batch);
                    $batch = [];
                }

                $processedRows ++;
            }

            $report->setProcessedRows($processedRows);
            $report->setEndTime(microtime(true));

            $this->reportStorage->saveReport($report);

            if (!empty($batch)) {
                $this->dispatchBatch($batch);
            }

            fclose($handle);
        } catch (Throwable $e) {
            throw new NormalizerException(sprintf('Error: %s', $e->getMessage()));
        }
    }

    /**
     * @param ProcessedUserData[] $batch
     *
     * @return void
     */
    private function dispatchBatch(array $batch): void
    {
        foreach ($batch as $message) {
            $this->messageBus->dispatch($message);
        }

        gc_collect_cycles();
    }
}
