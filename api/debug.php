<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = trim($_ENV['MYSQLHOST'] ?? getenv('MYSQLHOST') ?? '');
$port = trim($_ENV['MYSQLPORT'] ?? getenv('MYSQLPORT') ?? '17537');
$db   = trim($_ENV['MYSQLDATABASE'] ?? getenv('MYSQLDATABASE') ?? 'defaultdb');
$user = trim($_ENV['MYSQLUSER'] ?? getenv('MYSQLUSER') ?? '');
$pass = trim($_ENV['MYSQLPASSWORD'] ?? getenv('MYSQLPASSWORD') ?? '');

echo "Connecting to Host: $host, Port: $port, DB: $db, User: $user<br>";

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
    $caPath = __DIR__ . '/ca.pem';

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 10,
    ];

    if (file_exists($caPath)) {
        $sslAttr = defined('Pdo\Mysql::ATTR_SSL_CA') ? \Pdo\Mysql::ATTR_SSL_CA : PDO::MYSQL_ATTR_SSL_CA;
        $options[$sslAttr] = $caPath;
        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
        echo "SSL certificate found and applied.<br>";
    } else {
        echo "WARNING: ca.pem not found in " . __DIR__ . "<br>";
    }

    $conn = new PDO($dsn, $user, $pass, $options);
    echo "SUCCESS: Connected to database successfully!";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
?>
