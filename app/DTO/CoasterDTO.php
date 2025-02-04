<?php

namespace App\DTO;

class CoasterDTO
{
    public int $id;
    public int $staffCount;
    public int $customerCount;
    public int $trackLength;
    public string $hoursFrom;
    public string $hoursTo;
    public int $dayTime;
    public int $wagon = 0;
    public int $wagonNeed = 0;
    public int $staff;
    public int $staffNeed = 1;
    public object $wagonSettings;

    public function __construct(array $data)
    {
        $this->id = $data['id'] ?? 0;
        $this->staffCount = $data['staff_count'] ?? 0;
        $this->customerCount = $data['customer_count'] ?? 0;
        $this->trackLength = $data['track_length'] ?? 0;
        $this->hoursFrom = $data['hours_from'] ?? '00:00';
        $this->hoursTo = $data['hours_to'] ?? '00:00';
        $this->dayTime = self::calculateMinutesDiff($this->hoursFrom, $this->hoursTo);
        $this->staff = $data['staff_count'] ?? 0;
        $this->wagonSettings = (object) [
            'seat_count' => 0,
            'speed' => 1
        ];
    }

    private static function calculateMinutesDiff(string $hoursFrom, string $hoursTo): int
    {
        $timeFrom = \DateTime::createFromFormat('H:i', $hoursFrom);
        $timeTo = \DateTime::createFromFormat('H:i', $hoursTo);
        return ($timeFrom->diff($timeTo)->h * 60) + $timeFrom->diff($timeTo)->i;
    }
}