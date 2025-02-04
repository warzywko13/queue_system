<?php

namespace App\Models;

use App\Libraries\RedisLibrary;

class BaseModel
{
    protected readonly RedisLibrary $redis;
    protected string $prefix;

    public function __construct()
    {
        $this->redis = new RedisLibrary();
    }

    public function getPrefix(?int $parentId): string
    {
        if(empty($parentId)) {
            return $this->prefix;
        }

        return $this->prefix . $parentId;
    }

    public function add(array $data, int $parentId = null): int
    {
        return $this->redis->setWithIncrement(
            $this->getPrefix($parentId), 
            $data
        );
    }

    public function get(int $id, int $parentId = null): ?array
    {
        return $this->redis->get(
        $this->getPrefix($parentId), 
            $id
        );
    }

    public function update(int $id, array $data, int $parentId = null): int
    {
        return $this->redis->update(
            $this->getPrefix($parentId), 
            $id, 
            $data
        );
    }

    public function exists(int $id, int $parentId = null): bool
    {
        return $this->redis->exists(
            $this->getPrefix($parentId), 
            $id
        );
    }

    public function delete(int $id, int $parentId = null): bool
    {
        return $this->redis->delete(
            $this->getPrefix($parentId),
            $id
        );
    }
}