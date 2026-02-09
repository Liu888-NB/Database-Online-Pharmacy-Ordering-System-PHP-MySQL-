<?php
require 'connectdb.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $role = $_POST['role'] ?? '';
    $password = $_POST['password'] ?? '';
    $conf_password = $_POST['conf_password'] ?? '';

    if ($password !== $conf_password) {
        echo "Passwords do not match!";
        exit;
    }

    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    try {
        $conn->beginTransaction();

        if ($role === "user") {
            $u_name = $_POST['u_name'] ?? '';
            $phone = $_POST['phone'] ?? '';

            $stmt = $conn->prepare("INSERT INTO User (u_name, password, phone) VALUES (?, ?, ?)");
            $stmt->execute([$u_name, $hashed_password, $phone]);

        } elseif ($role === "pharmacy") {
            $pharmacy_name = $_POST['pharmacy_name'] ?? '';
            $p_address = $_POST['pharmacy_address'] ?? '';
            $contact_phone = $_POST['contact_number'] ?? '';

            $stmt = $conn->prepare("INSERT INTO Pharmacy (pharmacy_name, p_password, p_address, contact_phone) VALUES (?, ?, ?, ?)");
            $stmt->execute([$pharmacy_name, $hashed_password, $p_address, $contact_phone]);

        } else {
            echo "Invalid role selected.";
            exit;
        }

        $conn->commit();

        if ($stmt->rowCount() > 0) {
            header("Location: login.php");
            exit;
        } else {
            echo "Registration failed.";
        }

    } catch (PDOException $e) {
        $conn->rollBack();
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}
?>


