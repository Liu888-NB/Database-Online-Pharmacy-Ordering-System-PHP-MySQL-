<?php
session_start();
include "connectdb.php";

if (!isset($_POST['order_detail_id'])) {
    die("Parameter error");
}

$order_detail_id = $_POST['order_detail_id'];

// Query the order ID corresponding to this order detail
$stmt = $conn->prepare("SELECT order_id FROM order_detail WHERE order_detail_id = ?");
$stmt->execute([$order_detail_id]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$result) {
    die("Order item not found");
}

$order_id = $result['order_id'];

// Delete this order item
$stmt = $conn->prepare("DELETE FROM order_detail WHERE order_detail_id = ?");
$stmt->execute([$order_detail_id]);

// Check whether the order still has other products
$stmt = $conn->prepare("SELECT COUNT(*) FROM order_detail WHERE order_id = ?");
$stmt->execute([$order_id]);
$count = $stmt->fetchColumn();

if ($count == 0) {
    // No products left, delete the order
    $stmt = $conn->prepare("DELETE FROM `order` WHERE order_id = ?");
    $stmt->execute([$order_id]);
}

header("Location: cart.php");
exit();
?>



