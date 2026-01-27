<?php

declare(strict_types=1);

/**
 * System Stats Widget - Displays system resource usage
 *
 * Shows CPU load, memory usage, and disk space (Linux/Unix only)
 */
class SystemStatsWidget extends Widget
{
    /**
     * Fetch widget data
     *
     * @return array Widget data including CPU, memory, and disk stats
     */
    public function getData(): array
    {
        try {
            $data = [];

            $showCpu = (bool) $this->getSetting('show_cpu', true);
            $showMemory = (bool) $this->getSetting('show_memory', true);
            $showDisk = (bool) $this->getSetting('show_disk', true);

            // CPU Load (Linux/Unix only)
            if ($showCpu && function_exists('sys_getloadavg')) {
                $load = sys_getloadavg();
                if ($load !== false) {
                    $data['cpu'] = [
                        'load_1min' => round($load[0], 2),
                        'load_5min' => round($load[1], 2),
                        'load_15min' => round($load[2], 2)
                    ];
                }
            }

            // Memory Usage (Linux only)
            if ($showMemory && file_exists('/proc/meminfo')) {
                $memInfo = $this->parseMemInfo();
                if ($memInfo !== null) {
                    $data['memory'] = $memInfo;
                }
            }

            // Disk Usage
            if ($showDisk) {
                $diskPath = $this->getSetting('disk_path', '/');
                $diskInfo = $this->getDiskInfo($diskPath);
                if ($diskInfo !== null) {
                    $data['disk'] = $diskInfo;
                }
            }

            // Reason: Return error if no stats could be retrieved
            if (empty($data)) {
                return ['error' => 'System stats not available on this platform'];
            }

            return $data;

        } catch (Exception $e) {
            return ['error' => 'Failed to fetch system stats: ' . $e->getMessage()];
        }
    }

    /**
     * Parse /proc/meminfo for memory statistics
     *
     * @return array|null Memory info or null on failure
     */
    private function parseMemInfo(): ?array
    {
        $meminfo = @file_get_contents('/proc/meminfo');
        if ($meminfo === false) {
            return null;
        }

        $lines = explode("\n", $meminfo);
        $memData = [];

        foreach ($lines as $line) {
            if (preg_match('/^(\w+):\s+(\d+)/', $line, $matches)) {
                $memData[$matches[1]] = (int) $matches[2];
            }
        }

        if (!isset($memData['MemTotal']) || !isset($memData['MemAvailable'])) {
            return null;
        }

        $totalMb = round($memData['MemTotal'] / 1024, 0);
        $availableMb = round($memData['MemAvailable'] / 1024, 0);
        $usedMb = $totalMb - $availableMb;
        $usagePercent = $totalMb > 0 ? round(($usedMb / $totalMb) * 100, 1) : 0;

        return [
            'total_mb' => $totalMb,
            'used_mb' => $usedMb,
            'available_mb' => $availableMb,
            'usage_percent' => $usagePercent
        ];
    }

    /**
     * Get disk usage information
     *
     * @param string $path Disk path to check
     * @return array|null Disk info or null on failure
     */
    private function getDiskInfo(string $path): ?array
    {
        if (!file_exists($path)) {
            return null;
        }

        $totalBytes = @disk_total_space($path);
        $freeBytes = @disk_free_space($path);

        if ($totalBytes === false || $freeBytes === false) {
            return null;
        }

        $usedBytes = $totalBytes - $freeBytes;
        $totalGb = round($totalBytes / 1024 / 1024 / 1024, 1);
        $usedGb = round($usedBytes / 1024 / 1024 / 1024, 1);
        $freeGb = round($freeBytes / 1024 / 1024 / 1024, 1);
        $usagePercent = $totalBytes > 0 ? round(($usedBytes / $totalBytes) * 100, 1) : 0;

        return [
            'path' => $path,
            'total_gb' => $totalGb,
            'used_gb' => $usedGb,
            'free_gb' => $freeGb,
            'usage_percent' => $usagePercent
        ];
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

        // Reason: Validate disk path if provided
        if (isset($settings['disk_path']) && !empty($settings['disk_path'])) {
            if (!file_exists($settings['disk_path'])) {
                $errors[] = 'Disk path does not exist';
            }
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
            'show_cpu' => true,
            'show_memory' => true,
            'show_disk' => true,
            'disk_path' => '/'
        ];
    }
}
