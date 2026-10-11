<?php
$host = trim($_ENV['MYSQLHOST'] ?? getenv('MYSQLHOST') ?? '');
$port = trim($_ENV['MYSQLPORT'] ?? getenv('MYSQLPORT') ?? '17537');
$db   = trim($_ENV['MYSQLDATABASE'] ?? getenv('MYSQLDATABASE') ?? 'defaultdb');
$user = trim($_ENV['MYSQLUSER'] ?? getenv('MYSQLUSER') ?? '');
$pass = trim($_ENV['MYSQLPASSWORD'] ?? getenv('MYSQLPASSWORD') ?? '');

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";

    // Path to your certificate inside the api directory
    $caPath = __DIR__ . '/ca.pem'; // Rename to match your exact file name if different

    if (!file_exists($caPath)) {
        throw new Exception("Certificate file not found at: " . $caPath);
    }

    // Support PHP 8.5+ constant without throwing deprecation warnings
    $sslOption = defined('Pdo\Mysql::ATTR_SSL_CA') 
        ? \Pdo\Mysql::ATTR_SSL_CA 
        : PDO::MYSQL_ATTR_SSL_CA;

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 10,
        $sslOption => $caPath,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
    ];

    $conn = new PDO($dsn, $user, $pass, $options);

} catch (Exception $e) {
    if (!headers_sent()) {
        header('Content-Type: application/json');
        http_response_code(500);
    }
    echo json_encode([
        "success" => false,
        "message" => "Database Connection Error: " . $e->getMessage()
    ]);
    exit;
}
?>
