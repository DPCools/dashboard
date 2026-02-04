<?php
/**
 * File Sharing - Public View
 * Public file view with password prompt if protected
 */
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Security::escape($file['original_filename']) ?> - HomeDash</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-gray-50 dark:bg-gray-900 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-2xl w-full">
        <!-- File Card -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-8">
            <!-- Icon -->
            <div class="flex justify-center mb-6">
                <div class="w-20 h-20 bg-purple-100 dark:bg-purple-900 rounded-full flex items-center justify-center">
                    <i data-lucide="<?= FileHelper::getMimeTypeIcon($file['mime_type']) ?>" class="w-10 h-10 text-purple-600 dark:text-purple-400"></i>
                </div>
            </div>

            <!-- File Info -->
            <div class="text-center mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                    <?= Security::escape($file['original_filename']) ?>
                </h1>

                <?php if (!empty($file['description'])): ?>
                    <p class="text-gray-600 dark:text-gray-400 mb-4">
                        <?= Security::escape($file['description']) ?>
                    </p>
                <?php endif; ?>

                <div class="flex items-center justify-center gap-4 text-sm text-gray-500 dark:text-gray-400">
                    <span><?= FileHelper::formatFileSize($file['file_size']) ?></span>
                    <span>·</span>
                    <span><?= $file['download_count'] ?> downloads</span>
                    <?php if ($file['expires_at'] !== null): ?>
                        <span>·</span>
                        <span>Expires <?= date('M j, Y', strtotime($file['expires_at'])) ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($needs_password): ?>
                <!-- Password Form -->
                <div id="passwordSection">
                    <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4 mb-6">
                        <div class="flex items-center text-yellow-700 dark:text-yellow-300">
                            <i data-lucide="lock" class="w-5 h-5 mr-2"></i>
                            <span class="text-sm font-medium">This file is password protected</span>
                        </div>
                    </div>

                    <form id="passwordForm" class="mb-4">
                        <input type="hidden" name="token" value="<?= $file['token'] ?>">
                        <div class="flex gap-2">
                            <input type="password" name="password" id="passwordInput" placeholder="Enter password" required autofocus autocomplete="current-password" class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 dark:bg-gray-700 dark:text-white">
                            <button type="submit" class="px-6 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors">
                                Unlock
                            </button>
                        </div>
                        <div id="passwordError" class="text-red-600 dark:text-red-400 text-sm mt-2 hidden"></div>
                    </form>
                </div>

                <!-- Hidden Download Button -->
                <div id="downloadSection" class="hidden">
                    <a href="<?= View::url('/s/' . $file['token'] . '/download') ?>" class="block w-full text-center px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors">
                        <i data-lucide="download" class="w-5 h-5 inline mr-2"></i>
                        Download File
                    </a>
                </div>
            <?php else: ?>
                <!-- Download Button (No Password) -->
                <a href="<?= View::url('/s/' . $file['token'] . '/download') ?>" class="block w-full text-center px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors">
                    <i data-lucide="download" class="w-5 h-5 inline mr-2"></i>
                    Download File
                </a>
            <?php endif; ?>

            <!-- Uploaded Date -->
            <div class="text-center mt-6 text-sm text-gray-500 dark:text-gray-400">
                Uploaded <?= date('F j, Y', strtotime($file['created_at'])) ?>
            </div>
        </div>

        <!-- Branding -->
        <div class="text-center mt-6 text-sm text-gray-500 dark:text-gray-400">
            Powered by <a href="https://github.com/DPCools/dashboard" class="text-purple-600 hover:text-purple-700 dark:text-purple-400 dark:hover:text-purple-300">HomeDash</a>
        </div>
    </div>

    <script>
        lucide.createIcons();

        <?php if ($needs_password): ?>
        // Password verification
        document.getElementById('passwordForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const form = e.target;
            const formData = new FormData(form);
            const errorDiv = document.getElementById('passwordError');
            const submitButton = form.querySelector('button[type="submit"]');

            // Disable submit button
            submitButton.disabled = true;
            submitButton.textContent = 'Verifying...';

            try {
                const response = await fetch('<?= View::url('/s/' . $file['token'] . '/verify') ?>', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    // Hide password form, show download button
                    document.getElementById('passwordSection').classList.add('hidden');
                    document.getElementById('downloadSection').classList.remove('hidden');
                    lucide.createIcons();
                } else {
                    // Show error
                    errorDiv.textContent = result.error || 'Incorrect password';
                    errorDiv.classList.remove('hidden');
                    submitButton.disabled = false;
                    submitButton.textContent = 'Unlock';
                }
            } catch (error) {
                errorDiv.textContent = 'An error occurred. Please try again.';
                errorDiv.classList.remove('hidden');
                submitButton.disabled = false;
                submitButton.textContent = 'Unlock';
            }
        });
        <?php endif; ?>
    </script>
</body>
</html>
