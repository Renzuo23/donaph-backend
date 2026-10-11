<?php
$rawHost = trim($_ENV['MYSQLHOST'] ?? getenv('MYSQLHOST') ?? '');
// Force strict IPv4 resolution to prevent serverless DNS/routing dropouts
$host = gethostbyname($rawHost); 
$port = trim($_ENV['MYSQLPORT'] ?? getenv('MYSQLPORT') ?? '17537');
$db   = trim($_ENV['MYSQLDATABASE'] ?? getenv('MYSQLDATABASE') ?? 'defaultdb');
$user = trim($_ENV['MYSQLUSER'] ?? getenv('MYSQLUSER') ?? '');
$pass = trim($_ENV['MYSQLPASSWORD'] ?? getenv('MYSQLPASSWORD') ?? '');

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";

    $caPath = __DIR__ . '/ca.pem';
    if (!file_exists($caPath)) {
        throw new Exception("Certificate file not found at: " . $caPath);
    }

    $sslOption = defined('Pdo\Mysql::ATTR_SSL_CA') 
        ? \Pdo\Mysql::ATTR_SSL_CA 
        : PDO::MYSQL_ATTR_SSL_CA;

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 15,
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
        "message" => "Database Connection Error: " . $e->getMessage(),
        "resolved_ipv4" => $host
    ]);
    exit;
}
?>
