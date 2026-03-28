<?php
include 'config.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = "";

if (isset($_POST['login'])) {
    $email    = trim(htmlspecialchars($_POST['email']));
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $error = "All fields are required!";
    } else {
        $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($id, $name, $hashed_password);
        $stmt->fetch();

        if ($stmt->num_rows > 0 && password_verify($password, $hashed_password)) {
            $_SESSION['user_id']   = $id;
            $_SESSION['user_name'] = $name;
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
<title>Login - Live Tracking</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
<div class="bg-white p-8 rounded-lg shadow-lg w-96">
    <h1 class="text-2xl font-bold mb-6 text-center">Login</h1>

    <?php if($error): ?>
        <p class="text-red-500 mb-4 text-sm"><?= $error ?></p>
    <?php endif; ?>

    <form method="POST" class="flex flex-col gap-4">
        <input type="email" name="email" placeholder="Email"
               class="border p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" required>
        <input type="password" name="password" placeholder="Password"
               class="border p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" required>
        <button type="submit" name="login"
                class="bg-blue-500 text-white py-2 rounded hover:bg-blue-600">Login</button>
        <p class="text-sm text-center">
            No account? <a href="register.php" class="text-blue-500 hover:underline">Register</a>
        </p>
    </form>
</div>
</body>
</html>