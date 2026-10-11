<?php
require_once 'db.php';

if (!headers_sent()) {
    header('Content-Type: application/json');
}

try {
    // Select the actual column names from your Byethost schema
    $campaignStmt = $conn->query("SELECT id, title, description, target_amount, raised_amount, image_url, created_at FROM campaigns");
    $campaigns = $campaignStmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "campaigns" => $campaigns
    ]);
} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Query failed: " . $e->getMessage()
    ]);
}
?>
