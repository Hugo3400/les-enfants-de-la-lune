<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

final class Database
{
    private static ?PDO $pdo = null;

    public static function connection(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $config = require BASE_PATH . '/config/app.php';
        $dsn = self::buildDsn($config);
        $username = self::usernameForDsn($config);
        $password = self::passwordForDsn($config);

        self::$pdo = new PDO($dsn, $username, $password);
        self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        $autoMigrate = (bool) ($config['db_auto_migrate'] ?? true);
        if ($autoMigrate) {
            self::migrate(self::$pdo, $config);
        }

        return self::$pdo;
    }

    private static function buildDsn(array $config): string
    {
        $explicitDsn = trim((string) ($config['db_dsn'] ?? ''));
        if ($explicitDsn !== '') {
            return $explicitDsn;
        }

        $driver = (string) ($config['db_driver'] ?? 'sqlite');

        if ($driver === 'mysql' || $driver === 'mariadb') {
            $host = trim((string) ($config['db_host'] ?? ''));
            $port = trim((string) ($config['db_port'] ?? ''));
            $name = (string) ($config['db_name'] ?? '');
            $charset = trim((string) ($config['db_charset'] ?? ''));

            if ($host === '' || $port === '' || $name === '' || $charset === '') {
                throw new \RuntimeException('Configuration DB incomplète. Définissez DB_HOST, DB_PORT, DB_NAME et DB_CHARSET ou DB_DSN.');
            }

            return sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', $host, $port, $name, $charset);
        }

        throw new \RuntimeException('Configuration DB manquante. Définissez DB_DSN ou DB_DRIVER=mysql/mariadb avec les variables associées.');
    }

    private static function usernameForDsn(array $config): ?string
    {
        $driver = (string) ($config['db_driver'] ?? 'sqlite');
        if ($driver === 'mysql' || $driver === 'mariadb') {
            return (string) ($config['db_user'] ?? '');
        }

        return null;
    }

    private static function passwordForDsn(array $config): ?string
    {
        $driver = (string) ($config['db_driver'] ?? 'sqlite');
        if ($driver === 'mysql' || $driver === 'mariadb') {
            return (string) ($config['db_pass'] ?? '');
        }

        return null;
    }

    private static function migrate(PDO $pdo, array $config): void
    {
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

        if ($driver === 'mysql') {
            $pdo->exec(
                'CREATE TABLE IF NOT EXISTS users (
                    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(120) NOT NULL,
                    email VARCHAR(191) NOT NULL UNIQUE,
                    password_hash VARCHAR(255) NOT NULL,
                    role VARCHAR(30) NOT NULL DEFAULT \'admin\',
                    is_active TINYINT(1) NOT NULL DEFAULT 1,
                    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
            );

            $pdo->exec(
                'CREATE TABLE IF NOT EXISTS members (
                    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    first_name VARCHAR(120) NOT NULL,
                    last_name VARCHAR(120) NOT NULL,
                    email VARCHAR(191) NULL,
                    phone VARCHAR(30) NULL,
                    role VARCHAR(40) NOT NULL DEFAULT \'membre\',
                    status VARCHAR(20) NOT NULL DEFAULT \'active\',
                    user_id INT UNSIGNED NULL,
                    joined_at DATE NULL,
                    notes TEXT NULL,
                    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
            );

            $pdo->exec(
                'CREATE TABLE IF NOT EXISTS posts (
                    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    title VARCHAR(191) NOT NULL,
                    slug VARCHAR(191) NOT NULL UNIQUE,
                    excerpt TEXT NULL,
                    content LONGTEXT NOT NULL,
                    theme VARCHAR(40) NOT NULL DEFAULT "general",
                    is_published TINYINT(1) NOT NULL DEFAULT 1,
                    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
            );

            $pdo->exec(
                'CREATE TABLE IF NOT EXISTS contact_messages (
                    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(120) NOT NULL,
                    email VARCHAR(191) NOT NULL,
                    subject VARCHAR(191) NOT NULL,
                    category VARCHAR(60) NOT NULL DEFAULT \'general\',
                    message TEXT NOT NULL,
                    is_read TINYINT(1) NOT NULL DEFAULT 0,
                    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
            );

            $pdo->exec(
                'CREATE TABLE IF NOT EXISTS rentals (
                    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    title VARCHAR(191) NOT NULL,
                    location_label VARCHAR(191) NOT NULL,
                    price DECIMAL(10,2) NOT NULL DEFAULT 0,
                    status VARCHAR(30) NOT NULL DEFAULT "available",
                    description TEXT NULL,
                    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_rentals_status (status)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
            );

            $pdo->exec(
                'CREATE TABLE IF NOT EXISTS accounting_entries (
                    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    entry_type VARCHAR(20) NOT NULL,
                    label VARCHAR(191) NOT NULL,
                    amount DECIMAL(10,2) NOT NULL,
                    entry_date DATE NOT NULL,
                    notes TEXT NULL,
                    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_accounting_type (entry_type),
                    INDEX idx_accounting_date (entry_date)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
            );

            $pdo->exec(
                'CREATE TABLE IF NOT EXISTS accounting_accounts (
                    code VARCHAR(20) PRIMARY KEY,
                    label VARCHAR(191) NOT NULL,
                    account_type VARCHAR(20) NOT NULL,
                    is_active TINYINT(1) NOT NULL DEFAULT 1,
                    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_accounting_accounts_type (account_type)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
            );

            $pdo->exec(
                'CREATE TABLE IF NOT EXISTS events (
                    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    title VARCHAR(191) NOT NULL,
                    description VARCHAR(255) NULL,
                    event_date DATE NULL,
                    event_time VARCHAR(10) NULL,
                    registration_url VARCHAR(255) NULL,
                    is_visible TINYINT(1) NOT NULL DEFAULT 1,
                    sort_order INT NOT NULL DEFAULT 0,
                    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_events_visible (is_visible),
                    INDEX idx_events_sort (sort_order)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
            );

            $pdo->exec(
                'CREATE TABLE IF NOT EXISTS member_rentals (
                    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    member_id INT UNSIGNED NOT NULL,
                    rental_id INT UNSIGNED NOT NULL,
                    assigned_at DATE NOT NULL,
                    released_at DATE NULL,
                    lease_duration_value INT UNSIGNED NULL,
                    lease_duration_unit VARCHAR(10) NULL,
                    status VARCHAR(20) NOT NULL DEFAULT "active",
                    notes TEXT NULL,
                    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_mr_member (member_id),
                    INDEX idx_mr_rental (rental_id),
                    INDEX idx_mr_status (status)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
            );
        } else {
            $pdo->exec(
                'CREATE TABLE IF NOT EXISTS users (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name TEXT NOT NULL,
                    email TEXT NOT NULL UNIQUE,
                    password_hash TEXT NOT NULL,
                    role TEXT NOT NULL DEFAULT \'admin\',
                    is_active INTEGER NOT NULL DEFAULT 1,
                    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
                )'
            );

            $pdo->exec(
                'CREATE TABLE IF NOT EXISTS members (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    first_name TEXT NOT NULL,
                    last_name TEXT NOT NULL,
                    email TEXT,
                    phone TEXT,
                    role TEXT NOT NULL DEFAULT \'membre\',
                    status TEXT NOT NULL DEFAULT \'active\',
                    user_id INTEGER,
                    joined_at TEXT,
                    notes TEXT,
                    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
                )'
            );

            $pdo->exec(
                'CREATE TABLE IF NOT EXISTS posts (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    title TEXT NOT NULL,
                    slug TEXT NOT NULL UNIQUE,
                    excerpt TEXT,
                    content TEXT NOT NULL,
                    theme TEXT NOT NULL DEFAULT "general",
                    is_published INTEGER NOT NULL DEFAULT 1,
                    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
                )'
            );

            $pdo->exec(
                'CREATE TABLE IF NOT EXISTS contact_messages (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name TEXT NOT NULL,
                    email TEXT NOT NULL,
                    subject TEXT NOT NULL,
                    category TEXT NOT NULL DEFAULT \'general\',
                    message TEXT NOT NULL,
                    is_read INTEGER NOT NULL DEFAULT 0,
                    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
                )'
            );

            $pdo->exec(
                'CREATE TABLE IF NOT EXISTS rentals (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    title TEXT NOT NULL,
                    location_label TEXT NOT NULL,
                    price REAL NOT NULL DEFAULT 0,
                    status TEXT NOT NULL DEFAULT "available",
                    description TEXT,
                    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
                )'
            );

            $pdo->exec(
                'CREATE TABLE IF NOT EXISTS accounting_entries (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    entry_type TEXT NOT NULL,
                    label TEXT NOT NULL,
                    amount REAL NOT NULL,
                    entry_date TEXT NOT NULL,
                    notes TEXT,
                    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
                )'
            );

            $pdo->exec(
                'CREATE TABLE IF NOT EXISTS accounting_accounts (
                    code TEXT PRIMARY KEY,
                    label TEXT NOT NULL,
                    account_type TEXT NOT NULL,
                    is_active INTEGER NOT NULL DEFAULT 1,
                    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
                )'
            );

            $pdo->exec(
                'CREATE TABLE IF NOT EXISTS events (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    title TEXT NOT NULL,
                    description TEXT,
                    event_date TEXT,
                    event_time TEXT,
                    registration_url TEXT,
                    is_visible INTEGER NOT NULL DEFAULT 1,
                    sort_order INTEGER NOT NULL DEFAULT 0,
                    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
                )'
            );

            $pdo->exec(
                'CREATE TABLE IF NOT EXISTS member_rentals (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    member_id INTEGER NOT NULL,
                    rental_id INTEGER NOT NULL,
                    assigned_at TEXT NOT NULL,
                    released_at TEXT,
                    lease_duration_value INTEGER,
                    lease_duration_unit TEXT,
                    status TEXT NOT NULL DEFAULT "active",
                    notes TEXT,
                    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
                )'
            );
        }

        self::ensureAccountingColumns($pdo, $driver);
        self::ensureContactColumns($pdo, $driver);
        self::ensurePostColumns($pdo, $driver);
        self::ensureEventColumns($pdo, $driver);
        self::ensureUserColumns($pdo, $driver);
        self::ensureMemberRentalColumns($pdo, $driver);
        self::seedAccountingAccounts($pdo, $driver);

        $count = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
        if ($count === 0) {
            $email = trim((string) ($config['admin_seed_email'] ?? ''));
            $password = (string) ($config['admin_seed_password'] ?? '');

            if ($email === '' || $password === '') {
                return;
            }

            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash) VALUES (:name, :email, :password_hash)');
            $stmt->execute([
                ':name' => 'Administrateur',
                ':email' => $email,
                ':password_hash' => $hash,
            ]);
        }
    }

    private static function ensureAccountingColumns(PDO $pdo, string $driver): void
    {
        if ($driver === 'mysql') {
            self::tryExec($pdo, 'ALTER TABLE accounting_entries ADD COLUMN account_code VARCHAR(20) NOT NULL DEFAULT "758"');
            self::tryExec($pdo, 'ALTER TABLE accounting_entries ADD COLUMN payment_method VARCHAR(30) NOT NULL DEFAULT "transfer"');
            self::tryExec($pdo, 'ALTER TABLE accounting_entries ADD COLUMN reference VARCHAR(80) NULL');
            self::tryExec($pdo, 'ALTER TABLE accounting_entries ADD COLUMN entry_status VARCHAR(20) NOT NULL DEFAULT "draft"');
            self::tryExec($pdo, 'ALTER TABLE accounting_entries ADD COLUMN validated_at DATETIME NULL');
            self::tryExec($pdo, 'CREATE INDEX idx_accounting_account_code ON accounting_entries (account_code)');
            self::tryExec($pdo, 'CREATE INDEX idx_accounting_payment_method ON accounting_entries (payment_method)');
            self::tryExec($pdo, 'CREATE INDEX idx_accounting_entry_status ON accounting_entries (entry_status)');
            return;
        }

        self::tryExec($pdo, 'ALTER TABLE accounting_entries ADD COLUMN account_code TEXT NOT NULL DEFAULT "758"');
        self::tryExec($pdo, 'ALTER TABLE accounting_entries ADD COLUMN payment_method TEXT NOT NULL DEFAULT "transfer"');
        self::tryExec($pdo, 'ALTER TABLE accounting_entries ADD COLUMN reference TEXT');
        self::tryExec($pdo, 'ALTER TABLE accounting_entries ADD COLUMN entry_status TEXT NOT NULL DEFAULT "draft"');
        self::tryExec($pdo, 'ALTER TABLE accounting_entries ADD COLUMN validated_at TEXT');
        self::tryExec($pdo, 'CREATE INDEX IF NOT EXISTS idx_accounting_account_code ON accounting_entries (account_code)');
        self::tryExec($pdo, 'CREATE INDEX IF NOT EXISTS idx_accounting_payment_method ON accounting_entries (payment_method)');
        self::tryExec($pdo, 'CREATE INDEX IF NOT EXISTS idx_accounting_entry_status ON accounting_entries (entry_status)');
    }

    private static function ensureContactColumns(PDO $pdo, string $driver): void
    {
        if ($driver === 'mysql') {
            self::tryExec($pdo, 'ALTER TABLE contact_messages ADD COLUMN category VARCHAR(60) NOT NULL DEFAULT "general"');
            self::tryExec($pdo, 'ALTER TABLE contact_messages ADD COLUMN is_read TINYINT(1) NOT NULL DEFAULT 0');
            return;
        }

        self::tryExec($pdo, 'ALTER TABLE contact_messages ADD COLUMN category TEXT NOT NULL DEFAULT "general"');
        self::tryExec($pdo, 'ALTER TABLE contact_messages ADD COLUMN is_read INTEGER NOT NULL DEFAULT 0');
    }

    private static function ensureUserColumns(PDO $pdo, string $driver): void
    {
        if ($driver === 'mysql') {
            self::tryExec($pdo, 'ALTER TABLE users ADD COLUMN role VARCHAR(30) NOT NULL DEFAULT "admin"');
            self::tryExec($pdo, 'ALTER TABLE users ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1');
            return;
        }

        self::tryExec($pdo, 'ALTER TABLE users ADD COLUMN role TEXT NOT NULL DEFAULT "admin"');
        self::tryExec($pdo, 'ALTER TABLE users ADD COLUMN is_active INTEGER NOT NULL DEFAULT 1');
    }

    private static function ensurePostColumns(PDO $pdo, string $driver): void
    {
        if ($driver === 'mysql') {
            self::tryExec($pdo, 'ALTER TABLE posts ADD COLUMN theme VARCHAR(40) NOT NULL DEFAULT "general"');
            self::tryExec($pdo, 'CREATE INDEX idx_posts_theme ON posts (theme)');
            return;
        }

        self::tryExec($pdo, 'ALTER TABLE posts ADD COLUMN theme TEXT NOT NULL DEFAULT "general"');
        self::tryExec($pdo, 'CREATE INDEX IF NOT EXISTS idx_posts_theme ON posts (theme)');
    }

    private static function ensureMemberRentalColumns(PDO $pdo, string $driver): void
    {
        if ($driver === 'mysql') {
            self::tryExec($pdo, 'ALTER TABLE member_rentals ADD COLUMN lease_duration_value INT UNSIGNED NULL');
            self::tryExec($pdo, 'ALTER TABLE member_rentals ADD COLUMN lease_duration_unit VARCHAR(10) NULL');
            return;
        }

        self::tryExec($pdo, 'ALTER TABLE member_rentals ADD COLUMN lease_duration_value INTEGER');
        self::tryExec($pdo, 'ALTER TABLE member_rentals ADD COLUMN lease_duration_unit TEXT');
    }

    private static function ensureEventColumns(PDO $pdo, string $driver): void
    {
        if ($driver === 'mysql') {
            self::tryExec($pdo, 'ALTER TABLE events ADD COLUMN registration_url VARCHAR(255) NULL');
            return;
        }

        self::tryExec($pdo, 'ALTER TABLE events ADD COLUMN registration_url TEXT');
    }

    private static function seedAccountingAccounts(PDO $pdo, string $driver): void
    {
        $accounts = [
            ['512', 'Banque', 'asset'],
            ['530', 'Caisse', 'asset'],
            ['706', 'Prestations / locations', 'income'],
            ['707', 'Ventes diverses', 'income'],
            ['708', 'Produits annexes', 'income'],
            ['754', 'Subventions', 'income'],
            ['756', 'Cotisations', 'income'],
            ['758', 'Dons / autres produits', 'income'],
            ['606', 'Achats non stockés', 'expense'],
            ['613', 'Locations', 'expense'],
            ['615', 'Entretien / réparations', 'expense'],
            ['622', 'Honoraires', 'expense'],
            ['623', 'Communication', 'expense'],
            ['625', 'Déplacements', 'expense'],
            ['626', 'Frais postaux / télécom', 'expense'],
            ['627', 'Services bancaires', 'expense'],
        ];

        foreach ($accounts as [$code, $label, $type]) {
            if ($driver === 'mysql') {
                $stmt = $pdo->prepare(
                    'INSERT IGNORE INTO accounting_accounts (code, label, account_type, is_active)
                     VALUES (:code, :label, :account_type, 1)'
                );
            } else {
                $stmt = $pdo->prepare(
                    'INSERT OR IGNORE INTO accounting_accounts (code, label, account_type, is_active)
                     VALUES (:code, :label, :account_type, 1)'
                );
            }

            $stmt->execute([
                ':code' => $code,
                ':label' => $label,
                ':account_type' => $type,
            ]);
        }
    }

    private static function tryExec(PDO $pdo, string $sql): void
    {
        try {
            $pdo->exec($sql);
        } catch (\Throwable $exception) {
        }
    }
}
