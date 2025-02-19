<?php

declare(strict_types=1);

namespace App\Service\RedisStorage;

use App\Service\Raport\RaportDto;
use App\Service\Raport\RaportFactory;
use RedisException;

class ReportStorage extends Storage
{
    /**
     * @throws RedisException
     */
    public function save(RaportDto $report): void
    {
        $this->redis->set(sprintf("report:%s", $report->getId()), json_encode($report->toArray(), JSON_THROW_ON_ERROR));
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
}