<?php
include '../config.php';

// Must be logged in as admin
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

$order_id = (int)($_GET['id'] ?? 0);

if (!$order_id) {
    echo json_encode(['error' => 'No order ID provided']);
    exit();
}

$stmt = $conn->prepare("SELECT orders.*, users.name as customer_name, users.email as customer_email 
                         FROM orders 
                         JOIN users ON orders.user_id = users.id 
                         WHERE orders.id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order  = $result->fetch_assoc();
$stmt->close();

if ($order) {
    echo json_encode($order);
} else {
    echo json_encode(['error' => 'Order not found']);
}
?>