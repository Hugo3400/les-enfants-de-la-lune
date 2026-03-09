<?php
declare(strict_types=1);

return [
    'db_driver' => getenv('DB_DRIVER') ?: 'sqlite',
    'db_host' => getenv('DB_HOST') ?: '',
    'db_port' => getenv('DB_PORT') ?: '',
    'db_name' => getenv('DB_NAME') ?: '',
    'db_user' => getenv('DB_USER') ?: '',
    'db_pass' => getenv('DB_PASS') ?: '',
    'db_charset' => getenv('DB_CHARSET') ?: '',
    'db_dsn' => getenv('DB_DSN') ?: ('sqlite:' . BASE_PATH . '/storage/database.sqlite'),
    'db_auto_migrate' => in_array(strtolower((string) (getenv('DB_AUTO_MIGRATE') ?: '1')), ['1', 'true', 'yes', 'on'], true),
    'admin_seed_email' => getenv('ADMIN_SEED_EMAIL') ?: '',
    'admin_seed_password' => getenv('ADMIN_SEED_PASSWORD') ?: '',
    'site_name' => getenv('SITE_NAME') ?: 'Les Enfants de la Lune',
];
