<?php
// Copy config.example.php to config.local.php for local credentials, or use env vars.
$config = is_file(__DIR__ . '/config.local.php') ? require __DIR__ . '/config.local.php' : [];
$dbHost = $config['host'] ?? (getenv('NETFISH_DB_HOST') ?: '127.0.0.1');
$dbName = $config['database'] ?? (getenv('NETFISH_DB_NAME') ?: 'netfish');
$dbUser = $config['username'] ?? (getenv('NETFISH_DB_USER') ?: 'root');
$dbPassword = $config['password'] ?? (getenv('NETFISH_DB_PASSWORD') ?: '');
try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPassword, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    error_log('NETFISH database connection failed.');
    http_response_code(503);
    exit('Database tijdelijk niet beschikbaar.');
}
