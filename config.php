<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "tracking_db";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Session started ONLY here
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>