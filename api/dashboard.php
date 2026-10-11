<?php
require_once 'db.php';

if (!headers_sent()) {
    header('Content-Type: application/json');
}

try {
    // Fetch campaigns
    $campaignStmt = $conn->query("SELECT id, title, raised, goal, progress FROM campaigns");
    $campaigns = $campaignStmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch total given donations
    $donationStmt = $conn->query("SELECT SUM(amount) AS total_donated FROM given_donations");
    $donationResult = $donationStmt->fetch(PDO::FETCH_ASSOC);
    $totalDonated = $donationResult['total_donated'] ? (float)$donationResult['total_donated'] : 0.00;

    echo json_encode([
        "success" => true,
        "total_donated" => $totalDonated,
        "campaigns" => $campaigns
    ]);
} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Query failed: " . $e->getMessage()
    ]);
}
?>
