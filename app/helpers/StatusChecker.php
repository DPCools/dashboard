<?php

declare(strict_types=1);

class StatusChecker
{
    private const TIMEOUT = 5; // Reason: HTTPS handshakes with self-signed certs need more time
    private const CACHE_TTL = 30;

    // Reason: Block internal/private IP addresses to prevent SSRF attacks
    private const BLOCKED_IPS = [
        '127.0.0.1',
        'localhost',
        '::1',
        '0.0.0.0'
    ];

    /**
     * Check multiple URLs in parallel using curl_multi
     *
     * @param array<int, array{id: int, title: string, url: string}> $items
     * @return array<int, array{status: string, response_time: int, http_code?: int, error?: string}>
     */
    public static function checkBatch(array $items): array
    {
        $results = [];

        // Filter items with valid URLs
        $validItems = [];
        foreach ($items as $item) {
            $itemId = (int) $item['id'];

            // Check cache first
            $cached = self::getCached($itemId);
            if ($cached !== null) {
                $results[$itemId] = $cached;
                continue;
            }

            // Validate URL and check for SSRF
            if (self::isValidUrl((string) $item['url'])) {
                $validItems[$itemId] = $item;
            } else {
                $results[$itemId] = [
                    'status' => 'down',
                    'response_time' => 0,
                    'error' => 'Invalid or blocked URL'
                ];
                self::setCached($itemId, $results[$itemId]);
            }
        }

        // If no items to check, return early
        if (empty($validItems)) {
            return $results;
        }

        // Reason: Use curl_multi for parallel requests to improve performance
        $multiHandle = curl_multi_init();
        $curlHandles = [];

        foreach ($validItems as $itemId => $item) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, (string) $item['url']);
            curl_setopt($ch, CURLOPT_NOBODY, true); // HTTP HEAD request
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
            curl_setopt($ch, CURLOPT_TIMEOUT, self::TIMEOUT);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::TIMEOUT);
            // Reason: Use per-item SSL verification setting (default to verify if not specified)
            $sslVerify = (int)($item['ssl_verify'] ?? 1) === 1;
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $sslVerify);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $sslVerify ? 2 : 0); // Reason: 2=check CN/SAN, 0=no check

            curl_multi_add_handle($multiHandle, $ch);
            $curlHandles[$itemId] = ['handle' => $ch, 'start_time' => microtime(true)];
        }

        // Execute all queries simultaneously
        do {
            $status = curl_multi_exec($multiHandle, $active);
            if ($active) {
                curl_multi_select($multiHandle);
            }
        } while ($active && $status === CURLM_OK);

        // Get results
        foreach ($curlHandles as $itemId => $data) {
            $ch = $data['handle'];
            $startTime = $data['start_time'];
            $responseTime = (int) ((microtime(true) - $startTime) * 1000);

            $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            $curlErrno = curl_errno($ch);

            // Reason: Treat 501/405 as "up" - server responded but doesn't support HEAD method
            if (($httpCode >= 200 && $httpCode < 400) || $httpCode === 501 || $httpCode === 405) {
                $result = [
                    'status' => 'up',
                    'response_time' => $responseTime,
                    'http_code' => $httpCode
                ];
            } else {
                // Reason: Provide specific error messages for common SSL/DNS issues
                $errorMessage = $curlError;
                if ($curlErrno === 60 || $curlErrno === 51) {
                    $errorMessage = 'SSL certificate verification failed';
                } elseif ($curlErrno === 6) {
                    $errorMessage = 'DNS resolution failed';
                } elseif ($curlErrno === 28) {
                    $errorMessage = 'Connection timeout';
                } elseif ($curlError === '') {
                    $errorMessage = "HTTP $httpCode";
                }

                $result = [
                    'status' => 'down',
                    'response_time' => $responseTime,
                    'http_code' => $httpCode,
                    'error' => $errorMessage,
                    'curl_errno' => $curlErrno
                ];
            }

            $results[$itemId] = $result;
            self::setCached($itemId, $result);

            curl_multi_remove_handle($multiHandle, $ch);
            curl_close($ch);
        }

        curl_multi_close($multiHandle);

        return $results;
    }

    /**
     * Check single URL
     *
     * @param string $url
     * @return array{status: string, response_time: int, http_code?: int, error?: string}
     */
    public static function checkUrl(string $url): array
    {
        // Validate URL
        if (!self::isValidUrl($url)) {
            return [
                'status' => 'down',
                'response_time' => 0,
                'error' => 'Invalid or blocked URL'
            ];
        }

        $startTime = microtime(true);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
        curl_setopt($ch, CURLOPT_TIMEOUT, self::TIMEOUT);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::TIMEOUT);
        // Reason: Verify SSL by default for single URL checks (secure by default)
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        curl_exec($ch);

        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        $responseTime = (int) ((microtime(true) - $startTime) * 1000);

        curl_close($ch);

        // Reason: Treat 501/405 as "up" - server responded but doesn't support HEAD method
        if (($httpCode >= 200 && $httpCode < 400) || $httpCode === 501 || $httpCode === 405) {
            return [
                'status' => 'up',
                'response_time' => $responseTime,
                'http_code' => $httpCode
            ];
        }

        return [
            'status' => 'down',
            'response_time' => $responseTime,
            'http_code' => $httpCode,
            'error' => $curlError !== '' ? $curlError : "HTTP $httpCode"
        ];
    }

    /**
     * Validate URL and check for SSRF vulnerabilities
     *
     * @param string $url
     * @return bool
     */
    private static function isValidUrl(string $url): bool
    {
        // Basic URL validation
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        // Parse URL
        $parsed = parse_url($url);
        if ($parsed === false || !isset($parsed['host'])) {
            return false;
        }

        $host = $parsed['host'];

        // Reason: Block localhost and private IP addresses to prevent SSRF
        if (in_array($host, self::BLOCKED_IPS, true)) {
            return false;
        }

        // Check for private IP ranges
        if (preg_match('/^(10\.|172\.(1[6-9]|2[0-9]|3[01])\.|192\.168\.)/', $host)) {
            // Allow private IPs for homelab use - but log for security awareness
            error_log("StatusChecker: Checking private IP address: $host");
        }

        return true;
    }

    /**
     * Get cached status if available
     *
     * @param int $itemId
     * @return array{status: string, response_time: int, http_code?: int, error?: string}|null
     */
    private static function getCached(int $itemId): ?array
    {
        if (!isset($_SESSION['status_cache'])) {
            return null;
        }

        $cache = $_SESSION['status_cache'][$itemId] ?? null;

        if ($cache === null) {
            return null;
        }

        // Check if cache is expired
        $age = time() - ($cache['timestamp'] ?? 0);
        if ($age > self::CACHE_TTL) {
            unset($_SESSION['status_cache'][$itemId]);
            return null;
        }

        return $cache['data'] ?? null;
    }

    /**
     * Cache status result
     *
     * @param int $itemId
     * @param array{status: string, response_time: int, http_code?: int, error?: string} $result
     * @return void
     */
    private static function setCached(int $itemId, array $result): void
    {
        if (!isset($_SESSION['status_cache'])) {
            $_SESSION['status_cache'] = [];
        }

        $_SESSION['status_cache'][$itemId] = [
            'data' => $result,
            'timestamp' => time()
        ];
    }
}
