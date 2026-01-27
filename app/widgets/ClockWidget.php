<?php

declare(strict_types=1);

/**
 * Clock Widget - Displays current time and date
 *
 * Supports timezone configuration and 12h/24h formats
 */
class ClockWidget extends Widget
{
    /**
     * Fetch widget data
     *
     * @return array Widget data including time, date, timezone
     */
    public function getData(): array
    {
        try {
            $timezone = $this->getSetting('timezone', 'UTC');
            $format = $this->getSetting('format', '24h');
            $showSeconds = (bool) $this->getSetting('show_seconds', true);

            // Reason: Validate timezone
            try {
                $tz = new DateTimeZone($timezone);
            } catch (Exception $e) {
                return ['error' => 'Invalid timezone: ' . $timezone];
            }

            $now = new DateTime('now', $tz);

            // Format time based on user preference
            if ($format === '12h') {
                $timeFormat = $showSeconds ? 'g:i:s A' : 'g:i A';
            } else {
                $timeFormat = $showSeconds ? 'H:i:s' : 'H:i';
            }

            return [
                'time' => $now->format($timeFormat),
                'date' => $now->format('l, F j, Y'),
                'date_short' => $now->format('Y-m-d'),
                'timezone' => $timezone,
                'timezone_abbr' => $now->format('T'),
                'timestamp' => $now->getTimestamp(),
                'format' => $format,
                'show_seconds' => $showSeconds
            ];

        } catch (Exception $e) {
            return ['error' => 'Failed to get time: ' . $e->getMessage()];
        }
    }

    /**
     * Validate widget settings
     *
     * @param array $settings Settings to validate
     * @return array Validation result
     */
    public function validateSettings(array $settings): array
    {
        $errors = [];

        // Reason: Validate timezone if provided
        if (isset($settings['timezone']) && !empty($settings['timezone'])) {
            try {
                new DateTimeZone($settings['timezone']);
            } catch (Exception $e) {
                $errors[] = 'Invalid timezone: ' . $settings['timezone'];
            }
        }

        // Reason: Validate format
        if (isset($settings['format']) && !in_array($settings['format'], ['12h', '24h'], true)) {
            $errors[] = 'Format must be either "12h" or "24h"';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Get default settings
     *
     * @return array Default settings
     */
    public function getDefaultSettings(): array
    {
        return [
            'timezone' => date_default_timezone_get(),
            'format' => '24h',
            'show_seconds' => true
        ];
    }
}
