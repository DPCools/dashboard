<?php
/**
 * Proxmox Widget View
 *
 * @var array $data Widget data
 * @var array $settings Widget settings
 */
?>

<?php if (isset($data['error'])): ?>
    <div class="space-y-2">
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-3">
            <p class="text-sm font-semibold text-red-800 dark:text-red-200 mb-2">Connection Error</p>
            <p class="text-xs text-red-700 dark:text-red-300"><?= Security::escape($data['error']) ?></p>
        </div>
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-3">
            <p class="text-xs font-semibold text-blue-800 dark:text-blue-200 mb-2">Troubleshooting Steps:</p>
            <ol class="text-xs text-blue-700 dark:text-blue-300 space-y-1 list-decimal list-inside">
                <li>Verify API URL (must include https:// and :8006)</li>
                <li>Create API Token: Datacenter → Permissions → API Tokens → Add</li>
                <li><strong>Important:</strong> Uncheck "Privilege Separation" when creating token</li>
                <li>Verify token has "Administrator" or "PVEAuditor" role</li>
                <li>Token format: USER@REALM!TOKENID (e.g., root@pam!dashboard)</li>
                <li>Check Proxmox firewall allows connections from this server</li>
            </ol>
        </div>
    </div>
<?php else: ?>
    <div class="space-y-3">
        <!-- Cluster Overview -->
        <?php if (!empty($settings['show_cluster_stats']) && isset($data['cluster'])): ?>
        <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-3 border border-purple-200 dark:border-purple-800">
            <h5 class="text-xs font-semibold text-purple-900 dark:text-purple-200 mb-2 flex items-center">
                <i data-lucide="server" class="w-3 h-3 mr-1"></i>
                Cluster Overview
            </h5>
            <div class="grid grid-cols-2 gap-2 text-xs">
                <div>
                    <span class="text-gray-600 dark:text-gray-400">Nodes:</span>
                    <span class="font-semibold text-gray-900 dark:text-white ml-1"><?= $data['cluster']['total_nodes'] ?></span>
                </div>
                <div>
                    <span class="text-gray-600 dark:text-gray-400">VMs:</span>
                    <span class="font-semibold text-gray-900 dark:text-white ml-1"><?= $data['cluster']['total_vms'] ?></span>
                </div>
                <div>
                    <span class="text-gray-600 dark:text-gray-400">LXCs:</span>
                    <span class="font-semibold text-gray-900 dark:text-white ml-1"><?= $data['cluster']['total_lxc'] ?></span>
                </div>
                <div>
                    <span class="text-gray-600 dark:text-gray-400">Cores:</span>
                    <span class="font-semibold text-gray-900 dark:text-white ml-1"><?= (int)$data['cluster']['total_cpu'] ?></span>
                </div>
            </div>

            <!-- Cluster CPU Usage -->
            <div class="mt-2">
                <div class="flex items-center justify-between text-xs mb-1">
                    <span class="text-gray-600 dark:text-gray-400">CPU</span>
                    <span class="font-semibold text-gray-900 dark:text-white"><?= $data['cluster']['cpu_percent'] ?>%</span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                    <div class="bg-blue-600 dark:bg-blue-500 h-2 rounded-full transition-all" style="width: <?= $data['cluster']['cpu_percent'] ?>%"></div>
                </div>
            </div>

            <!-- Cluster Memory Usage -->
            <div class="mt-2">
                <div class="flex items-center justify-between text-xs mb-1">
                    <span class="text-gray-600 dark:text-gray-400">Memory</span>
                    <span class="font-semibold text-gray-900 dark:text-white">
                        <?= round($data['cluster']['used_memory'] / 1024 / 1024 / 1024, 1) ?> / <?= round($data['cluster']['total_memory'] / 1024 / 1024 / 1024, 1) ?> GB
                    </span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                    <div class="bg-green-600 dark:bg-green-500 h-2 rounded-full transition-all" style="width: <?= $data['cluster']['mem_percent'] ?>%"></div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Node Details -->
        <?php if (!empty($settings['show_node_details']) && !empty($data['nodes'])): ?>
        <div class="space-y-2">
            <h5 class="text-xs font-semibold text-gray-700 dark:text-gray-300">Nodes</h5>
            <?php foreach ($data['nodes'] as $node): ?>
            <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-2 border border-gray-200 dark:border-gray-700">
                <!-- Node Header -->
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center space-x-2">
                        <div class="w-2 h-2 rounded-full <?= $node['status'] === 'online' ? 'bg-green-500' : 'bg-red-500' ?>"></div>
                        <span class="text-sm font-semibold text-gray-900 dark:text-white"><?= Security::escape($node['name']) ?></span>
                    </div>
                    <div class="flex items-center space-x-2 text-xs text-gray-600 dark:text-gray-400">
                        <span title="Virtual Machines">
                            <i data-lucide="box" class="w-3 h-3 inline"></i>
                            <?= $node['vms'] ?>
                        </span>
                        <span title="LXC Containers">
                            <i data-lucide="package" class="w-3 h-3 inline"></i>
                            <?= $node['lxc'] ?>
                        </span>
                        <span title="CPU Cores">
                            <i data-lucide="cpu" class="w-3 h-3 inline"></i>
                            <?= $node['maxcpu'] ?>
                        </span>
                    </div>
                </div>

                <!-- CPU Bar -->
                <div class="mb-1.5">
                    <div class="flex items-center justify-between text-xs mb-0.5">
                        <span class="text-gray-600 dark:text-gray-400">CPU</span>
                        <span class="font-medium text-gray-900 dark:text-white"><?= $node['cpu'] ?>%</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                        <div class="bg-blue-600 dark:bg-blue-500 h-1.5 rounded-full transition-all" style="width: <?= min($node['cpu'], 100) ?>%"></div>
                    </div>
                </div>

                <!-- Memory Bar -->
                <div>
                    <div class="flex items-center justify-between text-xs mb-0.5">
                        <span class="text-gray-600 dark:text-gray-400">RAM</span>
                        <span class="font-medium text-gray-900 dark:text-white">
                            <?= round($node['mem'] / 1024 / 1024 / 1024, 1) ?> / <?= round($node['maxmem'] / 1024 / 1024 / 1024, 1) ?> GB
                            <span class="text-gray-500 dark:text-gray-400">(<?= $node['mem_percent'] ?>%)</span>
                        </span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                        <div class="bg-green-600 dark:bg-green-500 h-1.5 rounded-full transition-all" style="width: <?= min($node['mem_percent'], 100) ?>%"></div>
                    </div>
                </div>

                <!-- Free RAM -->
                <div class="mt-1 text-xs text-gray-600 dark:text-gray-400">
                    <span>Free: <?= round(($node['maxmem'] - $node['mem']) / 1024 / 1024 / 1024, 1) ?> GB</span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
<?php endif; ?>
