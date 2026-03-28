<?php
include '../config.php';

// Must be logged in as admin
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

// Order counts
$total_orders = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
$total_users  = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$pending      = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status='Pending'")->fetch_assoc()['count'];
$shipped      = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status='Shipped'")->fetch_assoc()['count'];
$delivered    = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status='Delivered'")->fetch_assoc()['count'];
$cancelled    = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status='Cancelled'")->fetch_assoc()['count'];

// Orders created in last 7 days
$recent = $conn->query("SELECT COUNT(*) as count FROM orders 
                         WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")
                ->fetch_assoc()['count'];

echo json_encode([
    'total_orders' => $total_orders,
    'total_users'  => $total_users,
    'pending'      => $pending,
    'shipped'      => $shipped,
    'delivered'    => $delivered,
    'cancelled'    => $cancelled,
    'last_7_days'  => $recent
]);
?>