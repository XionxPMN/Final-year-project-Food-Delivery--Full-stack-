<?php
$host = 'localhost';
$dbname = 'foodlink_myanmar';
$username = 'root'; // Change this if your local server uses a different username
$password = '';     // Change this if your local server uses a password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
