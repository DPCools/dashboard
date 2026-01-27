<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full text-center space-y-8">
        <div>
            <i data-lucide="alert-circle" class="w-24 h-24 mx-auto text-gray-400 dark:text-gray-600 mb-4"></i>
            <h2 class="text-6xl font-extrabold text-gray-900 dark:text-white mb-4">404</h2>
            <h3 class="text-2xl font-bold text-gray-700 dark:text-gray-300 mb-2">Page Not Found</h3>
            <p class="text-gray-500 dark:text-gray-400">
                The page you're looking for doesn't exist or has been moved.
            </p>
        </div>

        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="<?= View::url('/') ?>"
               class="inline-flex items-center justify-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-md text-sm font-medium transition-colors">
                <i data-lucide="home" class="w-4 h-4 mr-2"></i>
                Go to Dashboard
            </a>
            <?php if (Auth::isAdmin()): ?>
            <a href="<?= View::url('/settings') ?>"
               class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                <i data-lucide="settings" class="w-4 h-4 mr-2"></i>
                Settings
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>
