<?php

declare(strict_types=1);

/**
 * Weather Widget - Displays current weather information
 *
 * Uses wttr.in API (free, no API key required)
 */
class WeatherWidget extends Widget
{
    /**
     * Fetch widget data
     *
     * @return array Widget data including temperature, conditions, humidity, wind
     */
    public function getData(): array
    {
        $location = $this->getSetting('location');

        if (empty($location)) {
            return ['error' => 'Location not configured'];
        }

        // Reason: Check cache first (30 min TTL to respect free API rate limits)
        $cacheKey = 'weather_' . md5($location);
        $cached = $this->getCachedData($cacheKey);

        if ($cached !== null) {
            return $cached;
        }

        try {
            $units = $this->getSetting('units', 'metric');
            $unitParam = $units === 'imperial' ? 'u' : 'm';

            // Reason: Use wttr.in JSON format for structured data
            $url = 'https://wttr.in/' . urlencode($location) . '?format=j1&' . $unitParam;
            $response = $this->httpGet($url, 10);

            if ($response === null) {
                return ['error' => 'Failed to fetch weather data'];
            }

            $weatherData = json_decode($response, true);

            if (!isset($weatherData['current_condition'][0])) {
                return ['error' => 'Invalid weather response'];
            }

            $current = $weatherData['current_condition'][0];
            $nearest = $weatherData['nearest_area'][0] ?? [];

            $data = [
                'temperature' => $units === 'imperial' ? $current['temp_F'] : $current['temp_C'],
                'description' => $current['weatherDesc'][0]['value'] ?? 'Unknown',
                'humidity' => $current['humidity'] ?? null,
                'wind_speed' => $units === 'imperial'
                    ? ($current['windspeedMiles'] ?? null)
                    : ($current['windspeedKmph'] ?? null),
                'wind_dir' => $current['winddir16Point'] ?? null,
                'feels_like' => $units === 'imperial'
                    ? ($current['FeelsLikeF'] ?? null)
                    : ($current['FeelsLikeC'] ?? null),
                'location' => $nearest['areaName'][0]['value'] ?? $location,
                'country' => $nearest['country'][0]['value'] ?? null,
                'units' => $units,
                'unit_temp' => $units === 'imperial' ? '°F' : '°C',
                'unit_speed' => $units === 'imperial' ? 'mph' : 'km/h'
            ];

            // Reason: Cache for 30 minutes
            $this->setCachedData($cacheKey, $data, 1800);

            return $data;

        } catch (Exception $e) {
            return ['error' => 'Failed to fetch weather: ' . $e->getMessage()];
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

        // Reason: Location is required
        if (empty($settings['location'])) {
            $errors[] = 'Location is required';
        }

        // Reason: Units must be metric or imperial
        if (isset($settings['units']) && !in_array($settings['units'], ['metric', 'imperial'], true)) {
            $errors[] = 'Units must be either "metric" or "imperial"';
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
            'location' => '',
            'units' => 'metric',
            'show_forecast' => false
        ];
    }
}
