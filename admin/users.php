<?php
include '../config.php';

// Block if not logged in as admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

// Handle delete user
if (isset($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];
    // Delete user's orders first (foreign key)
    $conn->query("DELETE FROM orders WHERE user_id = $delete_id");
    // Then delete user
    $conn->query("DELETE FROM users WHERE id = $delete_id");
    header("Location: users.php?deleted=1");
    exit();
}

// Handle search
$search = trim($_GET['search'] ?? '');

$sql = "SELECT users.*, COUNT(orders.id) as total_orders 
        FROM users 
        LEFT JOIN orders ON users.id = orders.user_id";

if ($search) {
    $search_safe = $conn->real_escape_string($search);
    $sql .= " WHERE users.name LIKE '%$search_safe%' 
              OR users.email LIKE '%$search_safe%'";
}

$sql .= " GROUP BY users.id ORDER BY users.created_at DESC";
$users = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Users - LiveTrack Admin</title>
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
        <a href="orders.php" class="bg-white text-gray-900 px-4 py-2 rounded text-sm font-semibold hover:bg-gray-200">Orders</a>
        <a href="add_order.php" class="bg-white text-gray-900 px-4 py-2 rounded text-sm font-semibold hover:bg-gray-200">Add Order</a>
        <a href="users.php" class="bg-gray-900 text-white px-4 py-2 rounded text-sm font-semibold">Users</a>
    </div>

    <div class="bg-white rounded-xl shadow p-6">

        <div class="flex justify-between items-center mb-6">
            <h2 class="text-lg font-bold">👥 All Users</h2>
            <span class="text-sm text-gray-500">
                Total: <strong><?= $users->num_rows ?></strong> users
            </span>
        </div>

        <?php if(isset($_GET['deleted'])): ?>
            <div class="bg-red-50 border border-red-200 text-red-600 p-3 rounded-lg mb-4 text-sm">
                User and their orders deleted successfully.
            </div>
        <?php endif; ?>

        <!-- Search -->
        <form method="GET" class="flex gap-3 mb-6">
            <input type="text" name="search"
                   value="<?= htmlspecialchars($search) ?>"
                   placeholder="Search by name or email..."
                   class="flex-1 border p-2 rounded focus:outline-none focus:ring-2 focus:ring-gray-800 text-sm">
            <button type="submit"
                    class="bg-gray-900 text-white px-4 py-2 rounded text-sm font-semibold hover:bg-gray-700">
                Search
            </button>
            <?php if($search): ?>
                <a href="users.php"
                   class="bg-gray-200 text-gray-700 px-4 py-2 rounded text-sm font-semibold hover:bg-gray-300">
                    Clear
                </a>
            <?php endif; ?>
        </form>

        <!-- Users Table -->
        <?php if ($users->num_rows > 0): ?>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left">
                        <th class="p-3 font-semibold">#</th>
                        <th class="p-3 font-semibold">Name</th>
                        <th class="p-3 font-semibold">Email</th>
                        <th class="p-3 font-semibold">Total Orders</th>
                        <th class="p-3 font-semibold">Joined</th>
                        <th class="p-3 font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php $count = 1; while($user = $users->fetch_assoc()): ?>
                    <tr class="border-t hover:bg-gray-50">
                        <td class="p-3 text-gray-400"><?= $count++ ?></td>
                        <td class="p-3 font-semibold"><?= htmlspecialchars($user['name']) ?></td>
                        <td class="p-3 text-gray-600"><?= htmlspecialchars($user['email']) ?></td>
                        <td class="p-3">
                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs font-semibold">
                                <?= $user['total_orders'] ?> orders
                            </span>
                        </td>
                        <td class="p-3 text-gray-500 text-xs">
                            <?= date('M d, Y', strtotime($user['created_at'])) ?>
                        </td>
                        <td class="p-3 flex gap-3">
                            <a href="orders.php?search=<?= urlencode($user['name']) ?>"
                               class="text-blue-500 hover:underline text-xs font-semibold">
                                View Orders
                            </a>
                            <a href="users.php?delete=<?= $user['id'] ?>"
                               onclick="return confirm('Delete this user and ALL their orders? This cannot be undone.')"
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
                <p class="text-4xl mb-2">👤</p>
                <p>No users found.</p>
            </div>
        <?php endif; ?>

    </div>
</div>
</body>
</html>