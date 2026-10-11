<?php
require_once 'db.php';

if (!headers_sent()) {
    header('Content-Type: application/json');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        "success" => false,
        "message" => "Invalid request method. Please use POST."
    ]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$email = trim($input['email'] ?? $_POST['email'] ?? '');
$password = $input['password'] ?? $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    echo json_encode([
        "success" => false,
        "message" => "Email and password are required."
    ]);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT id, full_name, email, password, role, total_donations, impact_status FROM users WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode([
            "success" => false,
            "message" => "User not found."
        ]);
        exit;
    }

    $storedPassword = $user['password'];

    // Check if password matches via password_verify (for hashes) OR direct string comparison (for legacy plaintext)
    $isPasswordValid = false;
    if (password_verify($password, $storedPassword)) {
        $isPasswordValid = true;
    } elseif ($password === $storedPassword) {
        $isPasswordValid = true;
        
        // Optional: Automatically upgrade plain text password to a secure hash on successful login
        $newHash = password_hash($password, PASSWORD_DEFAULT);
        $updateStmt = $conn->prepare("UPDATE users SET password = :password WHERE id = :id");
        $updateStmt->execute([':password' => $newHash, ':id' => $user['id']]);
    }

    if ($isPasswordValid) {
        unset($user['password']); // Never return password hash
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
