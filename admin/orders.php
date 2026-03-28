<?php
include '../config.php';

// Block if not logged in as admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

// Handle delete
if (isset($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];
    $conn->query("DELETE FROM orders WHERE id = $delete_id");
    header("Location: orders.php?deleted=1");
    exit();
}

// Handle search/filter
$search = trim($_GET['search'] ?? '');
$filter = trim($_GET['filter'] ?? '');

$sql = "SELECT orders.*, users.name as customer_name 
        FROM orders 
        JOIN users ON orders.user_id = users.id";

$conditions = [];

if ($search) {
    $search_safe = $conn->real_escape_string($search);
    $conditions[] = "(orders.tracking_id LIKE '%$search_safe%' 
                   OR orders.item_description LIKE '%$search_safe%' 
                   OR users.name LIKE '%$search_safe%')";
}

if ($filter) {
    $filter_safe = $conn->real_escape_string($filter);
    $conditions[] = "orders.status = '$filter_safe'";
}

if ($conditions) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

$sql .= " ORDER BY orders.created_at DESC";
$orders = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Orders - LiveTrack Admin</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">

<!-- Navbar -->
<nav class="bg-gray-900 text-white px-6 py-4 flex justify-between items-center shadow">
    <h1 class="text-xl font-bold">📦 LiveTrack Admin</h1>
    <div class="flex items-center gap-4">
        <span class="text-sm">Welcome, <strong><?= htmlspecialchars($_SESSION['admin_name']) ?></strong></span>
        <a href="logout.php" class="bg-white text-gray-900 px-3 py-1 rounded text-sm font-semibold hover:bg-gray-100">Logout</a>
    </div>
</nav>

<div class="max-w-6xl mx-auto px-4 py-8">

    <!-- Nav Links -->
    <div class="flex gap-3 mb-8">
        <a href="dashboard.php" class="bg-white text-gray-900 px-4 py-2 rounded text-sm font-semibold hover:bg-gray-200">Dashboard</a>
        <a href="orders.php" class="bg-gray-900 text-white px-4 py-2 rounded text-sm font-semibold">Orders</a>
        <a href="add_order.php" class="bg-white text-gray-900 px-4 py-2 rounded text-sm font-semibold hover:bg-gray-200">Add Order</a>
        <a href="users.php" class="bg-white text-gray-900 px-4 py-2 rounded text-sm font-semibold hover:bg-gray-200">Users</a>
    </div>

    <div class="bg-white rounded-xl shadow p-6">

        <div class="flex justify-between items-center mb-6">
            <h2 class="text-lg font-bold">📋 All Orders</h2>
            <a href="add_order.php"
               class="bg-gray-900 text-white px-4 py-2 rounded text-sm font-semibold hover:bg-gray-700">
                + Add Order
            </a>
        </div>

        <?php if(isset($_GET['deleted'])): ?>
            <div class="bg-red-50 border border-red-200 text-red-600 p-3 rounded-lg mb-4 text-sm">
                Order deleted successfully.
            </div>
        <?php endif; ?>

        <?php if(isset($_GET['updated'])): ?>
            <div class="bg-green-50 border border-green-200 text-green-700 p-3 rounded-lg mb-4 text-sm">
                Order updated successfully.
            </div>
        <?php endif; ?>

        <!-- Search and Filter -->
        <form method="GET" class="flex gap-3 mb-6">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
                   placeholder="Search by name, item, tracking ID..."
                   class="flex-1 border p-2 rounded focus:outline-none focus:ring-2 focus:ring-gray-800 text-sm">
            <select name="filter"
                    class="border p-2 rounded focus:outline-none focus:ring-2 focus:ring-gray-800 text-sm">
                <option value="">All Status</option>
                <option value="Pending"   <?= $filter=='Pending'   ? 'selected' : '' ?>>Pending</option>
                <option value="Shipped"   <?= $filter=='Shipped'   ? 'selected' : '' ?>>Shipped</option>
                <option value="Delivered" <?= $filter=='Delivered' ? 'selected' : '' ?>>Delivered</option>
                <option value="Cancelled" <?= $filter=='Cancelled' ? 'selected' : '' ?>>Cancelled</option>
            </select>
            <button type="submit"
                    class="bg-gray-900 text-white px-4 py-2 rounded text-sm font-semibold hover:bg-gray-700">
                Search
            </button>
            <?php if($search || $filter): ?>
                <a href="orders.php"
                   class="bg-gray-200 text-gray-700 px-4 py-2 rounded text-sm font-semibold hover:bg-gray-300">
                    Clear
                </a>
            <?php endif; ?>
        </form>

        <!-- Orders Table -->
        <?php if ($orders->num_rows > 0): ?>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left">
                        <th class="p-3 font-semibold">Tracking ID</th>
                        <th class="p-3 font-semibold">Customer</th>
                        <th class="p-3 font-semibold">Item</th>
                        <th class="p-3 font-semibold">Status</th>
                        <th class="p-3 font-semibold">Location</th>
                        <th class="p-3 font-semibold">Date</th>
                        <th class="p-3 font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php while($order = $orders->fetch_assoc()): ?>
                    <tr class="border-t hover:bg-gray-50">
                        <td class="p-3 font-mono text-blue-600 text-xs"><?= htmlspecialchars($order['tracking_id']) ?></td>
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
                        <td class="p-3"><?= htmlspecialchars($order['location']) ?></td>
                        <td class="p-3 text-gray-500 text-xs"><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
                        <td class="p-3 flex gap-3">
                            <a href="edit_order.php?id=<?= $order['id'] ?>"
                               class="text-blue-500 hover:underline text-xs font-semibold">
                                Edit
                            </a>
                            <a href="orders.php?delete=<?= $order['id'] ?>"
                               onclick="return confirm('Are you sure you want to delete this order?')"
                               class="text-red-500 hover:underline text-xs font-semibold">
                                Delete
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
                <p>No orders found.</p>
            </div>
        <?php endif; ?>

    </div>
</div>
</body>
</html>