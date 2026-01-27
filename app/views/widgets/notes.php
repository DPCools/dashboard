<?php
/**
 * Notes Widget View
 *
 * @var array $data Widget data
 * @var array $settings Widget settings
 */
?>

<div class="prose prose-sm max-w-none dark:prose-invert">
    <?php if (empty($data['content'])): ?>
        <p class="text-gray-500 dark:text-gray-400 italic">No notes added yet...</p>
    <?php else: ?>
        <div class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">
<?= Security::escape($data['content']) ?>
        </div>
    <?php endif; ?>
</div>
