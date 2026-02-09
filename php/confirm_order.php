<?php
session_start();
require_once "connectdb.php";

if (!isset($_POST["order_id"]) || !isset($_SESSION["u_id"])) {
    header("Location: my_order.php");
    exit();
}

$order_id = $_POST["order_id"];
$u_id = $_SESSION["u_id"];

// Ensure the order belongs to the current user and the order status is 'in_transit'
$sql = "SELECT * FROM `order` WHERE order_id = ? AND u_id = ? AND o_status = 'in_transit'";
$stmt = $conn->prepare($sql);
$stmt->execute([$order_id, $u_id]);

if ($stmt->rowCount() === 1) {
    // Update order status and delivery status at the same time
    $conn->beginTransaction();
    try {
        // Update order status to delivered
        $update_order_sql = "UPDATE `order` SET o_status = 'delivered' WHERE order_id = ?";
        $update_order_stmt = $conn->prepare($update_order_sql);
        $update_order_stmt->execute([$order_id]);

        // Update delivery status to delivered
        $update_delivery_sql = "UPDATE `delivery` SET d_status = 'delivered' WHERE order_id = ?";
        $update_delivery_stmt = $conn->prepare($update_delivery_sql);
        $update_delivery_stmt->execute([$order_id]);

        $conn->commit();
    } catch (Exception $e) {
        $conn->rollBack();
        // Optional: log the error
    }
}

header("Location: my_order.php");
exit();



