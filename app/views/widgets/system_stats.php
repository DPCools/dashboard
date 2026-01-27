<?php
/**
 * System Stats Widget View
 *
 * @var array $data Widget data
 * @var array $settings Widget settings
 */
?>

<?php if (isset($data['error'])): ?>
    <p class="text-sm text-red-600 dark:text-red-400"><?= Security::escape($data['error']) ?></p>
<?php else: ?>
    <div class="space-y-3">
        <!-- CPU Load -->
        <?php if (isset($data['cpu'])): ?>
        <div>
            <div class="flex items-center justify-between text-sm mb-1">
                <div class="flex items-center space-x-2">
                    <i data-lucide="cpu" class="w-4 h-4 text-purple-600 dark:text-purple-400"></i>
                    <span class="font-medium text-gray-700 dark:text-gray-300">CPU Load</span>
                </div>
                <span class="text-gray-600 dark:text-gray-400 font-mono text-xs">
                    <?= Security::escape($data['cpu']['load_1min']) ?> /
                    <?= Security::escape($data['cpu']['load_5min']) ?> /
                    <?= Security::escape($data['cpu']['load_15min']) ?>
                </span>
            </div>
            <div class="text-xs text-gray-500 dark:text-gray-400">
                1m / 5m / 15m averages
            </div>
        </div>
        <?php endif; ?>

        <!-- Memory Usage -->
        <?php if (isset($data['memory'])): ?>
        <div>
            <div class="flex items-center justify-between text-sm mb-2">
                <div class="flex items-center space-x-2">
                    <i data-lucide="database" class="w-4 h-4 text-purple-600 dark:text-purple-400"></i>
                    <span class="font-medium text-gray-700 dark:text-gray-300">Memory</span>
                </div>
                <span class="text-gray-600 dark:text-gray-400 font-mono text-xs">
                    <?= Security::escape($data['memory']['used_mb']) ?> / <?= Security::escape($data['memory']['total_mb']) ?> MB
                </span>
            </div>
            <!-- Progress bar -->
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                <div class="bg-purple-600 dark:bg-purple-500 h-2 rounded-full transition-all"
                     style="width: <?= Security::escape($data['memory']['usage_percent']) ?>%">
                </div>
            </div>
            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                <?= Security::escape($data['memory']['usage_percent']) ?>% used
            </div>
        </div>
        <?php endif; ?>

        <!-- Disk Usage -->
        <?php if (isset($data['disk'])): ?>
        <div>
            <div class="flex items-center justify-between text-sm mb-2">
                <div class="flex items-center space-x-2">
                    <i data-lucide="hard-drive" class="w-4 h-4 text-purple-600 dark:text-purple-400"></i>
                    <span class="font-medium text-gray-700 dark:text-gray-300">
                        Disk (<?= Security::escape($data['disk']['path']) ?>)
                    </span>
                </div>
                <span class="text-gray-600 dark:text-gray-400 font-mono text-xs">
                    <?= Security::escape($data['disk']['used_gb']) ?> / <?= Security::escape($data['disk']['total_gb']) ?> GB
                </span>
            </div>
            <!-- Progress bar -->
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                <div class="bg-purple-600 dark:bg-purple-500 h-2 rounded-full transition-all"
                     style="width: <?= Security::escape($data['disk']['usage_percent']) ?>%">
                </div>
            </div>
            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                <?= Security::escape($data['disk']['usage_percent']) ?>% used (<?= Security::escape($data['disk']['free_gb']) ?> GB free)
            </div>
        </div>
        <?php endif; ?>
    </div>
<?php endif; ?>
