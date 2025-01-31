<?php

/**
 * TimeUtility class for handling time-related operations
 */
class TimeUtility {
    /**
     * Formats time in seconds to a human-readable string
     * @param int $seconds Time in seconds
     * @return string Formatted time string (e.g., "2 hr 30 min" or "45 min")
     */
    public static function format_time($seconds) {
        if ($seconds < 60) {
            return "1 min";
        }
        
        $time = self::seconds_to_time($seconds);
        $hours = $time['hours'];
        $minutes = $time['minutes'];
        
        if ($hours > 0) {
            return $hours . " hr " . ($minutes > 0 ? $minutes . " min" : "");
        }
        return $minutes . " min";
    }

    /**
     * Converts hours and minutes to seconds
     * @param int $hours Number of hours
     * @param int $minutes Number of minutes
     * @return int Total seconds
     */
    public static function time_to_seconds($hours, $minutes) {
        return ($hours * 3600) + ($minutes * 60);
    }

    /**
     * Converts seconds to hours and minutes
     * @param int $seconds Number of seconds
     * @return array Associative array with 'hours' and 'minutes' keys
     */
    public static function seconds_to_time($seconds) {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        return ['hours' => $hours, 'minutes' => $minutes];
    }
}

?>
