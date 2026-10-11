<?php
header('Content-Type: application/json');
include 'db.php'; // Your database connection file

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

$response = array();

if ($user_id <= 0) {
    echo json_encode(["success" => false, "message" => "Invalid User ID"]);
    exit();
}

// 1. Calculate Total Donations dynamically for THIS specific user
$donationQuery = "SELECT SUM(amount) as total FROM given_donations WHERE user_id = ?";
$stmt = $conn->prepare($donationQuery);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$donationResult = $stmt->get_result();
$donationRow = $donationResult->fetch_assoc();
$totalAmount = $donationRow['total'] ? $donationRow['total'] : 0;
$formattedDonation = "₱" . number_format($totalAmount, 2);

// 2. Calculate User Impact dynamically (e.g., count how many unique charities or communities they helped)
$impactQuery = "SELECT COUNT(DISTINCT charity_id) as communities FROM given_donations WHERE user_id = ?";
$stmtImpact = $conn->prepare($impactQuery);
$stmtImpact->bind_param("i", $user_id);
$stmtImpact->execute();
$impactResult = $stmtImpact->get_result();
$impactRow = $impactResult->fetch_assoc();
$communitiesCount = $impactRow['communities'] ? $impactRow['communities'] : 0;
$formattedImpact = "Helped " . $communitiesCount . " Communities";

// 3. Fetch Ongoing Campaigns from the campaigns table
$campaigns = array();
$campResult = $conn->query("SELECT title, raised, goal, progress FROM campaigns");
while($row = $campResult->fetch_assoc()) {
    $campaigns[] = $row;
}

$response['success'] = true;
$response['total_donation'] = $formattedDonation;
$response['impact'] = $formattedImpact;
$response['campaigns'] = $campaigns;

echo json_encode($response);
$conn->close();
?>