<?php
session_start();
require 'connectdb.php';

$pharmacy_id = $_SESSION['pharmacy_id'] ?? null;
if (!$pharmacy_id) {
    die("Pharmacy account not logged in!");
}

// add the product 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $product_name = $_POST['product_name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $is_prescription_required = $_POST['is_prescription_required'] ?? 0;
    $stock_quantity = $_POST['stock_quantity'];

    $stmt = $conn->prepare("SELECT product_id FROM product WHERE product_name = ?");
    $stmt->execute([$product_name]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        $product_id = $existing['product_id'];
    } else {
        $stmt = $conn->prepare("INSERT INTO product (product_name, description, price, is_prescription_required) VALUES (?, ?, ?, ?)");
        $stmt->execute([$product_name, $description, $price, $is_prescription_required]);
        $product_id = $conn->lastInsertId();
    }

    $stmt = $conn->prepare("INSERT INTO inventory (pharmacy_id, product_id, stock_quantity) VALUES (?, ?, ?)");
    $stmt->execute([$pharmacy_id, $product_id, $stock_quantity]);

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// delete the product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product_id'])) {
    $delete_product_id = $_POST['delete_product_id'];
    $stmt = $conn->prepare("DELETE FROM inventory WHERE pharmacy_id = ? AND product_id = ?");
    $stmt->execute([$pharmacy_id, $delete_product_id]);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Search inventory
$sql = "SELECT 
            p.product_id,
            p.product_name,
            p.description,
            p.price,
            p.is_prescription_required,
            i.stock_quantity
        FROM inventory i
        JOIN product p ON i.product_id = p.product_id
        WHERE i.pharmacy_id = ?";

$stmt = $conn->prepare($sql);
$stmt->execute([$pharmacy_id]);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pharmacy Inventory</title>
    <style>
        * {
            box-sizing: border-box;
        }

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
            text-align: center;
            color: #007bff;
            margin: 30px 0 10px;
        }

        .container {
            max-width: 960px;
            margin: auto;
            padding: 20px;
        }

        form {
            background: #ffffff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        label {
            font-weight: bold;
            display: block;
            margin: 15px 0 5px;
        }

        input, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }

        button {
            background-color: #007bff;
            color: white;
            padding: 10px 18px;
            border: none;
            border-radius: 8px;
            margin-top: 15px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #0056b3;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        th, td {
            padding: 14px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background-color: #007bff;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f7f9fc;
        }

        .yes {
            color: #28a745;
            font-weight: bold;
        }

        .no {
            color: #dc3545;
            font-weight: bold;
        }

        .delete-btn {
            background-color: #dc3545;
            padding: 8px 14px;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        .delete-btn:hover {
            background-color: #c82333;
        }

        .back-btn {
            display: block;
            width: 220px;
            margin: 30px auto;
            text-align: center;
            background-color: #6c757d;
            color: white;
            text-decoration: none;
            padding: 12px 0;
            border-radius: 8px;
        }

        .back-btn:hover {
            background-color: #5a6268;
        }

        @media (max-width: 768px) {
            table, thead, tbody, th, td, tr {
                display: block;
            }

            th, td {
                padding: 10px;
            }

            th {
                background: #007bff;
                color: white;
                position: sticky;
                top: 0;
            }

            tr {
                margin-bottom: 15px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Pharmacy Inventory Management</h2>

    <form method="POST">
        <input type="hidden" name="add_product" value="1">

        <label>Product Name:</label>
        <input type="text" name="product_name" required>

        <label>Description:</label>
        <input type="text" name="description" required>

        <label>Price (CNY):</label>
        <input type="number" step="0.01" name="price" required>

        <label>Prescription Required:</label>
        <select name="is_prescription_required">
            <option value="0">No</option>
            <option value="1">Yes</option>
        </select>

        <label>Stock Quantity:</label>
        <input type="number" name="stock_quantity" required>

        <button type="submit">Add Product</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>Product Name</th>
                <th>Description</th>
                <th>Price (CNY)</th>
                <th>Prescription</th>
                <th>Stock</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($products as $product): ?>
            <tr>
                <td><?= htmlspecialchars($product['product_name']) ?></td>
                <td><?= htmlspecialchars($product['description']) ?></td>
                <td><?= number_format($product['price'], 2) ?></td>
                <td class="<?= $product['is_prescription_required'] ? 'yes' : 'no' ?>">
                    <?= $product['is_prescription_required'] ? 'Yes' : 'No' ?>
                </td>
                <td><?= (int)$product['stock_quantity'] ?></td>
                <td>
                    <form method="POST" onsubmit="return confirm('Delete this product from inventory?');">
                        <input type="hidden" name="delete_product_id" value="<?= $product['product_id'] ?>">
                        <button type="submit" class="delete-btn">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <a href="pharmacy_home.php" class="back-btn">← Back to Home</a>
</div>

</body>
</html>



