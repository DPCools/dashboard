<?php

declare(strict_types=1);

class Database
{
    private static ?PDO $instance = null;

    /**
     * Get the database PDO instance (singleton pattern)
     *
     * @return PDO The database connection
     */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            self::connect();
        }

        return self::$instance;
    }

    /**
     * Connect to the SQLite database and initialize if needed
     */
    private static function connect(): void
    {
        $databaseExists = file_exists(DATABASE_PATH);

        // Reason: Redirect to setup if database doesn't exist
        if (!$databaseExists) {
            // Check if we're not already on the setup page to avoid redirect loop
            $requestUri = $_SERVER['REQUEST_URI'] ?? '';
            if (strpos($requestUri, '/setup.php') === false && strpos($requestUri, '/setup') === false) {
                header('Location: ' . BASE_URL . '/setup.php');
                exit;
            }

            // If we're on setup page, create empty DB connection for setup process
            try {
                self::$instance = new PDO(
                    'sqlite:' . DATABASE_PATH,
                    null,
                    null,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]
                );
                self::$instance->exec('PRAGMA foreign_keys = ON');
            } catch (PDOException $e) {
                die('Database connection failed: ' . $e->getMessage());
            }
            return;
        }

        try {
            // Reason: Create SQLite database with proper error handling and foreign key support
            self::$instance = new PDO(
                'sqlite:' . DATABASE_PATH,
                null,
                null,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );

            // Reason: Enable foreign key constraints in SQLite
            self::$instance->exec('PRAGMA foreign_keys = ON');

            // Reason: Run any pending migrations after database is initialized
            self::runMigrations();
        } catch (PDOException $e) {
            die('Database connection failed: ' . $e->getMessage());
        }
    }

    /**
     * Initialize the database with schema and seed data
     */
    private static function initialize(): void
    {
        try {
            // Read and execute schema
            $schemaPath = BASE_PATH . '/database/schema.sql';
            if (file_exists($schemaPath)) {
                $schema = file_get_contents($schemaPath);
                if ($schema !== false) {
                    self::$instance->exec($schema);
                }
            }

            // Read and execute seeds
            $seedsPath = BASE_PATH . '/database/seeds.sql';
            if (file_exists($seedsPath)) {
                $seeds = file_get_contents($seedsPath);
                if ($seeds !== false) {
                    self::$instance->exec($seeds);
                }
            }
        } catch (PDOException $e) {
            die('Database initialization failed: ' . $e->getMessage());
        }
    }

    /**
     * Execute a query with parameters
     *
     * @param string $sql The SQL query
     * @param array $params The parameters to bind
     * @return PDOStatement The executed statement
     */
    public static function query(string $sql, array $params = []): PDOStatement
    {
        $db = self::getInstance();
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Get a single row from the database
     *
     * @param string $sql The SQL query
     * @param array $params The parameters to bind
     * @return array|null The row data or null if not found
     */
    public static function fetchOne(string $sql, array $params = []): ?array
    {
        $stmt = self::query($sql, $params);
        $result = $stmt->fetch();
        return $result !== false ? $result : null;
    }

    /**
     * Get all rows from the database
     *
     * @param string $sql The SQL query
     * @param array $params The parameters to bind
     * @return array The array of rows
     */
    public static function fetchAll(string $sql, array $params = []): array
    {
        $stmt = self::query($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Insert a row and return the last insert ID
     *
     * @param string $sql The SQL query
     * @param array $params The parameters to bind
     * @return int The last insert ID
     */
    public static function insert(string $sql, array $params = []): int
    {
        self::query($sql, $params);
        return (int) self::getInstance()->lastInsertId();
    }

    /**
     * Update rows and return the number of affected rows
     *
     * @param string $sql The SQL query
     * @param array $params The parameters to bind
     * @return int The number of affected rows
     */
    public static function update(string $sql, array $params = []): int
    {
        $stmt = self::query($sql, $params);
        return $stmt->rowCount();
    }

    /**
     * Delete rows and return the number of affected rows
     *
     * @param string $sql The SQL query
     * @param array $params The parameters to bind
     * @return int The number of affected rows
     */
    public static function delete(string $sql, array $params = []): int
    {
        $stmt = self::query($sql, $params);
        return $stmt->rowCount();
    }

    /**
     * Run pending database migrations
     *
     * @return void
     */
    private static function runMigrations(): void
    {
        try {
            // Reason: Create migrations table if it doesn't exist
            self::$instance->exec('
                CREATE TABLE IF NOT EXISTS migrations (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    migration TEXT NOT NULL UNIQUE,
                    executed_at TEXT NOT NULL DEFAULT (datetime(\'now\'))
                )
            ');

            $migrationsPath = BASE_PATH . '/database/migrations';

            if (!is_dir($migrationsPath)) {
                return; // No migrations directory
            }

            // Get list of migration files
            $files = glob($migrationsPath . '/*.sql');
            if ($files === false) {
                return;
            }

            sort($files); // Ensure migrations run in order

            $migrationsRun = 0;

            foreach ($files as $file) {
                $migrationName = basename($file);

                // Check if migration has already been run
                $stmt = self::$instance->prepare('SELECT id FROM migrations WHERE migration = ?');
                $stmt->execute([$migrationName]);

                if ($stmt->fetch() !== false) {
                    continue; // Migration already executed
                }

                // Execute migration
                $sql = file_get_contents($file);
                if ($sql !== false && trim($sql) !== '') {
                    self::$instance->exec($sql);

                    // Record migration execution
                    $stmt = self::$instance->prepare('INSERT INTO migrations (migration) VALUES (?)');
                    $stmt->execute([$migrationName]);

                    $migrationsRun++;
                }
            }

            // Update version information if migrations were run
            if ($migrationsRun > 0 && defined('APP_VERSION') && defined('DB_VERSION')) {
                $stmt = self::$instance->prepare('INSERT OR REPLACE INTO settings (key, value) VALUES (?, ?)');
                $stmt->execute(['app_version', APP_VERSION]);
                $stmt->execute(['db_version', (string) DB_VERSION]);
            }
        } catch (PDOException $e) {
            // Reason: Don't die on migration errors, log them instead
            error_log('Migration error: ' . $e->getMessage());
        }
    }

    /**
     * Begin a transaction
     */
    public static function beginTransaction(): void
    {
        self::getInstance()->beginTransaction();
    }

    /**
     * Commit a transaction
     */
    public static function commit(): void
    {
        self::getInstance()->commit();
    }

    /**
     * Rollback a transaction
     */
    public static function rollback(): void
    {
        self::getInstance()->rollBack();
    }
}
