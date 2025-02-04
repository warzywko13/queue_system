<?php

namespace App\Commands;

use App\Service\MonitorCoastersService;
use CodeIgniter\CLI\BaseCommand;
use React\EventLoop\Loop;

class MonitorCoasters extends BaseCommand
{
    /**
     * Command Group
     */
    protected $group = 'App';

    /**
     * Command Name
     */
    protected $name = 'app:monitor_coasters';

    /**
     * Command Description
     */
    protected $description = 'Coasters statistics and monitoring';

    public function run(array $params)
    {
        (new MonitorCoastersService)->monitor();

        $loop = Loop::get();

        $loop->addPeriodicTimer( 5, function () {
            (new MonitorCoastersService)->monitor();
        });
        
        $loop->run();
    }
}
