<?php
declare(strict_types=1);

use phpseclib3\Net\SSH2;
use phpseclib3\Crypt\PublicKeyLoader;

/**
 * SshTransport
 *
 * SSH-based transport for command execution using phpseclib
 */
class SshTransport implements TransportInterface
{
    private ?SSH2 $ssh = null;
    private ?string $lastError = null;
    private array $host;
    private array $credentials;

    /**
     * Constructor
     *
     * @param array $host Host configuration
     * @param array $credentials Authentication credentials
     */
    public function __construct(array $host, array $credentials)
    {
        $this->host = $host;
        $this->credentials = $credentials;
    }

    /**
     * Connect to the remote host via SSH
     *
     * @return bool True if connection successful
     * @throws RuntimeException If connection fails
     */
    public function connect(): bool
    {
        try {
            $hostname = $this->host['hostname'];
            $port = $this->host['port'] ?? 22;
            $username = $this->host['username'] ?? 'root';

            // Create SSH connection
            $this->ssh = new SSH2($hostname, $port);
            $this->ssh->setTimeout(10); // 10 second connection timeout

            // Authenticate
            $authenticated = false;

            // Try SSH key authentication first
            if (!empty($this->credentials['ssh_key'])) {
                try {
                    $key = PublicKeyLoader::load($this->credentials['ssh_key']);
                    $authenticated = $this->ssh->login($username, $key);
                } catch (Exception $e) {
                    $this->lastError = 'SSH key authentication failed: ' . $e->getMessage();
                }
            }

            // Fall back to password authentication
            if (!$authenticated && !empty($this->credentials['password'])) {
                $authenticated = $this->ssh->login($username, $this->credentials['password']);
            }

            if (!$authenticated) {
                $this->lastError = 'Authentication failed: No valid credentials provided';
                throw new RuntimeException($this->lastError);
            }

            $this->lastError = null;
            return true;
        } catch (Exception $e) {
            $this->lastError = 'SSH connection failed: ' . $e->getMessage();
            throw new RuntimeException($this->lastError);
        }
    }

    /**
     * Wrap command with su elevation if needed
     *
     * @param string $command Original command to execute
     * @return string Wrapped command or original if no elevation
     * @throws RuntimeException If su elevation is enabled but credentials not configured
     */
    private function wrapCommandWithSu(string $command): string
    {
        require_once APP_PATH . '/models/CommandHost.php';

        // Check if su elevation is enabled
        if (!CommandHost::usesSuElevation($this->host)) {
            return $command;
        }

        // Get su credentials
        $suUsername = CommandHost::getSuUsername($this->host);
        $suPassword = CommandHost::loadSuPassword($this->host['id']);
        $suShell = CommandHost::getSuShell($this->host);

        if (empty($suUsername) || empty($suPassword)) {
            throw new RuntimeException('Su elevation enabled but credentials not configured');
        }

        // Escape password for use in single quotes
        $escapedPassword = str_replace("'", "'\\''", $suPassword);

        // Determine base shell path (extract path without flags)
        $shellParts = explode(' ', $suShell);
        $shellPath = $shellParts[0]; // e.g., /bin/bash

        // Use login shell approach (with - flag) to properly initialize target user's environment
        // This avoids permission denied errors from trying to access source user's home directory
        // Format: { printf '%s\n' 'password'; } | su - username -c 'command'

        // Escape command for use inside single quotes
        $singleQuotedCommand = str_replace("'", "'\\''", $command);

        $wrappedCommand = sprintf(
            "{ printf '%%s\\n' '%s'; } | su - %s -c '%s'",
            $escapedPassword,
            escapeshellarg($suUsername),
            $singleQuotedCommand
        );

        // Debug logging (without password)
        error_log('SshTransport::wrapCommandWithSu - Original command: ' . $command);
        error_log('SshTransport::wrapCommandWithSu - Su username: ' . $suUsername);
        error_log('SshTransport::wrapCommandWithSu - Shell path: ' . $shellPath);
        error_log('SshTransport::wrapCommandWithSu - Wrapped command (sanitized): ' .
            str_replace($escapedPassword, '***PASSWORD***', $wrappedCommand));

        return $wrappedCommand;
    }

    /**
     * Execute a command on the remote host
     *
     * @param string $command The command to execute
     * @param int $timeout Timeout in seconds
     * @return array ['exit_code' => int, 'stdout' => string, 'stderr' => string, 'execution_time_ms' => int]
     * @throws RuntimeException If execution fails
     */
    public function execute(string $command, int $timeout = 30): array
    {
        if (!$this->isConnected()) {
            throw new RuntimeException('Not connected to remote host');
        }

        try {
            $startTime = microtime(true);

            // Set command timeout
            $this->ssh->setTimeout($timeout);

            // Reason: Wrap command with su elevation if configured
            $wrappedCommand = $this->wrapCommandWithSu($command);

            // Execute command and capture both stdout and stderr
            // Reason: Redirect stderr to stdout to capture all output, append exit code marker
            $commandWithExitCode = "($wrappedCommand) 2>&1; echo \"__EXIT_CODE__:\$?\"";

            $output = $this->ssh->exec($commandWithExitCode);
            $executionTimeMs = (int)((microtime(true) - $startTime) * 1000);

            // Parse output to extract exit code
            $exitCode = 0;
            if (preg_match('/__EXIT_CODE__:(\d+)$/', $output, $matches)) {
                $exitCode = (int)$matches[1];
                $output = preg_replace('/__EXIT_CODE__:\d+$/', '', $output);
            }

            // Trim trailing whitespace
            $output = rtrim($output);

            // Separate stderr from stdout if possible
            // Reason: phpseclib doesn't separate streams, so we use exit code to determine success
            $stderr = '';
            if ($exitCode !== 0) {
                // If command failed, treat output as stderr
                $stderr = $output;
                $stdout = '';
            } else {
                $stdout = $output;
            }

            return [
                'exit_code' => $exitCode,
                'stdout' => $stdout,
                'stderr' => $stderr,
                'execution_time_ms' => $executionTimeMs
            ];
        } catch (Exception $e) {
            $this->lastError = 'Command execution failed: ' . $e->getMessage();
            throw new RuntimeException($this->lastError);
        }
    }

    /**
     * Test if connection is alive
     *
     * @return bool True if connection is alive
     */
    public function isConnected(): bool
    {
        return $this->ssh !== null && $this->ssh->isConnected();
    }

    /**
     * Disconnect from the remote host
     *
     * @return void
     */
    public function disconnect(): void
    {
        if ($this->ssh !== null) {
            $this->ssh->disconnect();
            $this->ssh = null;
        }
    }

    /**
     * Get the last error message
     *
     * @return string|null Error message or null if no error
     */
    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    /**
     * Destructor - ensure connection is closed
     */
    public function __destruct()
    {
        $this->disconnect();
    }
}
