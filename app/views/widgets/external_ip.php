<?php
/**
 * External IP Widget View
 *
 * @var array $data Widget data
 * @var array $settings Widget settings
 */
?>

<?php if (isset($data['error'])): ?>
    <p class="text-sm text-red-600 dark:text-red-400"><?= Security::escape($data['error']) ?></p>
<?php else: ?>
    <div class="space-y-2">
        <!-- IP Address -->
        <div class="flex items-center space-x-2">
            <i data-lucide="globe" class="w-4 h-4 text-purple-600 dark:text-purple-400 flex-shrink-0"></i>
            <span class="font-mono text-lg font-semibold text-gray-900 dark:text-white">
                <?= Security::escape($data['ip']) ?>
            </span>
        </div>

        <!-- Location Info -->
        <?php if (!empty($data['location'])): ?>
            <?php if (!empty($data['location']['city']) || !empty($data['location']['country'])): ?>
            <div class="flex items-center space-x-2 text-sm">
                <i data-lucide="map-pin" class="w-4 h-4 text-gray-500 dark:text-gray-400 flex-shrink-0"></i>
                <span class="text-gray-600 dark:text-gray-400">
                    <?php
                    $location = array_filter([
                        $data['location']['city'] ?? null,
                        $data['location']['postal'] ?? null,
                        $data['location']['region'] ?? null,
                        $data['location']['country'] ?? null
                    ]);
                    echo Security::escape(implode(', ', $location));
                    ?>
                </span>
            </div>
            <?php endif; ?>

            <?php if (!empty($data['location']['isp'])): ?>
            <div class="flex items-center space-x-2 text-sm">
                <i data-lucide="server" class="w-4 h-4 text-gray-500 dark:text-gray-400 flex-shrink-0"></i>
                <span class="text-gray-600 dark:text-gray-400">
                    <?= Security::escape($data['location']['isp']) ?>
                </span>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
<?php endif; ?>
