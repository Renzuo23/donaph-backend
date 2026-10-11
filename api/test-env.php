<?php
header('Content-Type: application/json');

$db_password = getenv('DB_PASSWORD');
$ca_path = __DIR__ . '/ca.pem';

$response = [
    "success" => true,
    "environment_variable_set" => !empty($db_password),
    "password_length" => $db_password ? strlen($db_password) : 0,
    "ca_pem_exists" => file_exists($ca_path),
    "ca_pem_readable" => is_readable($ca_path),
    "ca_pem_absolute_path" => $ca_path
];

echo json_encode($response, JSON_PRETTY_PRINT);
?>
