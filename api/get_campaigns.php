<?php
header('Content-Type: application/json');

// Database connection details for Byethost
$servername = "mysql-be91e57-donaph.g.aivencloud.com"; // Your Byethost SQL server
$username = "avnadmin";          // Replace with your Byethost DB username
$password = getenv('DB_PASSWORD');       // Replace with your Byethost DB password
$dbname = "defaultdb";    // Replace with your Byethost DB name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    echo json_encode([
        "status" => "error",
        "message" => "Database connection failed: " . $conn->connect_error
    ]);
    exit();
}

// Fetch all campaigns from the campaigns table
$sql = "SELECT id, title, description, target_amount, raised_amount, image_url FROM campaigns";
$result = $conn->query($sql);

$campaigns = array();

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        // Cast numerical fields properly for JSON
        $row['id'] = (int)$row['id'];
        $row['target_amount'] = (double)$row['target_amount'];
        $row['raised_amount'] = (double)$row['raised_amount'];
        
        $campaigns[] = $row;
    }
    
    echo json_encode([
        "status" => "success",
        "campaigns" => $campaigns
    ]);
} else {
    echo json_encode([
        "status" => "success",
        "campaigns" => []
    ]);
}

$conn->close();
?>