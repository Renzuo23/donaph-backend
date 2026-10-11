<?php
ob_start();

http_response_code(200);
header('Content-Type: application/json; charset=utf-8');

error_reporting(E_ALL);
ini_set('display_errors', 0);

// ==============================================================================
// 1. DATABASE CONFIGURATION
// ==============================================================================
$host = "mysql-be91e57-donaph.g.aivencloud.com";
$user = "avnadmin";
$pass = getenv('DB_PASSWORD');
$dbname = "defaultdb";

$conn = @new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    ob_end_clean();
    echo json_encode([
        "success" => false, 
        "message" => "Database connection failed: " . $conn->connect_error
    ]);
    exit();
}

$conn->set_charset("utf8mb4");

// ==============================================================================
// 2. READ INPUT DATA (Handles both Form-Data and JSON)
// ==============================================================================
$jsonInput = file_get_contents('php://input');
$data = json_decode($jsonInput, true);

// Fallback to $_POST if JSON body is empty
$email = trim($_POST['email'] ?? $data['email'] ?? '');
$password = trim($_POST['password'] ?? $data['password'] ?? '');

if (empty($email) || empty($password)) {
    ob_end_clean();
    echo json_encode([
        "success" => false, 
        "message" => "Please enter both email and password"
    ]);
    exit();
}

// ==============================================================================
// 3. AUTHENTICATION LOGIC
// ==============================================================================
$stmt = $conn->prepare("SELECT id, full_name, email, phone_number, role, password FROM users WHERE email = ? LIMIT 1");

if (!$stmt) {
    ob_end_clean();
    echo json_encode([
        "success" => false, 
        "message" => "Database query error: " . $conn->error
    ]);
    exit();
}

$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $storedPassword = $row['password'] ?? '';

    if (password_verify($password, $storedPassword) || $password === $storedPassword) {
        $token = bin2hex(random_bytes(16));

        ob_end_clean();
        echo json_encode([
            "success" => true,
            "message" => "Login successful",
            "token" => $token,
            "user" => [
                "id" => (int)$row['id'],
                "full_name" => $row['full_name'],
                "email" => $row['email'],
                "phone_number" => $row['phone_number'],
                "role" => $row['role']
            ]
        ]);
        $stmt->close();
        $conn->close();
        exit();
    }
}

ob_end_clean();
echo json_encode([
    "success" => false, 
    "message" => "Invalid email or password"
]);

$stmt->close();
$conn->close();
exit();
?>