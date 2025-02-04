<?php
namespace App\Service;

use App\Libraries\RedisLibrary;
use CodeIgniter\Config\BaseService;
use CodeIgniter\CLI\CLI;
use React\Promise\PromiseInterface;
use App\DTO\CoasterDTO;

class MonitorCoastersService extends BaseService
{
    private RedisLibrary $redis;
    private $logger;

    public function __construct()
    {
        $this->redis = new RedisLibrary;
        $this->logger = service('logger');
    }

    public function monitor(): void
    {
        $this->redis->startConnection();
            
        $this->redis->client->keys('coaster:*')->then(function ($keys) {
            CLI::write('');
            CLI::write(lang('MonitorCoasters.time', ['date' =>  date('H:i')]));

            if (empty($keys)) {
                $this->logNoCoaster();
                $this->redis->endConnection();
                return;
            }

            $promises = array_map(fn($key) => $this->processCoaster($key), $keys);
            
            \React\Promise\all($promises)->then(fn() => $this->redis->endConnection());
        });
    }

    private function processCoaster(string $key): PromiseInterface
    {
        return $this->redis->client->get($key)->then(function ($coasterData) use ($key) {
            if(empty($coasterData)) {
                return false;
            }

            $coasterId = str_replace('coaster:', '', $key);
            $coaster = new CoasterDTO(array_merge(['id' => (int) $coasterId], json_decode($coasterData, true)));
            
            return $this->processWagons($coaster);
        });
    }

    private function processWagons(CoasterDTO $coaster): PromiseInterface
    {
        return $this->redis->client->keys("wagon{$coaster->id}:*")->then(function ($wagonKeys) use ($coaster) {
            if(empty($wagonKeys)) {
                return $this->LogNoWagons($coaster);
            }
            
            $wagonPromises = array_map(fn($wagonKey) => $this->redis->client->get($wagonKey), $wagonKeys);

            return \React\Promise\all($wagonPromises)->then(function ($wagonsData) use ($coaster) {
                foreach ($wagonsData as $wagonData) {
                    $wagon = json_decode($wagonData, true);
                    $coaster->wagon++;
                    $coaster->wagonSettings->seat_count = max($coaster->wagonSettings->seat_count, $wagon['seat_count']);
                    $coaster->wagonSettings->speed = max($coaster->wagonSettings->speed, $wagon['speed']);
                }

                return $this->analyzeCoaster($coaster);
            });
        });
    }

    private function analyzeCoaster(CoasterDTO $coaster): void
    {
        $this->calculateMetrics($coaster);
        $this->displayStatus($coaster);
    }

    public static function calculateMetrics(CoasterDTO $coaster): object
    {
        $ride_time = (($coaster->trackLength / $coaster->wagonSettings->speed) / 60) + 5;
        $rides_per_day = $coaster->dayTime / $ride_time;
        $max_customers_per_day = $rides_per_day * $coaster->wagonSettings->seat_count;
        $coaster->wagonNeed = ceil($coaster->customerCount / max(1, $max_customers_per_day));
        $coaster->staffNeed = 1 + ($coaster->wagonNeed * 2);

        return $coaster;
    }

    private function displayStatus(CoasterDTO $coaster): void
    {
        CLI::write('');
        CLI::write(lang('MonitorCoasters.coaster', ['id' => $coaster->id]));
        CLI::write(lang('MonitorCoasters.opening_hours', ['hours_from' => $coaster->hoursFrom, 'hours_to' => $coaster->hoursTo]));
        CLI::write(lang('MonitorCoasters.wagons_number', ['wagon' => $coaster->wagon, 'wagonNeed' =>  $coaster->wagonNeed]));
        CLI::write(lang('MonitorCoasters.available_staff', ['staff' => $coaster->staff, 'staffNeed' => $coaster->staffNeed]));
        CLI::write(lang('MonitorCoasters.daily_customers', ['customerCount' => $coaster->customerCount]));

        $errors = [];

        if ($coaster->staff > 2 * $coaster->staffNeed && $coaster->wagon > 2 * $coaster->wagonNeed) {
            $errors[] = lang('MonitorCoasters.double_excess');
        }

        if ($coaster->staff < $coaster->staffNeed) {
            $errors[] = lang('MonitorCoasters.missing_staff', ['staff' => $coaster->staffNeed - $coaster->staff]);
        } elseif ($coaster->staff > $coaster->staffNeed) {
            $errors[] = lang('MonitorCoasters.excess_staff', ['staff' => $coaster->staff - $coaster->staffNeed]);
        }

        if ($coaster->wagon < $coaster->wagonNeed) {
            $errors[] = lang('MonitorCoasters.missing_wagons', ['wagons' => $coaster->wagonNeed - $coaster->wagon]);
        } elseif ($coaster->wagon > $coaster->wagonNeed) {
            $errors[] = lang('MonitorCoasters.excess_wagons', ['wagons' => $coaster->wagon - $coaster->wagonNeed]);
        }

        if(empty($errors)) {
            CLI::write(lang('MonitorCoasters.status', ['status' => 'OK']));
        } else {
            $message = implode(", ", $errors) . '.';
            $this->logError($coaster->id, $message);
        }
    }

    private function LogNoWagons(CoasterDTO $coaster): void
    {
        CLI::write('');
        CLI::write(lang('MonitorCoasters.coaster', ['id' => $coaster->id]));

        $this->logError($coaster->id, lang('MonitorCoasters.no_wagons'));
    }

    private function logNoCoaster(): void
    {
        $error_msg = lang('MonitorCoasters.no_coaster');
        $this->logger->warning($error_msg);
        CLI::write('');
        CLI::write($error_msg);
    }

    private function logError(int $coaster_id, string $message): void
    {
        $message = lang('MonitorCoasters.issue', ['message' => $message]);
        CLI::write($message);

        $coaster = lang('MonitorCoasters.coaster_id', ['id' => $coaster_id]) . ' - ';
        $this->logger->warning(
            $coaster . $message
        );
    }
}