<?php
$host = "mysql-be91e57-donaph.g.aivencloud.com";
$port = "17537";
$username = "avnadmin";
$password = getenv('DB_PASSWORD');
$dbname = "defaultdb";

$conn = mysqli_init();

// Required for Aiven MySQL SSL connection
mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);

// Establish connection with timeout
if (!mysqli_real_connect($conn, $host, $username, $password, $dbname, $port, NULL, MYSQLI_CLIENT_SSL)) {
    header('Content-Type: application/json');
    echo json_encode(["success" => false, "message" => "Database Connection Failed: " . mysqli_connect_error()]);
    exit();
}
?>
