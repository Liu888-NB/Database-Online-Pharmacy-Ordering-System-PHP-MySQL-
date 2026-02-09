<?php
session_start();
require_once "connectdb.php";

// Assume the logged-in user ID is stored in session
if (!isset($_SESSION["u_id"])) {
    header("Location: login.php");
    exit();
}
$u_id = $_SESSION["u_id"];

// Retrieve all order information for this user (including pharmacy name, order details, and delivery status)
$sql = "
    SELECT o.order_id, o.total_amount, o.o_status, o.order_time, 
           p.pharmacy_name, d.d_status, d.d_address, d.courier_contact
    FROM `order` o
    JOIN pharmacy p ON o.pharmacy_id = p.pharmacy_id
    LEFT JOIN delivery d ON o.order_id = d.order_id
    WHERE o.u_id = ?
    ORDER BY o.order_time DESC
";
$stmt = $conn->prepare($sql);
$stmt->execute([$u_id]);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

$orders = [];
foreach ($result as $row) {
    $order_id = $row["order_id"];
    $orders[$order_id] = $row;
    $orders[$order_id]["details"] = [];

    // Search the detail information of every order
    $detail_sql = "
        SELECT od.product_id, pr.product_name, od.price, od.quantity
        FROM order_detail od
        JOIN product pr ON od.product_id = pr.product_id
        WHERE od.order_id = ?
    ";
    $detail_stmt = $conn->prepare($detail_sql);
    $detail_stmt->execute([$order_id]);
    $details = $detail_stmt->fetchAll(PDO::FETCH_ASSOC);

    $orders[$order_id]["details"] = $details;
}
?>


<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>My Order</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f7f7f7;
            margin: 0;
            padding: 2em;
        }
        h2 {
            color: #333;
        }
        .order {
            background: #fff;
            border-radius: 8px;
            padding: 1em 1.5em;
            margin-bottom: 1.5em;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .order h3 {
            margin-top: 0;
        }
        .order-details {
            margin: 1em 0;
        }
        .product {
            padding: 0.5em 0;
            border-bottom: 1px dashed #ccc;
        }
        .product:last-child {
            border-bottom: none;
        }
        .meta {
            font-size: 0.95em;
            color: #666;
        }
        .confirm-btn {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 0.5em 1em;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 0.5em;
        }
        .confirm-btn:hover {
            background-color: #45a049;
        }
        .status {
            font-weight: bold;
            color: #2a6592;
        }
    </style>
</head>
<body>
    <h2>My Orders</h2>

    <?php if (empty($orders)): ?>
        <p>No Order Record.</p>
    <?php else: ?>
        <?php foreach ($orders as $order): ?>
            <div class="order">
                <h3>Order ID: <?= $order["order_id"] ?> ｜ Pharmacy: <?= htmlspecialchars($order["pharmacy_name"]) ?></h3>
                <div class="meta">
                    Order Time: <?= $order["order_time"] ?><br>
                    Order Status: <span class="status"><?= htmlspecialchars($order["o_status"]) ?></span><br>
                    Delivery Status: <span class="status"><?= $order["d_status"] ?? "pending" ?></span><br>
                    Delivered Address: <?= $order["d_address"] ?? "Null" ?><br>
                    Delivery Phone Number: <?= $order["courier_contact"] ?? "Null" ?>
                </div>
                <div class="order-details">
                    <?php foreach ($order["details"] as $product): ?>
                        <div class="product">
                            Product Name: <?= htmlspecialchars($product["product_name"]) ?><br>
                            Unit Price: ¥<?= $product["price"] ?> ｜ Quantity: <?= $product["quantity"] ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <strong>Total Price: ¥<?= $order["total_amount"] ?></strong><br>

                <?php if ($order["o_status"] === "in_transit"): ?>
                    <form action="confirm_order.php" method="POST">
                        <input type="hidden" name="order_id" value="<?= $order["order_id"] ?>">
                        <button class="confirm-btn" type="submit">Confirm Receipt</button>
                    </form>
                <?php elseif ($order["o_status"] === "delivered"): ?>
                    <p style="color: green; font-weight: bold;">Received</p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>




