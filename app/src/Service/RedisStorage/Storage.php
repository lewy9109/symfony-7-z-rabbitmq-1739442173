<?php

namespace App\Service\RedisStorage;

use Redis;
use Symfony\Component\Cache\Adapter\RedisAdapter;

abstract class Storage
{
    protected Redis $redis;

    public function __construct()
    {
        /**@phpstan-ignore-next-line */
        $this->redis = RedisAdapter::createConnection($_ENV['REDIS_CACHE_URL']);
    }
}