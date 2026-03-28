<?php
include 'config.php';

$error = "";

if (isset($_POST['register'])) {
    $name     = trim(htmlspecialchars($_POST['name']));
    $email    = trim(htmlspecialchars($_POST['email']));
    $password = trim($_POST['password']);

    if (empty($name) || empty($email) || empty($password)) {
        $error = "All fields are required!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format!";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters!";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Email already exists!";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $email, $hashed_password);

            if ($stmt->execute()) {
                $_SESSION['user_id'] = $conn->insert_id;
                $_SESSION['user_name'] = $name;
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Registration failed. Try again.";
            }
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Register - Live Tracking</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
<div class="bg-white p-8 rounded-lg shadow-lg w-96">
    <h1 class="text-2xl font-bold mb-6 text-center">Create Account</h1>

    <?php if($error): ?>
        <p class="text-red-500 mb-4 text-sm"><?= $error ?></p>
    <?php endif; ?>

    <form method="POST" class="flex flex-col gap-4">
        <input type="text" name="name" placeholder="Full Name"
               class="border p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" required>
        <input type="email" name="email" placeholder="Email"
               class="border p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" required>
        <input type="password" name="password" placeholder="Password (min 6 chars)"
               class="border p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" required>
        <button type="submit" name="register"
                class="bg-blue-500 text-white py-2 rounded hover:bg-blue-600">Register</button>
        <p class="text-sm text-center">
            Already have an account? <a href="index.php" class="text-blue-500 hover:underline">Login</a>
        </p>
    </form>
</div>
</body>
</html>