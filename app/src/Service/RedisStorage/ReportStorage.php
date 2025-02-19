<?php

declare(strict_types=1);

namespace App\Service\RedisStorage;

use App\Service\Raport\RaportDto;
use App\Service\Raport\RaportFactory;
use RedisException;

class ReportStorage extends Storage
{
    private const LAST_REPORT_KEY = "report:last";

    /**
     * @throws RedisException
     */
    public function save(RaportDto $report): void
    {
        $reportKey = sprintf("report:%s", $report->getId());
        $this->redis->set($reportKey, json_encode($report->toArray(), JSON_THROW_ON_ERROR));

        $this->redis->set(self::LAST_REPORT_KEY, $report->getId());
    }

    /**
     * @throws RedisException
     * @throws \Exception
     */
    public function get(string $id): ?RaportDto
    {
        $report = $this->redis->get("report:$id");

        if(!$report){
            throw new \Exception(sprintf('Report with id %s not found', $id));
        }

        /** @phpstan-ignore-next-line  */
        $reportDecode =  json_decode($report, true, JSON_THROW_ON_ERROR);

        if (!is_array($reportDecode)) {
            throw new \Exception(sprintf('Invalid report data for id %s', $id));
        }

        /** @phpstan-ignore-next-line  */
        return RaportFactory::fromArray($reportDecode);
    }

    /**
     * Retrieves the last saved report.
     *
     * @throws RedisException
     * @throws \Exception
     */
    public function getLastReport(): ?RaportDto
    {
        $lastReportId = $this->redis->get(self::LAST_REPORT_KEY);

        if (!$lastReportId) {
            throw new \Exception('No reports found in Redis.');
        }

        return $this->get($lastReportId);
    }
}