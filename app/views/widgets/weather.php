<?php
/**
 * Weather Widget View
 *
 * @var array $data Widget data
 * @var array $settings Widget settings
 */
?>

<?php if (isset($data['error'])): ?>
    <p class="text-sm text-red-600 dark:text-red-400"><?= Security::escape($data['error']) ?></p>
<?php else: ?>
    <div class="space-y-3">
        <!-- Temperature and Description -->
        <div class="flex items-start justify-between">
            <div>
                <div class="text-3xl font-bold text-gray-900 dark:text-white">
                    <?= Security::escape($data['temperature']) ?><?= Security::escape($data['unit_temp']) ?>
                </div>
                <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    <?= Security::escape($data['description']) ?>
                </div>
            </div>
            <i data-lucide="cloud" class="w-8 h-8 text-purple-600 dark:text-purple-400"></i>
        </div>

        <!-- Weather Details -->
        <div class="grid grid-cols-2 gap-2 text-sm">
            <?php if ($data['feels_like'] !== null): ?>
            <div class="flex items-center space-x-2">
                <i data-lucide="thermometer" class="w-4 h-4 text-gray-500 dark:text-gray-400"></i>
                <span class="text-gray-600 dark:text-gray-400">
                    Feels like <?= Security::escape($data['feels_like']) ?><?= Security::escape($data['unit_temp']) ?>
                </span>
            </div>
            <?php endif; ?>

            <?php if ($data['humidity'] !== null): ?>
            <div class="flex items-center space-x-2">
                <i data-lucide="droplet" class="w-4 h-4 text-gray-500 dark:text-gray-400"></i>
                <span class="text-gray-600 dark:text-gray-400">
                    <?= Security::escape($data['humidity']) ?>% humidity
                </span>
            </div>
            <?php endif; ?>

            <?php if ($data['wind_speed'] !== null): ?>
            <div class="flex items-center space-x-2">
                <i data-lucide="wind" class="w-4 h-4 text-gray-500 dark:text-gray-400"></i>
                <span class="text-gray-600 dark:text-gray-400">
                    <?= Security::escape($data['wind_speed']) ?> <?= Security::escape($data['unit_speed']) ?>
                    <?php if ($data['wind_dir'] !== null): ?>
                        <?= Security::escape($data['wind_dir']) ?>
                    <?php endif; ?>
                </span>
            </div>
            <?php endif; ?>
        </div>

        <!-- Location -->
        <div class="text-xs text-gray-500 dark:text-gray-400 pt-2 border-t border-gray-200 dark:border-gray-700">
            <i data-lucide="map-pin" class="w-3 h-3 inline"></i>
            <?= Security::escape($data['location']) ?>
            <?php if ($data['country'] !== null): ?>
                , <?= Security::escape($data['country']) ?>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
