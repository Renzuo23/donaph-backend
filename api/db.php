<?php
$host = trim($_ENV['MYSQLHOST'] ?? getenv('MYSQLHOST') ?? '');
$port = trim($_ENV['MYSQLPORT'] ?? getenv('MYSQLPORT') ?? '3306');
$db   = trim($_ENV['MYSQLDATABASE'] ?? getenv('MYSQLDATABASE') ?? '');
$user = trim($_ENV['MYSQLUSER'] ?? getenv('MYSQLUSER') ?? '');
$pass = trim($_ENV['MYSQLPASSWORD'] ?? getenv('MYSQLPASSWORD') ?? '');

try {
    // Added explicit SSL disable parameter for cloud serverless testing
    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4;sslmode=REQUIRED";
    
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 5, // Fail fast after 5 seconds instead of hanging
    ];

    $conn = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // Fallback: try without sslmode if required fails
    try {
        $dsnFallback = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
        $conn = new PDO($dsnFallback, $user, $pass, $options);
    } catch (PDOException $e2) {
        if (!headers_sent()) {
            header('Content-Type: application/json');
            http_response_code(500);
        }
        echo json_encode([
            "success" => false, 
            "message" => "Connection failed: " . $e2->getMessage(),
            "debug_host" => $host,
            "debug_port" => $port,
            "debug_db" => $db
        ]);
        exit;
    }
}
?>
