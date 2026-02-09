<?php
session_start();
require 'connectdb.php';

// Get pharmacy_id from session after login
$pharmacy_id = $_SESSION['pharmacy_id'] ?? null;

if (!$pharmacy_id) {
    die("Pharmacy account not logged in!");
}

// Fetch all orders related to this pharmacy
$sql = "SELECT 
            o.order_id,
            u.u_name AS buyer_name,
            o.total_amount,
            o.o_status,
            o.order_time
        FROM `order` o
        JOIN user u ON o.u_id = u.u_id
        WHERE o.pharmacy_id = ?
        ORDER BY o.order_time DESC";

$stmt = $conn->prepare($sql);
$stmt->execute([$pharmacy_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Pharmacy Orders</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-image: url('back_picture.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed; 
            margin: 0; 
            height: 100vh;
        }
        h2 {
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: center;
        }
        th {
            background: #007BFF;
            color: white;
        }
        tr:nth-child(even) {
            background: #f9f9f9;
        }
    </style>
</head>
<body>
    <h2>Orders Received</h2>
    <table>
        <tr>
            <th>Order ID</th>
            <th>Buyer Name</th>
            <th>Total Amount</th>
            <th>Status</th>
            <th>Order Time</th>
        </tr>
        <?php foreach ($orders as $order): ?>
        <tr>
            <td><?= htmlspecialchars($order['order_id']) ?></td>
            <td><?= htmlspecialchars($order['buyer_name']) ?></td>
            <td><?= $order['total_amount'] ?> CNY</td>
            <td><?= htmlspecialchars($order['o_status']) ?></td>
            <td><?= $order['order_time'] ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>

