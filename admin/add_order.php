<?php
include '../config.php';

// Block if not logged in as admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

$success = "";
$error   = "";

// Fetch all users for dropdown
$users = $conn->query("SELECT id, name, email FROM users ORDER BY name ASC");

if (isset($_POST['add_order'])) {
    $user_id          = trim($_POST['user_id']);
    $item_description = trim(htmlspecialchars($_POST['item_description']));
    $status           = trim($_POST['status']);
    $location         = trim(htmlspecialchars($_POST['location']));
    $latitude         = trim($_POST['latitude']);
    $longitude        = trim($_POST['longitude']);

    // Auto generate tracking ID
    $tracking_id = 'TRK-' . strtoupper(uniqid());

    if (empty($user_id) || empty($item_description) || empty($location)) {
        $error = "Please fill all required fields!";
    } else {
        $stmt = $conn->prepare("INSERT INTO orders 
            (user_id, tracking_id, item_description, status, location, latitude, longitude) 
            VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssdd", 
            $user_id, 
            $tracking_id, 
            $item_description, 
            $status, 
            $location, 
            $latitude, 
            $longitude
        );

        if ($stmt->execute()) {
            $success = "Order created! Tracking ID: <strong>" . $tracking_id . "</strong>";
        } else {
            $error = "Failed to create order. Try again.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Order - LiveTrack Admin</title>
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

<div class="max-w-3xl mx-auto px-4 py-8">

    <!-- Nav Links -->
    <div class="flex gap-3 mb-8">
        <a href="dashboard.php" class="bg-white text-gray-900 px-4 py-2 rounded text-sm font-semibold hover:bg-gray-200">Dashboard</a>
        <a href="orders.php" class="bg-white text-gray-900 px-4 py-2 rounded text-sm font-semibold hover:bg-gray-200">Orders</a>
        <a href="add_order.php" class="bg-gray-900 text-white px-4 py-2 rounded text-sm font-semibold">Add Order</a>
        <a href="users.php" class="bg-white text-gray-900 px-4 py-2 rounded text-sm font-semibold hover:bg-gray-200">Users</a>
    </div>

    <div class="bg-white rounded-xl shadow p-6">
        <h2 class="text-lg font-bold mb-6">➕ Add New Order</h2>

        <?php if($success): ?>
            <div class="bg-green-50 border border-green-200 text-green-700 p-3 rounded-lg mb-4 text-sm">
                <?= $success ?>
            </div>
        <?php endif; ?>

        <?php if($error): ?>
            <div class="bg-red-50 border border-red-200 text-red-600 p-3 rounded-lg mb-4 text-sm">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="flex flex-col gap-5">

            <!-- Customer -->
            <div>
                <label class="block text-sm font-semibold mb-1">Customer <span class="text-red-500">*</span></label>
                <select name="user_id" required
                        class="w-full border p-2 rounded focus:outline-none focus:ring-2 focus:ring-gray-800">
                    <option value="">-- Select Customer --</option>
                    <?php while($user = $users->fetch_assoc()): ?>
                        <option value="<?= $user['id'] ?>">
                            <?= htmlspecialchars($user['name']) ?> (<?= htmlspecialchars($user['email']) ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Item Description -->
            <div>
                <label class="block text-sm font-semibold mb-1">Item Description <span class="text-red-500">*</span></label>
                <input type="text" name="item_description" placeholder="e.g. Nike Sneakers Size 42"
                       class="w-full border p-2 rounded focus:outline-none focus:ring-2 focus:ring-gray-800" required>
            </div>

            <!-- Status -->
            <div>
                <label class="block text-sm font-semibold mb-1">Status</label>
                <select name="status"
                        class="w-full border p-2 rounded focus:outline-none focus:ring-2 focus:ring-gray-800">
                    <option value="Pending">Pending</option>
                    <option value="Shipped">Shipped</option>
                    <option value="Delivered">Delivered</option>
                    <option value="Cancelled">Cancelled</option>
                </select>
            </div>

            <!-- Location -->
            <div>
                <label class="block text-sm font-semibold mb-1">Current Location <span class="text-red-500">*</span></label>
                <input type="text" name="location" placeholder="e.g. Lagos Warehouse"
                       class="w-full border p-2 rounded focus:outline-none focus:ring-2 focus:ring-gray-800" required>
            </div>

            <!-- GPS Coordinates -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold mb-1">Latitude</label>
                    <input type="number" name="latitude" step="any" value="6.5244"
                           class="w-full border p-2 rounded focus:outline-none focus:ring-2 focus:ring-gray-800">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Longitude</label>
                    <input type="number" name="longitude" step="any" value="3.3792"
                           class="w-full border p-2 rounded focus:outline-none focus:ring-2 focus:ring-gray-800">
                </div>
            </div>

            <p class="text-xs text-gray-400">
                💡 Default coordinates are set to Lagos. Use 
                <a href="https://www.latlong.net" target="_blank" class="text-blue-500 hover:underline">latlong.net</a> 
                to find coordinates for any location.
            </p>

            <button type="submit" name="add_order"
                    class="bg-gray-900 text-white py-2 rounded hover:bg-gray-700 font-semibold">
                Create Order
            </button>

        </form>
    </div>
</div>
</body>
</html>