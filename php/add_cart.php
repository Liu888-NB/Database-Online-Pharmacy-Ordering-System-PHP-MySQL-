<?php
session_start();
include 'connectdb.php';

if (!isset($_SESSION['u_id'])) {
    echo "<script>alert('Please log in first and select a product!'); window.location.href='login.php';</script>";
    exit();
}

$product_id = intval($_POST['product_id']);
$pharmacy_id = intval($_POST['pharmacy_id']);
$quantity = intval($_POST['quantity']);
$u_id = intval($_SESSION['u_id']);  // User ID

echo "pharmacy_id: $pharmacy_id";  // debug

if ($quantity <= 0) {
    echo "<script>alert('Quantity must be greater than 0'); window.history.back();</script>";
    exit();
}

// Get product price
$product_query = $conn->prepare("SELECT price FROM Product WHERE product_id = :product_id");
$product_query->execute([':product_id' => $product_id]);
$product = $product_query->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    die("Product does not exist!");
}

$price = $product['price'];

// Check if the user already has a 'pending' order
$order_query = $conn->prepare("SELECT * FROM `order` WHERE u_id = :u_id AND o_status = 'pending'");
$order_query->execute([':u_id' => $u_id]);
$order_row = $order_query->fetch(PDO::FETCH_ASSOC);

if ($order_row) {
    // If there is a pending order, check if the pharmacy is the same
    if ($order_row['pharmacy_id'] != $pharmacy_id) {
        echo "<script>alert('Your cart already contains products from another pharmacy. Please complete or clear that order before adding products from a different pharmacy!'); window.location.href='cart.php';</script>";
        exit();
    }
    $order_id = $order_row['order_id'];
} else {
    // No pending order, create a new one
    $insert_order = $conn->prepare("INSERT INTO `order` (u_id, pharmacy_id, total_amount, o_status, order_time) VALUES (:u_id, :pharmacy_id, 0, 'pending', NOW())");
    $insert_order->execute([':u_id' => $u_id, ':pharmacy_id' => $pharmacy_id]);
    $order_id = $conn->lastInsertId();
}

// Check if the order already contains this product
$check_item = $conn->prepare("SELECT * FROM order_detail WHERE order_id = :order_id AND product_id = :product_id");
$check_item->execute([':order_id' => $order_id, ':product_id' => $product_id]);
$item_result = $check_item->fetch(PDO::FETCH_ASSOC);

if ($item_result) {
    // If the product exists, update the quantity
    $new_qty = $item_result['quantity'] + $quantity;
    $update_qty = $conn->prepare("UPDATE order_detail SET quantity = :quantity WHERE order_detail_id = :order_detail_id");
    $update_qty->execute([':quantity' => $new_qty, ':order_detail_id' => $item_result['order_detail_id']]);
} else {
    // If the product does not exist, insert a new item
    $insert_item = $conn->prepare("INSERT INTO order_detail (order_id, product_id, quantity, price) VALUES (:order_id, :product_id, :quantity, :price)");
    $insert_item->execute([':order_id' => $order_id, ':product_id' => $product_id, ':quantity' => $quantity, ':price' => $price]);
}

// Update the order total amount
$total_add = $price * $quantity;
$update_total = $conn->prepare("UPDATE `order` SET total_amount = total_amount + :total_add WHERE order_id = :order_id");
$update_total->execute([':total_add' => $total_add, ':order_id' => $order_id]);

header("Location: cart.php");
exit();
?>











