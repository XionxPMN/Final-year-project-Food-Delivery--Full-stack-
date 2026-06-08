<?php
session_start();
require 'includes/db.php';
header('Content-Type: application/json');

if (!isset($_GET['id']) || !isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$order_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT status FROM orders WHERE order_id = ? AND user_id = ?");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch();

if ($order) {
    echo json_encode(['status' => $order['status']]);
} else {
    echo json_encode(['error' => 'Order not found']);
}
?>