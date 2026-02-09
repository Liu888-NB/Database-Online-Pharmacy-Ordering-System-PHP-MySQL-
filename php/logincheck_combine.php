<?php
session_start();
include 'connectdb.php'; // database connection

if (isset($_POST['username']) && isset($_POST['password']) && isset($_POST['role'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    if ($role === 'user') {
        $stmt = $conn->prepare("SELECT u_id, u_name, password FROM User WHERE u_name = :username");
    } elseif ($role === 'pharmacy') {
        $stmt = $conn->prepare("SELECT pharmacy_id, pharmacy_name, p_password FROM Pharmacy WHERE pharmacy_name = :username");
    } else {
        header("Location: login.php?error=invalid_role");
        exit();
    }

    $stmt->bindValue(':username', $username, PDO::PARAM_STR);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $hash = ($role === 'user') ? $row['password'] : $row['p_password'];
        if (password_verify($password, $hash)) {
            if ($role === 'user') {
                $_SESSION['username'] = $row['u_name'];
                $_SESSION['u_id'] = $row['u_id'];
                header("Location: user_home.php");
            } else {
                $_SESSION['pharmacy_name'] = $row['pharmacy_name'];
                $_SESSION['pharmacy_id'] = $row['pharmacy_id'];
                header("Location: pharmacy_home.php");
            }
            exit();
        } else {
            header("Location: login.php?error=invalid_password");
            exit();
        }
    } else {
        header("Location: login.php?error=user_not_found");
        exit();
    }
} else {
    header("Location: login.php?error=missing_credentials");
    exit();
}

$stmt = null;
$conn = null;
?>

