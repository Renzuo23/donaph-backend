<?php
require_once 'db.php';

if (!headers_sent()) {
    header('Content-Type: application/json');
}

if (!isset($conn) || !$conn) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Database connection object is not available."
    ]);
    exit;
}

try {
    $campaignStmt = $conn->query("SELECT id, title, description, target_amount, raised_amount, image_url, created_at FROM campaigns");
    $campaigns = $campaignStmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "campaigns" => $campaigns
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Query failed: " . $e->getMessage()
    ]);
}
?>
