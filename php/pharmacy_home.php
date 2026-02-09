<?php
session_start();
if (!isset($_SESSION["pharmacy_id"])) {
    header("Location: pharmacy_login.php");
    exit();
}

$pharmacy_name = $_SESSION["pharmacy_name"] ?? "My Pharmacy";
?>

<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($pharmacy_name) ?> - Pharmacy ome</title>
    <style>
        body {
            font-family: "Segoe UI", sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
            color: #333;
            background-image: url('back_picture.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed; 
        }


        .header {
            background-color: #007bff;
            color: white;
            padding: 20px;
            text-align: center;
        }

        .container {
            display: flex;
            justify-content: center;
            margin-top: 50px;
            gap: 30px;
            flex-wrap: wrap;
        }

        .card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            width: 250px;
            height: 150px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-decoration: none;
            color: #333;
            transition: 0.2s ease-in-out;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 15px rgba(0,0,0,0.15);
        }

        .card h3 {
            margin: 0;
            font-size: 20px;
            color: #007bff;
        }

        .logout {
            display: block;
            width: 100px;
            margin: 40px auto 0;
            padding: 10px;
            text-align: center;
            background-color: #dc3545;
            color: white;
            border-radius: 5px;
            text-decoration: none;
        }

        .logout:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>

<div class="header">
    <h1>Welcome, <?= htmlspecialchars($pharmacy_name) ?> 👋</h1>
</div>

<div class="container">
    <a href="pharmacy_products.php" class="card">
        <h3>🧪 Manage Products</h3>
    </a>
    <a href="pharmacy_order.php" class="card">
        <h3>📦 View Orders</h3>
    </a>
    <a href="pending_prescription.php" class="card">
        <h3>📋 Review Prescriptions</h3>
    </a>
</div>

<a class="logout" href="logout.php">Logout</a>

</body>
</html>


