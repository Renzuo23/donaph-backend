<?php
header('Content-Type: application/json');
include 'db.php'; // Now returns a PDO instance

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
$response = array();

if ($user_id <= 0) {
    echo json_encode(["success" => false, "message" => "Invalid User ID"]);
    exit();
}

// 1. Total Donations
$stmt = $conn->prepare("SELECT SUM(amount) as total FROM given_donations WHERE user_id = ?");
$stmt->execute([$user_id]);
$donationRow = $stmt->fetch();
$totalAmount = $donationRow['total'] ? $donationRow['total'] : 0;
$formattedDonation = "₱" . number_format($totalAmount, 2);

// 2. User Impact
$stmtImpact = $conn->prepare("SELECT COUNT(DISTINCT charity_id) as communities FROM given_donations WHERE user_id = ?");
$stmtImpact->execute([$user_id]);
$impactRow = $stmtImpact->fetch();
$communitiesCount = $impactRow['communities'] ? $impactRow['communities'] : 0;
$formattedImpact = "Helped " . $communitiesCount . " Communities";

// 3. Campaigns
$campResult = $conn->query("SELECT title, raised, goal, progress FROM campaigns");
$campaigns = $campResult->fetchAll();

$response['success'] = true;
$response['total_donation'] = $formattedDonation;
$response['impact'] = $formattedImpact;
$response['campaigns'] = $campaigns;

echo json_encode($response);
?>
