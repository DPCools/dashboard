<?php
/**
 * RSS Feed Widget View
 *
 * @var array $data Widget data
 * @var array $settings Widget settings
 */
?>

<?php if (isset($data['error'])): ?>
    <p class="text-sm text-red-600 dark:text-red-400"><?= Security::escape($data['error']) ?></p>
<?php else: ?>
    <div class="space-y-3">
        <?php if (empty($data['items'])): ?>
            <p class="text-sm text-gray-500 dark:text-gray-400 italic">No feed items available</p>
        <?php else: ?>
            <?php foreach ($data['items'] as $item): ?>
            <div class="border-b border-gray-200 dark:border-gray-700 pb-2 last:border-0">
                <a href="<?= Security::escape($item['link']) ?>"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="block hover:bg-purple-50 dark:hover:bg-purple-900/20 rounded p-2 -m-2 transition-colors">
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex-1 min-w-0">
                            <h5 class="text-sm font-medium text-gray-900 dark:text-white hover:text-purple-600 dark:hover:text-purple-400 transition-colors line-clamp-2">
                                <?= Security::escape($item['title']) ?>
                            </h5>
                            <?php if (!empty($item['description'])): ?>
                            <p class="text-xs text-gray-600 dark:text-gray-400 mt-1 line-clamp-2">
                                <?= Security::escape($item['description']) ?>
                            </p>
                            <?php endif; ?>
                            <?php if (!empty($item['pub_date'])): ?>
                            <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                                <?= Security::escape($item['pub_date']) ?>
                            </p>
                            <?php endif; ?>
                        </div>
                        <i data-lucide="external-link" class="w-3 h-3 text-gray-400 dark:text-gray-500 flex-shrink-0"></i>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
<?php endif; ?>
