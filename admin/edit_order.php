<?php
include '../config.php';

// Block if not logged in as admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

$error   = "";
$success = "";

// Get order ID from URL
$order_id = (int)($_GET['id'] ?? 0);

if (!$order_id) {
    header("Location: orders.php");
    exit();
}

// Fetch the order
$stmt = $conn->prepare("SELECT orders.*, users.name as customer_name 
                         FROM orders 
                         JOIN users ON orders.user_id = users.id 
                         WHERE orders.id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order  = $result->fetch_assoc();
$stmt->close();

// If order not found
if (!$order) {
    header("Location: orders.php");
    exit();
}

// Handle form submission
if (isset($_POST['update_order'])) {
    $status    = trim($_POST['status']);
    $location  = trim(htmlspecialchars($_POST['location']));
    $latitude  = trim($_POST['latitude']);
    $longitude = trim($_POST['longitude']);
    $item_description = trim(htmlspecialchars($_POST['item_description']));

    if (empty($location) || empty($item_description)) {
        $error = "Please fill all required fields!";
    } else {
        $stmt = $conn->prepare("UPDATE orders 
                                SET status = ?, 
                                    location = ?, 
                                    latitude = ?, 
                                    longitude = ?,
                                    item_description = ?
                                WHERE id = ?");
        $stmt->bind_param("ssddsi", 
            $status, 
            $location, 
            $latitude, 
            $longitude,
            $item_description,
            $order_id
        );

        if ($stmt->execute()) {
            $success = "Order updated successfully!";
            // Refresh order data
            $stmt2 = $conn->prepare("SELECT orders.*, users.name as customer_name 
                                     FROM orders 
                                     JOIN users ON orders.user_id = users.id 
                                     WHERE orders.id = ?");
            $stmt2->bind_param("i", $order_id);
            $stmt2->execute();
            $result2 = $stmt2->get_result();
            $order   = $result2->fetch_assoc();
            $stmt2->close();
        } else {
            $error = "Failed to update order. Try again.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Order - LiveTrack Admin</title>
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
        <a href="orders.php" class="bg-gray-900 text-white px-4 py-2 rounded text-sm font-semibold">Orders</a>
        <a href="add_order.php" class="bg-white text-gray-900 px-4 py-2 rounded text-sm font-semibold hover:bg-gray-200">Add Order</a>
        <a href="users.php" class="bg-white text-gray-900 px-4 py-2 rounded text-sm font-semibold hover:bg-gray-200">Users</a>
    </div>

    <div class="bg-white rounded-xl shadow p-6">

        <!-- Order Info -->
        <div class="flex justify-between items-start mb-6">
            <div>
                <h2 class="text-lg font-bold">✏️ Edit Order</h2>
                <p class="text-sm text-gray-500 mt-1">
                    Tracking ID: 
                    <span class="font-mono text-blue-600">
                        <?= htmlspecialchars($order['tracking_id']) ?>
                    </span>
                </p>
                <p class="text-sm text-gray-500">
                    Customer: <strong><?= htmlspecialchars($order['customer_name']) ?></strong>
                </p>
            </div>
            <a href="orders.php"
               class="text-sm text-gray-500 hover:underline">
                ← Back to Orders
            </a>
        </div>

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

            <!-- Item Description -->
            <div>
                <label class="block text-sm font-semibold mb-1">
                    Item Description <span class="text-red-500">*</span>
                </label>
                <input type="text" name="item_description"
                       value="<?= htmlspecialchars($order['item_description']) ?>"
                       class="w-full border p-2 rounded focus:outline-none focus:ring-2 focus:ring-gray-800" required>
            </div>

            <!-- Status -->
            <div>
                <label class="block text-sm font-semibold mb-1">Status</label>
                <select name="status"
                        class="w-full border p-2 rounded focus:outline-none focus:ring-2 focus:ring-gray-800">
                    <option value="Pending"   <?= $order['status']=='Pending'   ? 'selected' : '' ?>>Pending</option>
                    <option value="Shipped"   <?= $order['status']=='Shipped'   ? 'selected' : '' ?>>Shipped</option>
                    <option value="Delivered" <?= $order['status']=='Delivered' ? 'selected' : '' ?>>Delivered</option>
                    <option value="Cancelled" <?= $order['status']=='Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
            </div>

            <!-- Location -->
            <div>
                <label class="block text-sm font-semibold mb-1">
                    Current Location <span class="text-red-500">*</span>
                </label>
                <input type="text" name="location"
                       value="<?= htmlspecialchars($order['location']) ?>"
                       placeholder="e.g. Ibadan Hub"
                       class="w-full border p-2 rounded focus:outline-none focus:ring-2 focus:ring-gray-800" required>
            </div>

            <!-- GPS Coordinates -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold mb-1">Latitude</label>
                    <input type="number" name="latitude" step="any"
                           value="<?= $order['latitude'] ?>"
                           class="w-full border p-2 rounded focus:outline-none focus:ring-2 focus:ring-gray-800">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Longitude</label>
                    <input type="number" name="longitude" step="any"
                           value="<?= $order['longitude'] ?>"
                           class="w-full border p-2 rounded focus:outline-none focus:ring-2 focus:ring-gray-800">
                </div>
            </div>

            <p class="text-xs text-gray-400">
                💡 Use 
                <a href="https://www.latlong.net" target="_blank" 
                   class="text-blue-500 hover:underline">latlong.net</a> 
                to find coordinates for any location.
            </p>

            <!-- Quick Location Presets -->
            <div>
                <label class="block text-sm font-semibold mb-2">
                    Quick Location Presets
                </label>
                <div class="flex flex-wrap gap-2">
                    <button type="button"
                            onclick="setLocation('Lagos Warehouse', 6.5244, 3.3792)"
                            class="bg-gray-100 text-gray-700 px-3 py-1 rounded text-xs hover:bg-gray-200">
                        Lagos
                    </button>
                    <button type="button"
                            onclick="setLocation('Abuja Hub', 9.0765, 7.3986)"
                            class="bg-gray-100 text-gray-700 px-3 py-1 rounded text-xs hover:bg-gray-200">
                        Abuja
                    </button>
                    <button type="button"
                            onclick="setLocation('Kano Depot', 12.0022, 8.5920)"
                            class="bg-gray-100 text-gray-700 px-3 py-1 rounded text-xs hover:bg-gray-200">
                        Kano
                    </button>
                    <button type="button"
                            onclick="setLocation('Port Harcourt Hub', 4.8156, 7.0498)"
                            class="bg-gray-100 text-gray-700 px-3 py-1 rounded text-xs hover:bg-gray-200">
                        Port Harcourt
                    </button>
                    <button type="button"
                            onclick="setLocation('Ibadan Center', 7.3775, 3.9470)"
                            class="bg-gray-100 text-gray-700 px-3 py-1 rounded text-xs hover:bg-gray-200">
                        Ibadan
                    </button>
                    <button type="button"
                            onclick="setLocation('Enugu Station', 6.4584, 7.5464)"
                            class="bg-gray-100 text-gray-700 px-3 py-1 rounded text-xs hover:bg-gray-200">
                        Enugu
                    </button>
                </div>
            </div>

            <button type="submit" name="update_order"
                    class="bg-gray-900 text-white py-2 rounded hover:bg-gray-700 font-semibold">
                Update Order
            </button>

        </form>
    </div>
</div>

<script>
function setLocation(name, lat, lng) {
    document.querySelector('[name="location"]').value  = name;
    document.querySelector('[name="latitude"]').value  = lat;
    document.querySelector('[name="longitude"]').value = lng;
}
</script>

</body>
</html>