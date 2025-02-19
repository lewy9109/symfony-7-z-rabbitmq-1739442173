<?php

declare(strict_types=1);

namespace App\Service\Normalize;

use App\Service\Exception\NormalizerException;
use App\Service\Raport\RaportDto;
use App\Service\RedisStorage\ReportStorage;
use App\Service\RedisStorage\UserStorage;
use App\Service\User\UserDto;
use Exception;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Throwable;

class CsvNormalizingProcessor
{
    private const CHUNK_SIZE = 500;

    public function __construct(
        private readonly ReportStorage $reportStorage,
        private readonly UserStorage $userStorage,
        private readonly ValidatorInterface $validator
    )
    {
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

            $batch = 0;
            $processedRows = 0;
            while (($row = fgetcsv($handle, 1000, ",")) !== false) {
                list($id, $fullName, $email, $city) = array_map('trim', $row);

                $processData = new UserDto((int)$id, $fullName, $email, $city, $report->getId());

                $violations = $this->validator->validate($processData);

                if (count($violations) > 0) {
                    $err = [];
                    foreach ($violations as $violation) {
                        $err[] = [
                            'id' => $processData->getId(),
                            'message' => $violation->getMessage()
                        ];
                        $report->addErrors($err);
                    }
                    $this->reportStorage->save($report);

                } else {
                    $this->userStorage->save($processData);
                }


                if ($batch >= self::CHUNK_SIZE) {
                    gc_collect_cycles();
                    $batch = 0;
                }

                $batch ++;
                $processedRows ++;
            }

            $report->setProcessedRows($processedRows);
            $report->setEndTime(microtime());
            $report->setStatus("SUCCESS");
            $this->reportStorage->save($report);
            fclose($handle);
        } catch (Throwable $e) {
            $report->setStatus("ERROR");
            $report->setEndTime(microtime());
            $this->reportStorage->save($report);
            throw new NormalizerException(sprintf('Error: %s', $e->getMessage()));
        }
    }
}
