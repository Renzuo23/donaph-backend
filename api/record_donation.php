<?php
header('Content-Type: application/json');

$user_id        = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
$campaign_id    = isset($_POST['campaign_id']) ? intval($_POST['campaign_id']) : 1;
$amount         = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
$payment_method = isset($_POST['payment_method']) ? trim($_POST['payment_method']) : '';

// 1. Block guests / missing user IDs
if ($user_id <= 0) {
    echo json_encode(["status" => "error", "message" => "Unauthorized: Please log in to donate."]);
    exit();
}

// 2. Validate amount and payment method
if ($amount <= 0 || empty($payment_method)) {
    echo json_encode(["status" => "error", "message" => "Invalid amount or payment method"]);
    exit();
}

// Database configuration
$servername = "mysql-be91e57-donaph.g.aivencloud.com";
$username   = "avnadmin";
$password   = getenv('DB_PASSWORD');
$dbname     = "defaultdb";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "DB Connection Failed"]);
    exit();
}

// 3. Optional: Verify that the user actually exists in the database
$userCheck = $conn->prepare("SELECT id FROM users WHERE id = ?");
$userCheck->bind_param("i", $user_id);
$userCheck->execute();
$userCheck->store_result();

if ($userCheck->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Invalid user ID"]);
    $userCheck->close();
    $conn->close();
    exit();
}
$userCheck->close();

// 4. Insert the donation mapped to the correct user ID
$sql = "INSERT INTO donations (user_id, campaign_id, amount, payment_method, donation_date) VALUES (?, ?, ?, ?, NOW())";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iids", $user_id, $campaign_id, $amount, $payment_method);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Donation recorded successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => "SQL Error: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>