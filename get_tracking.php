<?php
include 'config.php';

// Must be logged in to track
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$tracking_id = trim(htmlspecialchars($_GET['tracking_id'] ?? ''));

if (!$tracking_id) {
    echo json_encode(['status'=>'N/A','location'=>'N/A','latitude'=>0,'longitude'=>0]);
    exit();
}

// Prepared statement - no SQL injection
$stmt = $conn->prepare("SELECT status, location, latitude, longitude FROM orders WHERE tracking_id = ?");
$stmt->bind_param("s", $tracking_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if ($order) {
    echo json_encode($order);
} else {
    echo json_encode(['status'=>'Not Found','location'=>'Unknown','latitude'=>0,'longitude'=>0]);
}

$stmt->close();
?>