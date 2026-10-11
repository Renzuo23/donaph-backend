<?php
// Prevent output buffering leaks and header corruptions
ob_start();

// Set JSON response header
header('Content-Type: application/json; charset=utf-8');

// Disable error display
error_reporting(E_ALL);
ini_set('display_errors', 0);

// ==============================================================================
// 1. DATABASE CONFIGURATION (Byethost)
// ==============================================================================
$host = "mysql-be91e57-donaph.g.aivencloud.com";
$user = "avnadmin";
$pass = getenv('DB_PASSWORD');
$dbname = "defaultdb";

// Create database connection
$conn = @new mysqli($host, $user, $pass, $dbname);

// Check connection failure
if ($conn->connect_error) {
    ob_end_clean();
    echo json_encode([
        "success" => false, 
        "message" => "Database connection failed: " . $conn->connect_error
    ]);
    exit();
}

// Ensure correct character set
$conn->set_charset("utf8mb4");

// ==============================================================================
// 2. READ AND VALIDATE INPUT DATA
// ==============================================================================
// Read raw JSON input sent via HTTP POST body from Retrofit
$jsonInput = file_get_contents('php://input');
$data = json_decode($jsonInput, true);

// Extract fields safely
$fullName = trim($data['full_name'] ?? '');
$email = trim($data['email'] ?? '');
$phoneNumber = trim($data['phone_number'] ?? '');
$role = trim($data['role'] ?? 'donor'); // Default to 'donor' if omitted
$password = trim($data['password'] ?? '');

// Validate required fields
if (empty($fullName) || empty($email) || empty($password)) {
    ob_end_clean();
    echo json_encode([
        "success" => false, 
        "message" => "Please fill in all required fields"
    ]);
    exit();
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    ob_end_clean();
    echo json_encode([
        "success" => false, 
        "message" => "Invalid email format"
    ]);
    exit();
}

// Validate role enum
$allowedRoles = ['donor', 'charity', 'admin'];
if (!in_array($role, $allowedRoles)) {
    $role = 'donor';
}

// ==============================================================================
// 3. CHECK IF EMAIL ALREADY EXISTS
// ==============================================================================
$checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
$checkStmt->bind_param("s", $email);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows > 0) {
    ob_end_clean();
    echo json_encode([
        "success" => false,
        "message" => "Email address is already registered"
    ]);
    $checkStmt->close();
    $conn->close();
    exit();
}
$checkStmt->close();

// ==============================================================================
// 4. INSERT NEW USER RECORD
// ==============================================================================
// Hash password securely using BCrypt
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

$insertStmt = $conn->prepare("INSERT INTO users (full_name, email, phone_number, role, password) VALUES (?, ?, ?, ?, ?)");

if (!$insertStmt) {
    ob_end_clean();
    echo json_encode([
        "success" => false,
        "message" => "Database insertion error: " . $conn->error
    ]);
    exit();
}

$insertStmt->bind_param("sssss", $fullName, $email, $phoneNumber, $role, $hashedPassword);

if ($insertStmt->execute()) {
    $newUserId = $insertStmt->insert_id;

    ob_end_clean();
    echo json_encode([
        "success" => true,
        "message" => "User registered successfully",
        "user" => [
            "id" => (int)$newUserId,
            "full_name" => $fullName,
            "email" => $email,
            "phone_number" => $phoneNumber,
            "role" => $role
        ]
    ]);
} else {
    ob_end_clean();
    echo json_encode([
        "success" => false,
        "message" => "Registration failed: " . $insertStmt->error
    ]);
}

$insertStmt->close();
$conn->close();
exit();
?>