<?php

declare(strict_types=1);

/**
 * Health Controller - Fast system health checks
 */
class HealthController
{
    /**
     * Quick health check endpoint (< 50ms response time)
     */
    public function check(): void
    {
        $startTime = microtime(true);
        $checks = [];
        $allHealthy = true;

        // Check 1: Database accessible and migrations table exists
        try {
            $result = Database::fetchOne('SELECT COUNT(*) as count FROM migrations');
            $checks['database'] = [
                'status' => 'healthy',
                'message' => 'Database accessible',
                'details' => ($result['count'] ?? 0) . ' migrations applied'
            ];
        } catch (Exception $e) {
            $checks['database'] = [
                'status' => 'error',
                'message' => 'Database error',
                'details' => 'Cannot access database'
            ];
            $allHealthy = false;
        }

        // Check 2: Icons directory writable
        $iconsDir = BASE_PATH . '/public/icons/';
        if (is_dir($iconsDir) && is_writable($iconsDir)) {
            $iconCount = count(glob($iconsDir . '*.svg') ?: []);
            $checks['icons'] = [
                'status' => 'healthy',
                'message' => 'Icons directory OK',
                'details' => $iconCount . ' custom icons'
            ];
        } else {
            $checks['icons'] = [
                'status' => 'warning',
                'message' => 'Icons directory not writable',
                'details' => 'Cannot upload icons'
            ];
            // Not critical, don't mark as unhealthy
        }

        // Check 3: Data directory writable
        $dataDir = BASE_PATH . '/data/';
        if (is_dir($dataDir) && is_writable($dataDir)) {
            $dbSize = file_exists(DATABASE_PATH) ? filesize(DATABASE_PATH) : 0;
            $checks['data'] = [
                'status' => 'healthy',
                'message' => 'Data directory OK',
                'details' => 'DB size: ' . $this->formatBytes($dbSize)
            ];
        } else {
            $checks['data'] = [
                'status' => 'error',
                'message' => 'Data directory not writable',
                'details' => 'Database changes may fail'
            ];
            $allHealthy = false;
        }

        // Check 4: Session working
        if (session_status() === PHP_SESSION_ACTIVE) {
            $checks['session'] = [
                'status' => 'healthy',
                'message' => 'Sessions working',
                'details' => 'Authentication enabled'
            ];
        } else {
            $checks['session'] = [
                'status' => 'error',
                'message' => 'Session error',
                'details' => 'Login may not work'
            ];
            $allHealthy = false;
        }

        $executionTime = round((microtime(true) - $startTime) * 1000, 2);

        header('Content-Type: application/json');
        echo json_encode([
            'healthy' => $allHealthy,
            'status' => $allHealthy ? 'healthy' : 'degraded',
            'checks' => $checks,
            'response_time_ms' => $executionTime,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Format bytes to human readable
     */
    private function formatBytes(int $bytes): string
    {
        if ($bytes === 0) return '0 B';

        $units = ['B', 'KB', 'MB', 'GB'];
        $i = floor(log($bytes) / log(1024));

        return round($bytes / pow(1024, $i), 2) . ' ' . $units[$i];
    }
}
