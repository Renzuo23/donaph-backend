<?php
$host = "mysql-be91e57-donaph.g.aivencloud.com";
$port = "17537";
$username = "avnadmin";
$password = getenv('DB_PASSWORD');
$dbname = "defaultdb";

$dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
];

try {
    // Create PDO instance forcing SSL connection behavior
    $conn = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(["success" => false, "message" => "Database Connection Failed: " . $e->getMessage()]);
    exit();
}
?>
