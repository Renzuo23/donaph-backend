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
        PDO::MYSQL_ATTR_SSL_CA => __DIR__ . '/ca.pem', // Essential for Aiven SSL
    ];
    $conn = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Connection failed: " . $e->getMessage()]);
    exit;
}
?>
