<?php
$host = $_ENV['MYSQLHOST'] ?? getenv('MYSQLHOST');
$port = $_ENV['MYSQLPORT'] ?? getenv('MYSQLPORT');
$db   = $_ENV['MYSQLDATABASE'] ?? getenv('MYSQLDATABASE');
$user = $_ENV['MYSQLUSER'] ?? getenv('MYSQLUSER');
$pass = $_ENV['MYSQLPASSWORD'] ?? getenv('MYSQLPASSWORD');

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
    
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];

    // Check if ca.pem exists locally before applying SSL constant
    $caPath = __DIR__ . '/ca.pem';
    if (file_exists($caPath)) {
        // Use modern PHP 8.5+ constant if available, fallback to legacy
        $sslAttr = defined('Pdo\Mysql::ATTR_SSL_CA') ? \Pdo\Mysql::ATTR_SSL_CA : PDO::MYSQL_ATTR_SSL_CA;
        $options[$sslAttr] = $caPath;
    }

    $conn = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    if (!headers_sent()) {
        header('Content-Type: application/json');
        http_response_code(500);
    }
    echo json_encode(["success" => false, "message" => "Connection failed: " . $e->getMessage()]);
    exit;
}
?>
