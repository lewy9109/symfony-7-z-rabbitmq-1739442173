<?php

namespace App\Service\RedisStorage;

use App\Service\Raport\RaportDto;
use Redis;
use Symfony\Component\Cache\Adapter\RedisAdapter;

abstract class Storage
{
    protected Redis $redis;

    public function __construct()
    {
        $this->redis = RedisAdapter::createConnection($_ENV['REDIS_CACHE_URL']);
    }
}