<?php

namespace App\Validation;

class ValidTime
{
    /**
     * Validate if the input is a valid time in HH:mm format (24-hour format)
     */
    public function hours_format(string $str, ?string $fields = null, ?array $data = []): bool
    {
        return (bool) preg_match('/^(?:[0-9]|1\d|2[0-3]):[0-5]\d$/', $str);
    }

    /**
     * Check if hours_to is greater than hours_from
     */
    public function hours_greater(string $hours_to, ?string $fields = null, ?array $data = []): bool
    {
        if (!isset($data['hours_from'])) {
            return false;
        }

        $from_time = date('H:i', strtotime($data['hours_from']));
        $to_time = date('H:i', strtotime($hours_to));

        return $to_time > $from_time;
    }
}