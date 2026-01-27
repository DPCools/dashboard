<?php

declare(strict_types=1);

/**
 * External IP Widget - Displays public IP address and location information
 *
 * Uses ipify.org API for IP address and ip-api.com for location details
 */
class ExternalIpWidget extends Widget
{
    /**
     * Fetch widget data
     *
     * @return array Widget data including IP, location, and ISP
     */
    public function getData(): array
    {
        // Reason: Check cache first to avoid hitting API too frequently (5 min cache)
        $cacheKey = 'ip_data';
        $cached = $this->getCachedData($cacheKey);

        if ($cached !== null) {
            return $cached;
        }

        try {
            $data = ['ip' => null, 'location' => null];

            // Fetch IP address
            $ipResponse = $this->httpGet('https://api.ipify.org?format=json', 5);
            if ($ipResponse === null) {
                return ['error' => 'Failed to fetch IP address'];
            }

            $ipData = json_decode($ipResponse, true);
            if (!isset($ipData['ip'])) {
                return ['error' => 'Invalid IP response'];
            }

            $data['ip'] = $ipData['ip'];

            // Fetch location data if enabled
            $showLocation = (bool) $this->getSetting('show_location', false);
            $showIsp = (bool) $this->getSetting('show_isp', false);

            if ($showLocation || $showIsp) {
                // Reason: Try ipapi.co first (more accurate), fallback to ip-api.com
                $locationResponse = $this->httpGet('https://ipapi.co/' . $data['ip'] . '/json/', 5);

                if ($locationResponse !== null) {
                    $locationData = json_decode($locationResponse, true);

                    // Check if we got valid data from ipapi.co
                    if (isset($locationData['city'])) {
                        $data['location'] = [
                            'country' => $locationData['country_name'] ?? null,
                            'city' => $locationData['city'] ?? null,
                            'region' => $locationData['region'] ?? null,
                            'isp' => $locationData['org'] ?? null,
                            'postal' => $locationData['postal'] ?? null
                        ];
                    } else {
                        // Fallback to ip-api.com
                        $locationResponse = $this->httpGet('http://ip-api.com/json/' . $data['ip'], 5);

                        if ($locationResponse !== null) {
                            $locationData = json_decode($locationResponse, true);

                            if (isset($locationData['status']) && $locationData['status'] === 'success') {
                                $data['location'] = [
                                    'country' => $locationData['country'] ?? null,
                                    'city' => $locationData['city'] ?? null,
                                    'region' => $locationData['regionName'] ?? null,
                                    'isp' => $locationData['isp'] ?? null,
                                    'postal' => $locationData['zip'] ?? null
                                ];
                            }
                        }
                    }
                }
            }

            // Reason: Cache for 5 minutes (IP rarely changes)
            $this->setCachedData($cacheKey, $data, 300);

            return $data;

        } catch (Exception $e) {
            return ['error' => 'Failed to fetch IP data: ' . $e->getMessage()];
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
        // Reason: No required settings for this widget, just boolean flags
        return [
            'valid' => true,
            'errors' => []
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
            'show_location' => true,
            'show_isp' => true
        ];
    }
}
