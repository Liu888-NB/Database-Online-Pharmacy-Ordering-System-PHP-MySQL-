<?php
session_start();
require 'connectdb.php';

// Ensure pharmacy user is logged in
$pharmacy_id = $_SESSION['pharmacy_id'] ?? null;
if (!$pharmacy_id) {
    die("Please log in to your pharmacy account!");
}

// Handle approval or rejection of prescriptions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['prescription_id'], $_POST['action'])) {
    $prescription_id = $_POST['prescription_id'];
    $action = $_POST['action'];

    if ($action === 'approve') {
        $new_status = 'approved';
    } elseif ($action === 'reject') {
        $new_status = 'rejected';
    } else {
        die("Invalid operation");
    }

    $stmt = $conn->prepare("UPDATE prescription SET p_status = ? WHERE prescription_id = ?");
    $stmt->execute([$new_status, $prescription_id]);

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Query pending prescriptions to ensure they are from the current pharmacy's orders
$sql = "
    SELECT 
        pr.prescription_id,
        pr.u_id,
        u.u_name,
        pr.image_url,
        pr.expiry_date,
        pr.p_status,
        od.product_id,
        p.product_name,
        p.description,
        od.quantity,
        o.order_time
    FROM prescription pr
    JOIN user u ON pr.u_id = u.u_id
    JOIN order_detail od ON pr.prescription_id = od.prescription_id
    JOIN product p ON od.product_id = p.product_id
    JOIN `order` o ON od.order_id = o.order_id
    JOIN inventory i ON i.product_id = p.product_id
    WHERE pr.p_status = 'pending' AND i.pharmacy_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->execute([$pharmacy_id]);
$prescriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Prescription Review - Pharmacy System</title>
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
            text-align: center;
            color: #333;
        }

        table {
            width: 90%;
            margin: 20px auto;
            border-collapse: collapse;
            background: #fff;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #007bff;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .approve-btn {
            background-color: #28a745;
            padding: 6px 12px;
            border: none;
            color: white;
            border-radius: 4px;
            margin-right: 5px;
        }

        .approve-btn:hover {
            background-color: #218838;
        }

        .reject-btn {
            background-color: #dc3545;
            padding: 6px 12px;
            border: none;
            color: white;
            border-radius: 4px;
        }

        .reject-btn:hover {
            background-color: #c82333;
        }

        .action-form {
            display: inline;
        }

        .back-btn {
            display: block;
            text-align: center;
            margin: 20px auto;
            background-color: #6c757d;
            padding: 10px 20px;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            width: 200px;
        }

        .back-btn:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <h2>Pending Prescription List</h2>
    <table>
        <thead>
            <tr>
                <th>User Name</th>
                <th>Product Name</th>
                <th>Quantity</th>
                <th>Prescription Expiry Date</th>
                <th>Prescription Image</th>
                <th>Order Time</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($prescriptions)): ?>
                <tr><td colspan="7">No pending prescriptions</td></tr>
            <?php else: ?>
                <?php foreach ($prescriptions as $prescription): ?>
                    <tr>
                        <td><?= htmlspecialchars($prescription['u_name']) ?></td>
                        <td><?= htmlspecialchars($prescription['product_name']) ?></td>
                        <td><?= (int)$prescription['quantity'] ?></td>
                        <td><?= htmlspecialchars($prescription['expiry_date']) ?></td>
                        <td><img src="<?= htmlspecialchars($prescription['image_url']) ?>" alt="Prescription Image" style="max-height: 100px;"></td>
                        <td><?= htmlspecialchars($prescription['order_time']) ?></td>
                        <td>
                        <form method="POST" action="approve_prescription.php" class="action-form" onsubmit="return confirm('Approve this prescription?');">
                            <input type="hidden" name="prescription_id" value="<?= $prescription['prescription_id'] ?>">
                            <input type="hidden" name="action" value="approve">
                            <button type="submit" class="approve-btn">Approve</button>
                        </form>
                        <form method="POST" action="approve_prescription.php" class="action-form" onsubmit="return confirm('Reject this prescription?');">
                            <input type="hidden" name="prescription_id" value="<?= $prescription['prescription_id'] ?>">
                            <input type="hidden" name="action" value="reject">
                            <button type="submit" class="reject-btn">Reject</button>
                        </form>
                        </td>
                    </tr>
                <?php endforeach ?>
            <?php endif ?>
        </tbody>
    </table>

    <a href="pharmacy_home.php" class="back-btn">Back to Dashboard</a>
</body>
</html>

