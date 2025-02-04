<?php

namespace App\Libraries;

use React\Promise\PromiseInterface;
use function React\Async\await;

class RedisLibrary
{
    
    public $client;

    final public function startConnection(): void
    {
        $redis = config(\Config\RedisDB::class);

        $connectionString = "redis://{$redis->redis_db['host']}:{$redis->redis_db['port']}?db={$redis->redis_db['database']}";

        $factory = new \Clue\React\Redis\Factory();
        $this->client = $factory->createLazyClient($connectionString);
    }

    final public function endConnection(): void
    {
        $this->client->end();
    }

    public function update(string $prefix, int $id, array $data): int
    {
        $jsonData = json_encode($data);

        $this->startConnection();

        $this->client->set("$prefix:$id", $jsonData);

        $this->endConnection();

        return $id;
    }

    public function get(string $prefix, int $id): array 
    {
        $this->startConnection();

        $result = await($this->client->get("$prefix:$id"));

        $this->endConnection();

        return $result 
            ? (array) json_decode( $result) 
            : [];
    }

    public function getAll(string $prefix): array
    {
        $result = [];

        $this->startConnection();

        $keys = $this->client->keys("$prefix:*");
        foreach ($keys as $key) {
            $result[$key] = $this->client->get($key);
        }

        $this->endConnection();

        return $result;
    }

    public function delete(string $prefix, string $key): bool 
    {
        $this->startConnection();

        $this->client->del("$prefix:$key");

        $this->endConnection();

        return true;
    }

    public function exists(string $prefix, string $key): bool 
    {
        $this->startConnection();

        $exists = await($this->client->exists("$prefix:$key"));
        
        $this->endConnection();

        return $exists;
    }

    private function createNewId(string $prefix): PromiseInterface
    {
        return $this->client->incr($prefix)->then(function ($nextId) {
            return (int) $nextId;
        });
    }

    public function setWithIncrement(string $prefix, array $data): int 
    {   
        $this->startConnection();

        $jsonData = json_encode($data);

        return await($this->createNewId($prefix . '_counter')->then(function($newId) use ($prefix, $jsonData) {
            $this->client->set("$prefix:$newId", $jsonData);
            $this->endConnection();

            return $newId;
        }));
    }
}