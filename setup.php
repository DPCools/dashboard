<?php

declare(strict_types=1);

/**
 * HomeDash Setup Script
 *
 * This script runs on first installation to:
 * - Create the database
 * - Load schema and seed data
 * - Generate a random admin password
 * - Display the password to the user
 */

// Start session
session_start();

// Load configuration
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/version.php';

// Check if database already exists
$dbPath = __DIR__ . '/data/dashboard.db';
$dbExists = file_exists($dbPath);

// If database exists and we're not forcing reinstall, redirect to dashboard
if ($dbExists && !isset($_GET['force'])) {
    header('Location: ' . BASE_URL . '/');
    exit;
}

// Handle setup submission
$setupComplete = false;
$generatedPassword = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_setup'])) {
    try {
        // Generate random admin password (16 characters, alphanumeric + symbols)
        $generatedPassword = bin2hex(random_bytes(8)) . '!' . bin2hex(random_bytes(4));

        // Delete existing database if forcing reinstall
        if ($dbExists) {
            unlink($dbPath);
        }

        // Create data directory if it doesn't exist
        $dataDir = __DIR__ . '/data';
        if (!is_dir($dataDir)) {
            mkdir($dataDir, 0775, true);
        }

        // Create database connection
        $db = new PDO('sqlite:' . $dbPath);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Load and execute schema
        $schemaPath = __DIR__ . '/database/schema.sql';
        if (!file_exists($schemaPath)) {
            throw new Exception('Schema file not found: ' . $schemaPath);
        }

        $schema = file_get_contents($schemaPath);
        $db->exec($schema);

        // Load and execute seeds
        $seedsPath = __DIR__ . '/database/seeds.sql';
        if (!file_exists($seedsPath)) {
            throw new Exception('Seeds file not found: ' . $seedsPath);
        }

        $seeds = file_get_contents($seedsPath);
        $db->exec($seeds);

        // Update admin password with generated one
        $passwordHash = password_hash($generatedPassword, PASSWORD_ARGON2ID);
        $stmt = $db->prepare('UPDATE settings SET value = :hash WHERE key = :key');
        $stmt->execute([
            ':hash' => $passwordHash,
            ':key' => 'admin_password_hash'
        ]);

        // Store current version in database
        $stmt = $db->prepare('INSERT OR REPLACE INTO settings (key, value) VALUES (:key, :value)');
        $stmt->execute([':key' => 'app_version', ':value' => APP_VERSION]);
        $stmt->execute([':key' => 'db_version', ':value' => (string) DB_VERSION]);

        // Run all migrations
        $migrationsDir = __DIR__ . '/database/migrations';
        if (is_dir($migrationsDir)) {
            $migrations = glob($migrationsDir . '/*.sql');
            sort($migrations);

            foreach ($migrations as $migrationFile) {
                $migrationSql = file_get_contents($migrationFile);
                if (!empty($migrationSql)) {
                    $db->exec($migrationSql);
                }
            }
        }

        // Set permissions
        chmod($dbPath, 0664);

        $setupComplete = true;

    } catch (Exception $e) {
        $error = 'Setup failed: ' . $e->getMessage();
        // Clean up partial database
        if (file_exists($dbPath)) {
            unlink($dbPath);
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HomeDash Setup</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        dark: {
                            bg: '#0f172a',
                            surface: '#1e293b',
                            border: '#334155'
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 dark:bg-dark-bg min-h-screen flex items-center justify-center p-4">
    <div class="max-w-2xl w-full">
        <div class="bg-white dark:bg-dark-surface rounded-lg shadow-xl border border-gray-200 dark:border-dark-border overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-600 to-purple-600 px-8 py-6">
                <h1 class="text-3xl font-bold text-white mb-2">
                    Welcome to HomeDash
                </h1>
                <p class="text-blue-100">
                    Let's set up your personal dashboard
                </p>
            </div>

            <div class="p-8">
                <?php if (!$setupComplete): ?>
                    <!-- Setup Form -->
                    <?php if ($error): ?>
                        <div class="mb-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-600 dark:text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-red-800 dark:text-red-200">Error</h3>
                                    <p class="mt-1 text-sm text-red-700 dark:text-red-300"><?= htmlspecialchars($error) ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="space-y-6">
                        <div>
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                                First Run Setup
                            </h2>
                            <p class="text-gray-600 dark:text-gray-400">
                                This setup will:
                            </p>
                            <ul class="mt-3 space-y-2 text-gray-600 dark:text-gray-400">
                                <li class="flex items-start">
                                    <svg class="h-5 w-5 text-green-500 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Create a new SQLite database
                                </li>
                                <li class="flex items-start">
                                    <svg class="h-5 w-5 text-green-500 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Load sample dashboard with example services
                                </li>
                                <li class="flex items-start">
                                    <svg class="h-5 w-5 text-green-500 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Generate a secure random admin password
                                </li>
                                <li class="flex items-start">
                                    <svg class="h-5 w-5 text-green-500 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Apply all database migrations
                                </li>
                            </ul>
                        </div>

                        <?php if ($dbExists): ?>
                            <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                                <div class="flex items-start">
                                    <svg class="h-5 w-5 text-yellow-600 dark:text-yellow-400 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                    <div>
                                        <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Warning</h3>
                                        <p class="mt-1 text-sm text-yellow-700 dark:text-yellow-300">
                                            A database already exists. Continuing will delete all existing data.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <form method="POST" class="space-y-4">
                            <button type="submit" name="confirm_setup" value="1"
                                    class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all">
                                <?= $dbExists ? 'Reset Database & Continue Setup' : 'Begin Setup' ?>
                            </button>

                            <?php if ($dbExists): ?>
                                <a href="<?= BASE_URL ?>/"
                                   class="w-full flex justify-center py-3 px-4 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-dark-surface hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                    Cancel - Go to Dashboard
                                </a>
                            <?php endif; ?>
                        </form>
                    </div>

                <?php else: ?>
                    <!-- Setup Complete -->
                    <div class="space-y-6">
                        <div class="text-center">
                            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 dark:bg-green-900/30 mb-4">
                                <svg class="h-8 w-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                                Setup Complete!
                            </h2>
                            <p class="text-gray-600 dark:text-gray-400">
                                Your dashboard has been configured successfully.
                            </p>
                        </div>

                        <div class="bg-blue-50 dark:bg-blue-900/20 border-2 border-blue-200 dark:border-blue-800 rounded-lg p-6">
                            <div class="flex items-start mb-4">
                                <svg class="h-6 w-6 text-blue-600 dark:text-blue-400 mr-3 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-blue-900 dark:text-blue-200 mb-2">
                                        Your Admin Password
                                    </h3>
                                    <p class="text-sm text-blue-800 dark:text-blue-300 mb-3">
                                        Save this password now - it won't be shown again!
                                    </p>
                                    <div class="bg-white dark:bg-dark-surface border border-blue-300 dark:border-blue-700 rounded-md p-4">
                                        <div class="flex items-center justify-between">
                                            <code class="text-2xl font-mono font-bold text-blue-600 dark:text-blue-400 select-all" id="password">
                                                <?= htmlspecialchars($generatedPassword) ?>
                                            </code>
                                            <button type="button" onclick="copyPassword()"
                                                    class="ml-4 px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-md transition-colors">
                                                Copy
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-4">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">
                                What's Included:
                            </h3>
                            <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                                <li>• Sample dashboard page with example services</li>
                                <li>• 18 pre-configured service items across 4 categories</li>
                                <li>• Widget system ready to use (7 widget types available)</li>
                                <li>• Custom icon upload functionality</li>
                                <li>• Page and item privacy features</li>
                            </ul>
                        </div>

                        <div class="space-y-3">
                            <a href="<?= BASE_URL ?>/"
                               class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 transition-all">
                                Go to Dashboard
                            </a>
                            <a href="<?= BASE_URL ?>/settings"
                               class="w-full flex justify-center py-3 px-4 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-dark-surface hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                Go to Settings
                            </a>
                        </div>

                        <div class="text-center text-xs text-gray-500 dark:text-gray-400 pt-4 border-t border-gray-200 dark:border-gray-700">
                            HomeDash v<?= APP_VERSION ?> | Database v<?= DB_VERSION ?>
                        </div>
                    </div>

                    <script>
                        function copyPassword() {
                            const password = document.getElementById('password').textContent;
                            navigator.clipboard.writeText(password).then(() => {
                                const btn = event.target;
                                const originalText = btn.textContent;
                                btn.textContent = 'Copied!';
                                btn.classList.add('bg-green-600');
                                btn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
                                setTimeout(() => {
                                    btn.textContent = originalText;
                                    btn.classList.remove('bg-green-600');
                                    btn.classList.add('bg-blue-600', 'hover:bg-blue-700');
                                }, 2000);
                            });
                        }
                    </script>
                <?php endif; ?>
            </div>
        </div>

        <div class="mt-4 text-center text-sm text-gray-500 dark:text-gray-400">
            <a href="https://github.com/yourusername/homedash" target="_blank" class="hover:text-gray-700 dark:hover:text-gray-300">
                Documentation
            </a>
            <span class="mx-2">•</span>
            <a href="https://github.com/yourusername/homedash/issues" target="_blank" class="hover:text-gray-700 dark:hover:text-gray-300">
                Support
            </a>
        </div>
    </div>
</body>
</html>
