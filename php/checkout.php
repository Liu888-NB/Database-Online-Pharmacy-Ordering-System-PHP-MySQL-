<?php
session_start();
include "connectdb.php";

if (!isset($_SESSION['u_id'])) {
    die("Please login first");
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['order_id'], $_POST['address'])) {
    $order_id = intval($_POST['order_id']);
    $address = trim($_POST['address']);

    // Check if there are any un-reviewed prescription drugs
    $stmt = $conn->prepare("
        SELECT COUNT(*) FROM Order_Detail od
        JOIN Prescription p ON od.prescription_id = p.prescription_id
        WHERE od.order_id = ? AND p.p_status != 'valid'
    ");
    $stmt->execute([$order_id]);
    $unapprovedCount = $stmt->fetchColumn();

    if ($unapprovedCount > 0) {
        echo "<script>alert('The order contains unapproved prescription drugs and cannot be submitted'); window.history.back();</script>";
        exit;
    }

    try {
        $conn->beginTransaction();

        // Update the order status to in_transit, and the trigger will check the inventory and deduct it automatically.
        $update_order_sql = "UPDATE `Order` SET o_status = 'in_transit', order_time = NOW() WHERE order_id = ?";
        $stmt1 = $conn->prepare($update_order_sql);
        $stmt1->execute([$order_id]);

        // Insert the delivery information
        $insert_delivery_sql = "INSERT INTO Delivery (order_id, d_address, d_status, courier_contact) VALUES (?, ?, 'in_transit', '')";
        $stmt2 = $conn->prepare($insert_delivery_sql);
        $stmt2->execute([$order_id, $address]);

        $conn->commit();

        echo "<script>alert('Order submitted!'); window.location.href='menu.php';</script>";
    } catch (PDOException $e) {
        $conn->rollBack();
        echo "<script>alert('提交失败：" . $e->getMessage() . "'); window.history.back();</script>";
    }
    exit();
} else {
    echo "Invalid request";
}
?>






