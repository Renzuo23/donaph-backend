<?php
header('Content-Type: application/json');

// Database configuration
$servername = "mysql-be91e57-donaph.g.aivencloud.com";
$username   = "avnadmin";
$password   = getenv('DB_PASSWORD');
$dbname     = "defaultdb";

// Connect to MySQL
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Database connection failed"]);
    exit();
}

// Get user_id from GET request (e.g., get_user_donations.php?user_id=1)
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

if ($user_id <= 0) {
    echo json_encode(["status" => "error", "message" => "Invalid user ID"]);
    exit();
}

// 1. Calculate Totals (Total, This Month, This Week)
$total_query = "SELECT 
    COALESCE(SUM(amount), 0) AS total_donation,
    COALESCE(SUM(CASE WHEN MONTH(donation_date) = MONTH(CURRENT_DATE()) AND YEAR(donation_date) = YEAR(CURRENT_DATE()) THEN amount ELSE 0 END), 0) AS month_donation,
    COALESCE(SUM(CASE WHEN YEARWEEK(donation_date, 1) = YEARWEEK(CURRENT_DATE(), 1) THEN amount ELSE 0 END), 0) AS week_donation
    FROM donations WHERE user_id = ?";

$stmt = $conn->prepare($total_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$totals_result = $stmt->get_result()->fetch_assoc();
$stmt->close();

// 2. Fetch Recently Donated Campaigns
$campaigns_query = "SELECT c.id, c.title, c.image_url, d.amount, d.donation_date 
                    FROM donations d 
                    JOIN campaigns c ON d.campaign_id = c.id 
                    WHERE d.user_id = ? 
                    ORDER BY d.donation_date DESC 
                    LIMIT 5";

$stmt = $conn->prepare($campaigns_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$campaigns_result = $stmt->get_result();

$recent_donations = array();
while ($row = $campaigns_result->fetch_assoc()) {
    $recent_donations[] = array(
        "campaign_id"   => $row['id'],
        "title"         => $row['title'],
        "image_url"     => $row['image_url'],
        "amount"        => $row['amount'],
        "donation_date" => date("F j, Y", strtotime($row['donation_date']))
    );
}
$stmt->close();
$conn->close();

// 3. Return Combined JSON Response
echo json_encode(array(
    "status"           => "success",
    "total_donation"   => floatval($totals_result['total_donation']),
    "month_donation"   => floatval($totals_result['month_donation']),
    "week_donation"    => floatval($totals_result['week_donation']),
    "recent_donations" => $recent_donations
));
?>