<?php
session_start();
include "connectdb.php";

if (!isset($_SESSION['u_id'])) {
    die("Please login");
}

$u_id = $_SESSION['u_id'];

// Get the unique pending order
$order_sql = "SELECT * FROM `order` WHERE u_id = ? AND o_status = 'pending' LIMIT 1";
$stmt = $conn->prepare($order_sql);
$stmt->execute([$u_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    echo "<div style='text-align:center; margin-top:50px;'>
            <h2>🛒 Cart is Empty</h2>
            <a href='menu.php'><input type='button' value='Back To Menu'></a>
          </div>";
    exit();
}

$order_id = $order['order_id'];

// Query order details
$detail_sql = "SELECT od.*, p.product_name, p.price, ph.pharmacy_name
               FROM order_detail od
               JOIN product p ON od.product_id = p.product_id
               JOIN `order` o ON o.order_id = od.order_id
               JOIN pharmacy ph ON o.pharmacy_id = ph.pharmacy_id
               WHERE od.order_id = ?";
$stmt = $conn->prepare($detail_sql);
$stmt->execute([$order_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// If no products, show empty message
if (empty($items)) {
    echo "<div style='text-align:center; margin-top:50px;'>
            <h2>🛒 Cart is Empty</h2>
            <a href='menu.php'><input type='button' value='Back to Menu'></a>
          </div>";
    exit();
}

$totalAmount = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cart</title>
    <style>
        body {
            font-family: "Segoe UI", sans-serif;
            background-color: #f7f7f7;
            margin: 20px;
            padding: 0;
            color: #333;
        }
        h2, h3 {
            text-align: center;
        }
        table {
            width: 80%;
            margin: 20px auto;
            border-collapse: collapse;
            background-color: #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #ccc;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
        input[type="text"], input[type="submit"], input[type="button"] {
            padding: 10px;
            margin: 5px;
            border: none;
            border-radius: 5px;
        }
        input[type="text"] {
            width: 300px;
            border: 1px solid #ccc;
        }
        input[type="submit"], input[type="button"] {
            background-color: #4CAF50;
            color: white;
            cursor: pointer;
        }
        input[type="submit"]:hover, input[type="button"]:hover {
            background-color: #45a049;
        }
        form {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>

<h2>🛒 Your Shopping Cart</h2>
<table>
    <tr>
        <th>Product Name</th>
        <th>Pharmacy</th>
        <th>Quantity</th>
        <th>Unit Price (￥)</th>
        <th>Subtotal (￥)</th>
        <th>Operation</th>
    </tr>
    <?php foreach ($items as $item):
        $subtotal = $item['price'] * $item['quantity'];
        $totalAmount += $subtotal;
    ?>
    <tr>
        <td><?= htmlspecialchars($item['product_name']) ?></td>
        <td><?= htmlspecialchars($item['pharmacy_name']) ?></td>
        <td><?= $item['quantity'] ?></td>
        <td><?= number_format($item['price'], 2) ?></td>
        <td><?= number_format($subtotal, 2) ?></td>
        <td>
            <form method="post" action="remove_cart_item.php" style="display:inline;">
                <input type="hidden" name="order_detail_id" value="<?= $item['order_detail_id'] ?>">
                <input type="submit" value="Delete">
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<h3>Total Price: ￥<?= number_format($totalAmount, 2) ?></h3>

<form method="post" action="checkout.php">
    <input type="hidden" name="order_id" value="<?= $order_id ?>">
    <label for="address">Delivery Address:</label><br><br>
    <input type="text" name="address" required><br><br>
    <input type="submit" value="Submit Order">
</form>

<div style="text-align: center;">
    <a href="menu.php"><input type="button" value="Back to Menu"></a>
</div>

</body>
</html>





