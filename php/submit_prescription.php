<?php
session_start();
include "connectdb.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'];
    $pharmacy_id = $_POST['pharmacy_id'];
    $quantity = $_POST['quantity'];
    $u_id = $_SESSION['u_id'];

    if (isset($_FILES['prescription']) && $_FILES['prescription']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = uniqid() . '_' . basename($_FILES['prescription']['name']);
        $targetFile = $uploadDir . $filename;

        if (move_uploaded_file($_FILES['prescription']['tmp_name'], $targetFile)) {
            $expiry_date = date('Y-m-d', strtotime('+30 days'));

            // Insert into Prescription table
            $stmt = $conn->prepare("INSERT INTO Prescription (u_id, image_url, expiry_date, p_status) VALUES (?, ?, ?, 'pending')");
            $stmt->execute([$u_id, $targetFile, $expiry_date]);

            $prescription_id = $conn->lastInsertId();

            // Check if there is an unsubmitted order (status = 'pending')
            $stmt = $conn->prepare("SELECT order_id FROM `Order` WHERE u_id = ? AND o_status = 'pending' AND pharmacy_id = ?");
            $stmt->execute([$u_id, $pharmacy_id]);
            $existingOrder = $stmt->fetch();

            if ($existingOrder) {
                $order_id = $existingOrder['order_id'];
            } else {
                // Create a new order (status pending, initial total amount is 0)
                $stmt = $conn->prepare("INSERT INTO `Order` (u_id, pharmacy_id, total_amount, o_status, order_time) VALUES (?, ?, 0.00, 'pending', NOW())");
                $stmt->execute([$u_id, $pharmacy_id]);
                $order_id = $conn->lastInsertId();
            }

            // Get the current product price
            $stmt = $conn->prepare("SELECT price FROM Product WHERE product_id = ?");
            $stmt->execute([$product_id]);
            $price = $stmt->fetchColumn();

            // Insert order details
            $stmt = $conn->prepare("INSERT INTO Order_Detail (order_id, product_id, price, quantity, prescription_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$order_id, $product_id, $price, $quantity, $prescription_id]);

            // Update order total amount
            $stmt = $conn->prepare("UPDATE `Order` SET total_amount = total_amount + (? * ?) WHERE order_id = ?");
            $stmt->execute([$price, $quantity, $order_id]);

            echo "Prescription uploaded successfully, product added to pending order, awaiting review.";
        } else {
            echo "Prescription file upload failed.";
        }
    } else {
        echo "Please upload a prescription file.";
    }
}
?>



