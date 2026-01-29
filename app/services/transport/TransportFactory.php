<?php
declare(strict_types=1);

/**
 * TransportFactory
 *
 * Factory for creating transport adapters based on host type
 */
class TransportFactory
{
    /**
     * Create a transport adapter for a host
     *
     * @param array $host Host configuration
     * @return TransportInterface Transport adapter instance
     * @throws InvalidArgumentException If host type is unsupported
     */
    public static function create(array $host): TransportInterface
    {
        $hostType = $host['host_type'] ?? '';

        // Get credentials for the host
        $credentials = self::loadCredentials($host['id']);

        switch ($hostType) {
            case 'ssh':
            case 'proxmox':
                return new SshTransport($host, $credentials);

            case 'docker':
                // Docker transport will be implemented in Phase 6
                throw new RuntimeException('Docker transport not yet implemented');

            default:
                throw new InvalidArgumentException("Unsupported host type: {$hostType}");
        }
    }

    /**
     * Load and decrypt credentials for a host
     *
     * @param int $hostId Host ID
     * @return array Decrypted credentials
     */
    private static function loadCredentials(int $hostId): array
    {
        $credentials = [];

        // Try to load SSH key
        $sshKey = CommandHost::getDecryptedCredential($hostId, 'ssh_key');
        if ($sshKey !== null) {
            $credentials['ssh_key'] = $sshKey;
        }

        // Try to load password
        $password = CommandHost::getDecryptedCredential($hostId, 'password');
        if ($password !== null) {
            $credentials['password'] = $password;
        }

        // Try to load API token (for future use)
        $apiToken = CommandHost::getDecryptedCredential($hostId, 'api_token');
        if ($apiToken !== null) {
            $credentials['api_token'] = $apiToken;
        }

        return $credentials;
    }

    /**
     * Test connection to a host
     *
     * @param array $host Host configuration
     * @return array ['success' => bool, 'error' => string|null, 'time_ms' => int, 'message' => string|null]
     */
    public static function testConnection(array $host): array
    {
        $startTime = microtime(true);

        try {
            $transport = self::create($host);
            $transport->connect();

            // Execute simple test command
            $result = $transport->execute('echo "test"', 5);

            if ($result['exit_code'] !== 0) {
                $transport->disconnect();
                $timeMs = (int)((microtime(true) - $startTime) * 1000);

                return [
                    'success' => false,
                    'error' => 'Test command failed with exit code ' . $result['exit_code'],
                    'time_ms' => $timeMs,
                    'message' => null
                ];
            }

            // If su elevation is enabled, test that too
            $message = 'Connection successful!';
            if (CommandHost::usesSuElevation($host)) {
                $suUsername = CommandHost::getSuUsername($host);
                $whoamiResult = $transport->execute('whoami', 10);

                if ($whoamiResult['exit_code'] === 0) {
                    $fullOutput = trim($whoamiResult['stdout']);

                    // Extract the actual username from the output
                    // The username should be on a line by itself, often the last line
                    // Ignore warning messages like "Permission denied" or "Could not chdir"
                    $lines = array_filter(array_map('trim', explode("\n", $fullOutput)));
                    $actualUsername = '';

                    // Look for a line that looks like a username (single word, no special chars)
                    foreach (array_reverse($lines) as $line) {
                        // Skip lines with error indicators
                        if (stripos($line, 'permission denied') !== false ||
                            stripos($line, 'could not') !== false ||
                            stripos($line, 'bash:') !== false ||
                            stripos($line, 'su:') !== false ||
                            empty($line)) {
                            continue;
                        }

                        // Check if line starts with "Password:" (su's prompt) followed by username
                        if (preg_match('/^Password:\s*(\w+)$/i', $line, $matches)) {
                            $actualUsername = $matches[1];
                            break;
                        }

                        // Check if line is just a username (alphanumeric, underscores, hyphens)
                        if (preg_match('/^[a-z0-9_-]+$/i', $line)) {
                            $actualUsername = $line;
                            break;
                        }
                    }

                    // Log for debugging
                    error_log('Su elevation test - Full output: ' . $fullOutput);
                    error_log('Su elevation test - Extracted username: ' . $actualUsername);
                    error_log('Su elevation test - Expected username: ' . $suUsername);

                    if (empty($actualUsername)) {
                        $transport->disconnect();
                        $timeMs = (int)((microtime(true) - $startTime) * 1000);
                        return [
                            'success' => false,
                            'error' => 'Su elevation: Could not extract username from output. Full output: ' . $fullOutput,
                            'time_ms' => $timeMs,
                            'message' => null
                        ];
                    }

                    if ($actualUsername === $suUsername) {
                        $message = "Connection successful! Su elevation verified (running as {$suUsername}).";
                    } else {
                        $message = "Connection successful, but unexpected user. Expected: {$suUsername}, got: {$actualUsername}";
                    }
                } else {
                    // Su elevation test failed
                    $errorOutput = !empty($whoamiResult['stderr']) ? $whoamiResult['stderr'] : $whoamiResult['stdout'];
                    $transport->disconnect();
                    $timeMs = (int)((microtime(true) - $startTime) * 1000);

                    return [
                        'success' => false,
                        'error' => 'Su elevation test failed with exit code ' . $whoamiResult['exit_code'] . '. Error: ' . trim($errorOutput),
                        'time_ms' => $timeMs,
                        'message' => null
                    ];
                }
            }

            $transport->disconnect();
            $timeMs = (int)((microtime(true) - $startTime) * 1000);

            return [
                'success' => true,
                'error' => null,
                'time_ms' => $timeMs,
                'message' => $message
            ];
        } catch (Exception $e) {
            $timeMs = (int)((microtime(true) - $startTime) * 1000);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'time_ms' => $timeMs,
                'message' => null
            ];
        }
    }
}
