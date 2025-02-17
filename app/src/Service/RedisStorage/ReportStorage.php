<?php

declare(strict_types=1);

namespace App\Service\RedisStorage;

use App\Service\Raport\RaportDto;
use App\Service\Raport\RaportDtoFactory;
use Redis;
use RedisException;
use Symfony\Component\Cache\Adapter\RedisAdapter;

class ReportStorage
{
    private Redis $redis;

    public function __construct()
    {
        $this->redis = RedisAdapter::createConnection($_ENV['REDIS_CACHE_URL']);
    }

    /**
     * @throws RedisException
     */
    public function saveReport(RaportDto $report): void
    {
        $this->redis->set(sprintf("report:%s", $report->getId()), json_encode($report->toArray()));
    }

    /**
     * @throws RedisException
     * @throws \Exception
     */
    public function getReport(string $id): ?RaportDto
    {
        $report = $this->redis->get("report:$id");

        if(!$report){
            throw new \Exception(sprintf('Report with id %s not found', $id));
        }

        $reportDecode =  json_decode($report, true);

        return RaportDtoFactory::fromArray($reportDecode);
    }

}