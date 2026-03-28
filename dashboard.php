<?php
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_name = $_SESSION['user_name'];
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT tracking_id, item_description, status, location, created_at FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Dashboard - Live Tracking</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <link rel="stylesheet" href="asset/css/style.css">
</head>
<body class="bg-gray-100 min-h-screen">

<nav class="bg-blue-600 text-white px-6 py-4 flex justify-between items-center shadow">
    <h1 class="text-xl font-bold">📦 LiveTrack</h1>
    <div class="flex items-center gap-4">
        <span class="text-sm">Welcome, <strong><?= htmlspecialchars($user_name) ?></strong></span>
        <a href="logout.php" class="bg-white text-blue-600 px-3 py-1 rounded text-sm font-semibold hover:bg-gray-100">Logout</a>
    </div>
</nav>

<div class="max-w-5xl mx-auto px-4 py-8">

    <div class="bg-white rounded-xl shadow p-6 mb-8">
        <h2 class="text-lg font-bold mb-4">🔍 Track an Order</h2>
        <div class="flex gap-3">
            <input type="text" id="trackingInput" placeholder="Enter Tracking ID (e.g. TRK-00123)"
                   class="flex-1 border p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            <button onclick="trackOrder()"
                    class="bg-blue-500 text-white px-5 py-2 rounded hover:bg-blue-600 font-semibold">
                Track
            </button>
        </div>

        <div id="trackResult" class="mt-4 hidden">
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div class="bg-gray-50 p-3 rounded-lg border">
                    <p class="text-xs text-gray-500">Status</p>
                    <p id="resultStatus" class="font-bold text-lg"></p>
                </div>
                <div class="bg-gray-50 p-3 rounded-lg border">
                    <p class="text-xs text-gray-500">Current Location</p>
                    <p id="resultLocation" class="font-bold text-lg"></p>
                </div>
            </div>
            <div id="map"></div>
        </div>

        <div id="trackError" class="mt-4 hidden bg-red-50 border border-red-200 text-red-600 p-3 rounded-lg text-sm"></div>
    </div>

    <div class="bg-white rounded-xl shadow p-6">
        <h2 class="text-lg font-bold mb-4">📋 My Orders</h2>

        <?php if ($orders->num_rows > 0): ?>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left">
                        <th class="p-3 font-semibold">Tracking ID</th>
                        <th class="p-3 font-semibold">Item</th>
                        <th class="p-3 font-semibold">Status</th>
                        <th class="p-3 font-semibold">Location</th>
                        <th class="p-3 font-semibold">Date</th>
                        <th class="p-3 font-semibold">Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php while($order = $orders->fetch_assoc()): ?>
                    <tr class="border-t hover:bg-gray-50">
                        <td class="p-3 font-mono text-blue-600"><?= htmlspecialchars($order['tracking_id']) ?></td>
                        <td class="p-3"><?= htmlspecialchars($order['item_description']) ?></td>
                        <td class="p-3">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold
                                status-<?= strtolower($order['status']) ?>">
                                <?= htmlspecialchars($order['status']) ?>
                            </span>
                        </td>
                        <td class="p-3"><?= htmlspecialchars($order['location']) ?></td>
                        <td class="p-3 text-gray-500"><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
                        <td class="p-3">
                            <button onclick="quickTrack('<?= htmlspecialchars($order['tracking_id']) ?>')"
                                    class="text-blue-500 hover:underline text-xs font-semibold">
                                Track
                            </button>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <div class="text-center py-10 text-gray-400">
                <p class="text-4xl mb-2">📭</p>
                <p>No orders found yet.</p>
            </div>
        <?php endif; ?>
    </div>

</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="asset/js/map.js"></script>
</body>
</html>