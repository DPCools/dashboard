<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <div class="flex justify-center">
                <i data-lucide="lock" class="w-16 h-16 text-blue-600 dark:text-blue-400"></i>
            </div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900 dark:text-white">
                <?php if ($action === 'admin'): ?>
                    Admin Login
                <?php elseif ($page !== null): ?>
                    Unlock Page
                <?php else: ?>
                    Login Required
                <?php endif; ?>
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600 dark:text-gray-400">
                <?php if ($action === 'admin'): ?>
                    Enter the admin password to access settings
                <?php elseif ($page !== null): ?>
                    Enter the password to access "<?= Security::escape($page['name']) ?>"
                <?php else: ?>
                    Authentication is required to continue
                <?php endif; ?>
            </p>
        </div>

        <form class="mt-8 space-y-6" method="POST" action="<?= View::url('/login.php') ?>">
            <input type="hidden" name="csrf_token" value="<?= Security::getCSRFToken() ?>">
            <?php if ($action === 'admin'): ?>
                <input type="hidden" name="action" value="admin">
            <?php endif; ?>
            <?php if ($page !== null): ?>
                <input type="hidden" name="page" value="<?= Security::escape($page['slug']) ?>">
            <?php endif; ?>

            <div class="rounded-md shadow-sm -space-y-px">
                <div>
                    <label for="password" class="sr-only">Password</label>
                    <input id="password"
                           name="password"
                           type="password"
                           required
                           autofocus
                           class="appearance-none rounded-md relative block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 placeholder-gray-500 dark:placeholder-gray-400 text-gray-900 dark:text-white bg-white dark:bg-gray-700 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                           placeholder="Enter password">
                </div>
            </div>

            <div>
                <button type="submit"
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <i data-lucide="lock" class="h-5 w-5 text-blue-500 group-hover:text-blue-400"></i>
                    </span>
                    Sign in
                </button>
            </div>

            <div class="text-center">
                <a href="<?= View::url('/') ?>"
                   class="text-sm text-blue-600 dark:text-blue-400 hover:underline">
                    Return to Dashboard
                </a>
            </div>
        </form>
    </div>
</div>
