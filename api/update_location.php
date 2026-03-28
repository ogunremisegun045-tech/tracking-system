<?php
include '../config.php';

// Must be logged in as admin
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

$order_id  = (int)($_POST['order_id'] ?? 0);
$location  = trim(htmlspecialchars($_POST['location']  ?? ''));
$latitude  = (float)($_POST['latitude']  ?? 0);
$longitude = (float)($_POST['longitude'] ?? 0);
$status    = trim($_POST['status'] ?? '');

if (!$order_id || !$location) {
    echo json_encode(['error' => 'Missing required fields']);
    exit();
}

$stmt = $conn->prepare("UPDATE orders 
                         SET location  = ?, 
                             latitude  = ?, 
                             longitude = ?,
                             status    = ?
                         WHERE id = ?");
$stmt->bind_param("sddsi", 
    $location, 
    $latitude, 
    $longitude, 
    $status, 
    $order_id
);

if ($stmt->execute()) {
    echo json_encode([
        'success'   => true,
        'message'   => 'Location updated successfully',
        'order_id'  => $order_id,
        'location'  => $location,
        'latitude'  => $latitude,
        'longitude' => $longitude,
        'status'    => $status
    ]);
} else {
    echo json_encode(['error' => 'Failed to update location']);
}

$stmt->close();
?>