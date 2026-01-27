<?php
/**
 * Clock Widget View
 *
 * @var array $data Widget data
 * @var array $settings Widget settings
 */
?>

<?php if (isset($data['error'])): ?>
    <p class="text-sm text-red-600 dark:text-red-400"><?= Security::escape($data['error']) ?></p>
<?php else: ?>
    <div class="space-y-2">
        <!-- Time Display -->
        <div class="text-center">
            <div class="text-4xl font-bold text-gray-900 dark:text-white font-mono live-time"
                 data-timestamp="<?= $data['timestamp'] ?>"
                 data-format="<?= Security::escape($data['format']) ?>"
                 data-show-seconds="<?= $data['show_seconds'] ? '1' : '0' ?>"
                 data-timezone="<?= Security::escape($data['timezone']) ?>">
                <?= Security::escape($data['time']) ?>
            </div>
            <div class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                <?= Security::escape($data['date']) ?>
            </div>
        </div>

        <!-- Timezone Info -->
        <div class="text-xs text-gray-500 dark:text-gray-400 text-center pt-2 border-t border-gray-200 dark:border-gray-700">
            <i data-lucide="globe" class="w-3 h-3 inline"></i>
            <?= Security::escape($data['timezone']) ?> (<?= Security::escape($data['timezone_abbr']) ?>)
        </div>
    </div>
<?php endif; ?>
