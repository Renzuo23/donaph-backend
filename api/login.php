<?php
require_once 'db.php';

if (!headers_sent()) {
    header('Content-Type: application/json');
}

// Ensure the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        "success" => false,
        "message" => "Invalid request method. Please use POST."
    ]);
    exit;
}

// Read raw JSON input or form data
$input = json_decode(file_get_contents('php://input'), true);
$email = $input['email'] ?? $_POST['email'] ?? '';
$password = $input['password'] ?? $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    echo json_encode([
        "success" => false,
        "message" => "Email and password are required."
    ]);
    exit;
}

try {
    // Query user by email matching your table schema
    $stmt = $conn->prepare("SELECT id, full_name, email, password, role, total_donations, impact_status FROM users WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verify credentials
    // Note: For plain-text passwords in legacy data like '123456', check direct equality.
    // If using password_hash(), use password_verify($password, $user['password']).
    if ($user && ($password === $user['password'] || password_verify($password, $user['password']))) {
        // Omit sensitive password hash from response
        unset($user['password']);

        echo json_encode([
            "success" => true,
            "message" => "Login successful",
            "user" => $user
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Invalid email or password."
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Database query failed: " . $e->getMessage()
    ]);
}
?>
