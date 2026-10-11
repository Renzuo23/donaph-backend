<?php
ini_set('display_errors', 0);
error_reporting(0);

$host = "mysql-be91e57-donaph.g.aivencloud.com";
$port = "17533"; // Updated port from your Aiven dashboard
$username = "avnadmin";
$password = getenv('DB_PASSWORD');
$dbname = "defaultdb";

$dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::MYSQL_ATTR_SSL_CA => __DIR__ . '/ca.pem',
];

try {
    $conn = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    if (!headers_sent()) {
        header('Content-Type: application/json');
    }
    echo json_encode(["success" => false, "message" => "Database Connection Failed: " . $e->getMessage()]);
    exit();
}
?>
