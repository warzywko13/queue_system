<?php

namespace App\Models;

use function React\Async\await;

class WagonModel extends BaseModel
{
    protected string $prefix = 'wagon';

    public function getByCoasterId(int $coasterId)
    {
        $this->redis->startConnection();

        $result = await($this->redis->client->keys("wagon$coasterId:*")->then(function ($wagonKeys) {
            if (empty($wagonKeys)) {
                return null;
            }

            return $this->redis->client->get($wagonKeys[0])->then(function($wagonData) {
                return json_decode($wagonData, true);
            });
        }));

        $this->redis->endConnection();
        return $result;
    }
}