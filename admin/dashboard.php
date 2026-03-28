<?php
include '../config.php';

// Block if not logged in as admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

$admin_name = $_SESSION['admin_name'];

// Get total orders
$total_orders = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];

// Get total users
$total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];

// Get orders by status
$pending   = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status='Pending'")->fetch_assoc()['count'];
$shipped   = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status='Shipped'")->fetch_assoc()['count'];
$delivered = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status='Delivered'")->fetch_assoc()['count'];
$cancelled = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status='Cancelled'")->fetch_assoc()['count'];

// Get 5 most recent orders
$recent_orders = $conn->query("SELECT orders.*, users.name as customer_name 
                                FROM orders 
                                JOIN users ON orders.user_id = users.id 
                                ORDER BY orders.created_at DESC 
                                LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard - LiveTrack</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">

<!-- Navbar -->
<nav class="bg-gray-900 text-white px-6 py-4 flex justify-between items-center shadow">
    <h1 class="text-xl font-bold">📦 LiveTrack Admin</h1>
    <div class="flex items-center gap-4">
        <span class="text-sm">Welcome, <strong><?= htmlspecialchars($admin_name) ?></strong></span>
        <a href="logout.php" class="bg-white text-gray-900 px-3 py-1 rounded text-sm font-semibold hover:bg-gray-100">Logout</a>
    </div>
</nav>

<div class="max-w-6xl mx-auto px-4 py-8">

    <!-- Nav Links -->
    <div class="flex gap-3 mb-8">
        <a href="dashboard.php" class="bg-gray-900 text-white px-4 py-2 rounded text-sm font-semibold">Dashboard</a>
        <a href="orders.php" class="bg-white text-gray-900 px-4 py-2 rounded text-sm font-semibold hover:bg-gray-200">Orders</a>
        <a href="add_order.php" class="bg-white text-gray-900 px-4 py-2 rounded text-sm font-semibold hover:bg-gray-200">Add Order</a>
        <a href="users.php" class="bg-white text-gray-900 px-4 py-2 rounded text-sm font-semibold hover:bg-gray-200">Users</a>
    </div>

    <!-- Stat Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-xl shadow p-5">
            <p class="text-xs text-gray-500 mb-1">Total Orders</p>
            <p class="text-3xl font-bold text-gray-800"><?= $total_orders ?></p>
        </div>
        <div class="bg-white rounded-xl shadow p-5">
            <p class="text-xs text-gray-500 mb-1">Total Users</p>
            <p class="text-3xl font-bold text-gray-800"><?= $total_users ?></p>
        </div>
        <div class="bg-white rounded-xl shadow p-5">
            <p class="text-xs text-gray-500 mb-1">Delivered</p>
            <p class="text-3xl font-bold text-green-600"><?= $delivered ?></p>
        </div>
        <div class="bg-white rounded-xl shadow p-5">
            <p class="text-xs text-gray-500 mb-1">Pending</p>
            <p class="text-3xl font-bold text-yellow-500"><?= $pending ?></p>
        </div>
    </div>

    <!-- Status Breakdown -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 text-center">
            <p class="text-xs text-yellow-700 font-semibold">Pending</p>
            <p class="text-2xl font-bold text-yellow-600"><?= $pending ?></p>
        </div>
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-center">
            <p class="text-xs text-blue-700 font-semibold">Shipped</p>
            <p class="text-2xl font-bold text-blue-600"><?= $shipped ?></p>
        </div>
        <div class="bg-green-50 border border-green-200 rounded-xl p-4 text-center">
            <p class="text-xs text-green-700 font-semibold">Delivered</p>
            <p class="text-2xl font-bold text-green-600"><?= $delivered ?></p>
        </div>
        <div class="bg-red-50 border border-red-200 rounded-xl p-4 text-center">
            <p class="text-xs text-red-700 font-semibold">Cancelled</p>
            <p class="text-2xl font-bold text-red-600"><?= $cancelled ?></p>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="bg-white rounded-xl shadow p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-bold">🕐 Recent Orders</h2>
            <a href="orders.php" class="text-blue-500 text-sm hover:underline">View All</a>
        </div>

        <?php if ($recent_orders->num_rows > 0): ?>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left">
                        <th class="p-3 font-semibold">Tracking ID</th>
                        <th class="p-3 font-semibold">Customer</th>
                        <th class="p-3 font-semibold">Item</th>
                        <th class="p-3 font-semibold">Status</th>
                        <th class="p-3 font-semibold">Date</th>
                        <th class="p-3 font-semibold">Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php while($order = $recent_orders->fetch_assoc()): ?>
                    <tr class="border-t hover:bg-gray-50">
                        <td class="p-3 font-mono text-blue-600"><?= htmlspecialchars($order['tracking_id']) ?></td>
                        <td class="p-3"><?= htmlspecialchars($order['customer_name']) ?></td>
                        <td class="p-3"><?= htmlspecialchars($order['item_description']) ?></td>
                        <td class="p-3">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold
                                <?php
                                switch($order['status']) {
                                    case 'Pending':   echo 'bg-yellow-100 text-yellow-800'; break;
                                    case 'Shipped':   echo 'bg-blue-100 text-blue-800';    break;
                                    case 'Delivered': echo 'bg-green-100 text-green-800';  break;
                                    case 'Cancelled': echo 'bg-red-100 text-red-800';      break;
                                }
                                ?>">
                                <?= htmlspecialchars($order['status']) ?>
                            </span>
                        </td>
                        <td class="p-3 text-gray-500"><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
                        <td class="p-3">
                            <a href="edit_order.php?id=<?= $order['id'] ?>"
                               class="text-blue-500 hover:underline text-xs font-semibold">
                                Edit
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <div class="text-center py-10 text-gray-400">
                <p class="text-4xl mb-2">📭</p>
                <p>No orders yet.</p>
            </div>
        <?php endif; ?>
    </div>

</div>
</body>
</html>