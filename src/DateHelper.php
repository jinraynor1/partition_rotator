<?php


namespace Jinraynor1\PartitionRotator;


class DateHelper
{
    static function to_days($date) {
        if (is_numeric($date)) {
            $res = 719528 + (int) ($date / 86400);
        } else {
            $TZ = date_default_timezone_get();
            date_default_timezone_set('UTC');
            $res = 719528 + (int) (strtotime($date) / 86400);
            date_default_timezone_set($TZ);
        }
        return $res;
    }

    static function from_days($daystamp, $asTS = false) {
        $ts = ($daystamp - 719528) * 86400;
        return $asTS?$ts:gmdate('Y-m-d', $ts);
    }
}