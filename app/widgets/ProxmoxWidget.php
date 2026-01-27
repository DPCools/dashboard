<?php

declare(strict_types=1);

/**
 * Proxmox Widget - Displays Proxmox cluster information
 *
 * Shows all nodes in the cluster with CPU, RAM, VM/LXC counts
 */
class ProxmoxWidget extends Widget
{
    /**
     * Fetch widget data
     *
     * @return array Widget data including cluster and node information
     */
    public function getData(): array
    {
        // Reason: Check cache first (cache for 1 minute since Proxmox data changes frequently)
        $cacheKey = 'proxmox_data';
        $cached = $this->getCachedData($cacheKey);

        if ($cached !== null) {
            return $cached;
        }

        try {
            $apiUrl = trim($this->getSetting('api_url', ''));
            $username = trim($this->getSetting('username', ''));
            $tokenId = trim($this->getSetting('token_id', ''));
            $tokenSecret = trim($this->getSetting('token_secret', ''));

            if (empty($apiUrl)) {
                return ['error' => 'Proxmox API URL not configured'];
            }

            if (empty($username) || empty($tokenId) || empty($tokenSecret)) {
                return ['error' => 'Missing credentials - ensure all fields are filled'];
            }

            // Remove trailing slash from API URL
            $apiUrl = rtrim($apiUrl, '/');

            // Reason: Clean up token_id if user accidentally copied full format (USER@REALM!TOKENID)
            // We only need the TOKENID part
            if (strpos($tokenId, '!') !== false) {
                $parts = explode('!', $tokenId);
                $tokenId = end($parts);
            }

            // Reason: Log the authentication attempt for debugging (without exposing secret)
            error_log("Proxmox auth attempt - URL: $apiUrl, User: $username, TokenID: $tokenId");

            // Reason: Get cluster resources which includes nodes, VMs, and containers
            $resources = $this->proxmoxApiRequest($apiUrl, $username, $tokenId, $tokenSecret, '/api2/json/cluster/resources');

            if (isset($resources['error'])) {
                return $resources;
            }

            // Reason: Parse and organize the data
            $nodes = [];
            $clusterStats = [
                'total_cpu' => 0,
                'used_cpu' => 0,
                'total_memory' => 0,
                'used_memory' => 0,
                'total_vms' => 0,
                'total_lxc' => 0,
                'total_nodes' => 0
            ];

            // Reason: Process cluster resources
            foreach ($resources as $resource) {
                $type = $resource['type'] ?? '';

                if ($type === 'node') {
                    // Node information
                    $nodeName = $resource['node'] ?? 'unknown';
                    $nodes[$nodeName] = [
                        'name' => $nodeName,
                        'status' => $resource['status'] ?? 'unknown',
                        'cpu' => round(($resource['cpu'] ?? 0) * 100, 1),
                        'maxcpu' => $resource['maxcpu'] ?? 0,
                        'mem' => $resource['mem'] ?? 0,
                        'maxmem' => $resource['maxmem'] ?? 0,
                        'mem_percent' => $resource['maxmem'] > 0 ? round(($resource['mem'] / $resource['maxmem']) * 100, 1) : 0,
                        'disk' => $resource['disk'] ?? 0,
                        'maxdisk' => $resource['maxdisk'] ?? 0,
                        'uptime' => $resource['uptime'] ?? 0,
                        'vms' => 0,
                        'lxc' => 0
                    ];

                    $clusterStats['total_nodes']++;
                    $clusterStats['total_cpu'] += $resource['maxcpu'] ?? 0;
                    $clusterStats['used_cpu'] += ($resource['cpu'] ?? 0) * ($resource['maxcpu'] ?? 0);
                    $clusterStats['total_memory'] += $resource['maxmem'] ?? 0;
                    $clusterStats['used_memory'] += $resource['mem'] ?? 0;
                }
            }

            // Reason: Count VMs and LXCs per node
            foreach ($resources as $resource) {
                $type = $resource['type'] ?? '';
                $nodeName = $resource['node'] ?? '';

                if (isset($nodes[$nodeName])) {
                    if ($type === 'qemu') {
                        $nodes[$nodeName]['vms']++;
                        $clusterStats['total_vms']++;
                    } elseif ($type === 'lxc') {
                        $nodes[$nodeName]['lxc']++;
                        $clusterStats['total_lxc']++;
                    }
                }
            }

            // Calculate cluster-wide percentages
            if ($clusterStats['total_cpu'] > 0) {
                $clusterStats['cpu_percent'] = round(($clusterStats['used_cpu'] / $clusterStats['total_cpu']) * 100, 1);
            } else {
                $clusterStats['cpu_percent'] = 0;
            }

            if ($clusterStats['total_memory'] > 0) {
                $clusterStats['mem_percent'] = round(($clusterStats['used_memory'] / $clusterStats['total_memory']) * 100, 1);
            } else {
                $clusterStats['mem_percent'] = 0;
            }

            $data = [
                'nodes' => array_values($nodes),
                'cluster' => $clusterStats
            ];

            // Reason: Cache for 60 seconds (Proxmox updates frequently)
            $this->setCachedData($cacheKey, $data, 60);

            return $data;

        } catch (Exception $e) {
            return ['error' => 'Failed to fetch Proxmox data: ' . $e->getMessage()];
        }
    }

    /**
     * Make a request to the Proxmox API
     *
     * @param string $apiUrl Base API URL
     * @param string $username Username (e.g., root@pam)
     * @param string $tokenId API Token ID
     * @param string $tokenSecret API Token Secret
     * @param string $endpoint API endpoint path
     * @return array|null Response data or null on failure
     */
    private function proxmoxApiRequest(string $apiUrl, string $username, string $tokenId, string $tokenSecret, string $endpoint): ?array
    {
        $url = $apiUrl . $endpoint;

        // Reason: Build authorization header with API token (format: PVEAPIToken=USER@REALM!TOKENID=SECRET)
        $authHeader = "PVEAPIToken={$username}!{$tokenId}={$tokenSecret}";

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false, // Reason: Many Proxmox installations use self-signed certs
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER => [
                "Authorization: {$authHeader}",
                'Content-Type: application/json'
            ],
            CURLOPT_VERBOSE => false
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        // Reason: Provide detailed error messages based on HTTP status code
        if ($response === false) {
            return ['error' => 'Connection failed: ' . $curlError . '. Check API URL and network connectivity.'];
        }

        if ($httpCode === 401) {
            return ['error' => 'Authentication failed (401). Check: 1) Token ID and Secret are correct, 2) Token has "PVEAuditor" or "Administrator" role, 3) "Privilege Separation" is disabled when creating token'];
        }

        if ($httpCode === 403) {
            return ['error' => 'Access denied (403). Token needs "PVEAuditor" or "Administrator" privileges'];
        }

        if ($httpCode === 495 || $httpCode === 0) {
            return ['error' => 'SSL certificate error. Proxmox may be using self-signed cert. This should be handled automatically.'];
        }

        if ($httpCode !== 200) {
            $errorDetails = '';
            if ($response) {
                $decoded = json_decode($response, true);
                if (isset($decoded['errors'])) {
                    $errorDetails = ': ' . json_encode($decoded['errors']);
                }
            }
            return ['error' => "HTTP $httpCode error{$errorDetails}. URL: $url"];
        }

        $decoded = json_decode($response, true);

        if (!isset($decoded['data'])) {
            return ['error' => 'Invalid API response format. Response: ' . substr($response, 0, 200)];
        }

        return $decoded['data'];
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

        if (empty($settings['api_url'])) {
            $errors[] = 'Proxmox API URL is required';
        } elseif (!filter_var($settings['api_url'], FILTER_VALIDATE_URL)) {
            $errors[] = 'Invalid Proxmox API URL format';
        }

        if (empty($settings['username'])) {
            $errors[] = 'Username is required (e.g., root@pam)';
        }

        if (empty($settings['token_id'])) {
            $errors[] = 'API Token ID is required';
        }

        if (empty($settings['token_secret'])) {
            $errors[] = 'API Token Secret is required';
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
            'api_url' => '',
            'username' => 'root@pam',
            'token_id' => '',
            'token_secret' => '',
            'show_cluster_stats' => true,
            'show_node_details' => true
        ];
    }
}
