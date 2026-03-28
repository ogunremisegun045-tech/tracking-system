<?php
include '../config.php';

// If already logged in as admin, go to admin dashboard
if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = "";

if (isset($_POST['login'])) {
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $error = "All fields are required!";
    } else {
        $stmt = $conn->prepare("SELECT id, name, password FROM admins WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($id, $name, $hashed_password);
        $stmt->fetch();

        if ($stmt->num_rows > 0 && password_verify($password, $hashed_password)) {
            $_SESSION['admin_id']   = $id;
            $_SESSION['admin_name'] = $name;
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid email or password!";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Login - LiveTrack</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 flex items-center justify-center h-screen">
<div class="bg-white p-8 rounded-lg shadow-lg w-96">
    <h1 class="text-2xl font-bold mb-2 text-center">Admin Panel</h1>
    <p class="text-center text-gray-400 text-sm mb-6">LiveTrack Management</p>

    <?php if($error): ?>
        <p class="text-red-500 mb-4 text-sm"><?= $error ?></p>
    <?php endif; ?>

    <form method="POST" class="flex flex-col gap-4">
        <input type="email" name="email" placeholder="Admin Email"
               class="border p-2 rounded focus:outline-none focus:ring-2 focus:ring-gray-800" required>
        <input type="password" name="password" placeholder="Password"
               class="border p-2 rounded focus:outline-none focus:ring-2 focus:ring-gray-800" required>
        <button type="submit" name="login"
                class="bg-gray-900 text-white py-2 rounded hover:bg-gray-700">
            Login as Admin
        </button>
    </form>
</div>
</body>
</html>