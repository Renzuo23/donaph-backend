<?php
header('Content-Type: application/json');

// Database connection configuration
$host = "mysql-be91e57-donaph.g.aivencloud.com";
$db_user = "avnadmin";
$db_pass = getenv('DB_PASSWORD');
$db_name = "defaultdb";

$conn = new mysqli($host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Database connection failed"]);
    exit();
}

// Get user_id sent via POST request from the Android app
$user_id = $_POST['user_id'] ?? '';

if (!empty($user_id)) {
    // Corrected WHERE clause to search by 'id'
    $stmt = $conn->prepare("SELECT total_donations, impact_status FROM users WHERE id = ?");
    
    if (!$stmt) {
        echo json_encode(["status" => "error", "message" => "Query preparation failed: " . $conn->error]);
        exit();
    }

    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        echo json_encode([
            "status" => "success",
            "total_donations" => $row['total_donations'],
            "impact_status" => $row['impact_status']
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "User not found"]);
    }
    
    $stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "Missing user_id"]);
}

$conn->close();
?>