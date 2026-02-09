
<?php
session_start();

// connect to the database
include "connectdb.php";

$isLoggedIn = false;
$userInfo = null;
$userOrders = [];

if (isset($_SESSION['u_id'])) {
    $u_id = $_SESSION['u_id'];
    $isLoggedIn = true;

    // Get the information of User（PDO）
    $stmt = $conn->prepare("SELECT u_name, phone FROM User WHERE u_id = :uid");
    $stmt->execute([':uid' => $u_id]);
    $userInfo = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get the message of order
    $stmt = $conn->prepare("SELECT order_id, total_amount, o_status, order_time FROM `Order` WHERE u_id = :uid");
    $stmt->execute([':uid' => $u_id]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($orders as $order) {
        $stmtDetail = $conn->prepare("
            SELECT od.product_id, p.product_name, od.price, od.quantity
            FROM Order_Detail od
            JOIN Product p ON od.product_id = p.product_id
            WHERE od.order_id = :oid
        ");
        $stmtDetail->execute([':oid' => $order['order_id']]);
        $order['details'] = $stmtDetail->fetchAll(PDO::FETCH_ASSOC);
        $userOrders[] = $order;
    }
}
?>

<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <title>Liu's Handmade Home</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            text-align: center;
            background-image: url('back_picture.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed; 
            margin: 0; 
            height: 100vh;
        }

        header {
            background-color: #009879;
            color: white;
            padding: 20px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            padding-left: 10%;
            padding-right: 10%;
        }

        .logo-container {
            display: flex;
            align-items: center;
        }

        .logo {
            height: 50px;
            margin-right: 20px;
        }

        nav a.button {
            background-color: white;
            color: #009879;
            padding: 8px 15px;
            margin: 0 10px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
        }

        button.button, input[type="submit"] {
            background-color: white;
            color: #009879;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            margin-left: 10px;
            cursor: pointer;
            font-weight: bold;
        }

        .content {
            max-width: 800px;
            margin: 30px auto;
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        h2 {
            color: #009879;
            margin-bottom: 20px;
        }

        section {
            margin-bottom: 30px;
            text-align: left;
        }

        ul {
            padding-left: 20px;
        }
    </style>
</head>
<body>
<header>
    <div class="logo-container">
        <a href="index.php"><img src="qiao_logo.svg" alt="Logo" class="logo"></a>
        <nav>
            <a href="menu.php" class="button">All Product</a>
        </nav>
    </div>
    <div>
        <?php if ($isLoggedIn): ?>
            <span>Welcom, <?php echo htmlspecialchars($userInfo['u_name']); ?>！</span>
            <form method="post" action="logout.php" style="display:inline;">
                <input type="submit" value="Log Out">
            </form>
        <?php else: ?>
            <button class="button login-btn" onclick="location.href='login.php'">Login</button>
            <button class="button register-btn" onclick="location.href='register.php'">Registe</button>
        <?php endif; ?>
    </div>
</header>

<div class="content">
    <?php if ($isLoggedIn): ?>
    <section style="margin-top: 20px;">
        <h2>My information</h2>
        <p>User name: <?php echo htmlspecialchars($userInfo['u_name']); ?></p>
        <p>Contact: <?php echo htmlspecialchars($userInfo['phone']); ?></p>
    </section>

    <section style="margin-top: 30px;">
        <h2>My order</h2>
        <?php if (count($userOrders) === 0): ?>
            <p>No Order Record.</p>
        <?php else: ?>
            <?php foreach ($userOrders as $order): ?>
                <div style="border:1px solid #ccc; padding:10px; margin-bottom:10px;">
                    <p>Order ID: <?php echo $order['order_id']; ?></p>
                    <p>Total Price: <?php echo $order['total_amount']; ?> 元</p>
                    <p>Status: <?php echo $order['o_status']; ?></p>
                    <p>Order Time: <?php echo $order['order_time']; ?></p>
                    <ul>
                        <?php foreach ($order['details'] as $item): ?>
                            <li><?php echo $item['product_name']; ?> - ￥<?php echo $item['price']; ?> × <?php echo $item['quantity']; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
    <?php endif; ?>
</div>
</body>
</html>