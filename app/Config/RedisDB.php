<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class RedisDB extends BaseConfig
{
    public array $redis_db;

    public function __construct()
    {
        parent::__construct();

        $this->redis_db = [
            'host'     => getenv('DOCKER_ENV') ? env('REDIS_HOST', 'codeigniter_redis') : '127.0.0.1',
            'port'     => env('REDIS_PORT', 6379),
            'database' => env('CI_ENVIRONMENT', 'development') === 'production' ? 0 : 1,
        ];
    }
}