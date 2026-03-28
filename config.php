<?php
$servername = "gondola.proxy.rlwy.net";
$username = "root";
$password = "BTyJyfvFctqqGTWAZdWwwxPfHoGdvbhL";
$database = "railway";
$port = 48985;

$conn = new mysqli($servername, $username, $password, $database, $port);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>