<?php
// connect to the database
require 'connectdb.php';
set_time_limit(0); // remove time limitation

try {
    // Query all users
    $stmt = $conn->query("SELECT u_id, password FROM user");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($users as $user) {
        $id = $user['u_id'];
        $plainPassword = $user['password'];

        // If the password does not start with $2y$ (i.e., it has not been encrypted yet), encrypt it.
        if (strpos($plainPassword, '$2y$') !== 0) {
            $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);

            // Update to dataabse
            $updateStmt = $conn->prepare("UPDATE user SET password = ? WHERE u_id = ?");
            $updateStmt->execute([$hashedPassword, $id]);

            echo "✅ User ID {$id} Has been encrpyted<br>";
        } else {
            echo "✔ User ID {$id} Already encrpyted，skip<br>";
        }
    }
} catch (PDOException $e) {
    echo "❌ database error：" . $e->getMessage();
}

$conn = null;
?>
