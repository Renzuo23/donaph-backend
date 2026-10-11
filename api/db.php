<?php
$host = "mysql-be91e57-donaph.g.aivencloud.com";
$port = "17537";
$username = "avnadmin";
$password = getenv('DB_PASSWORD');
$dbname = "defaultdb";

$conn = mysqli_init();

// Disable local certificate verification checks for cloud-hosted managed databases like Aiven
mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);
mysqli_options($conn, MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);

if (!mysqli_real_connect($conn, $host, $username, $password, $dbname, $port, NULL, MYSQLI_CLIENT_SSL)) {
    header('Content-Type: application/json');
    echo json_encode(["success" => false, "message" => "Database Connection Failed: " . mysqli_connect_error()]);
    exit();
}
?>
