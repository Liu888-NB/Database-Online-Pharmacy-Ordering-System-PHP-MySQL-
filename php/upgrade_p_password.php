<?php
// Include database connection
require 'connectdb.php';
set_time_limit(0); // Remove execution time limit

try {
    // Query all pharmacies
    $stmt = $conn->query("SELECT pharmacy_id, p_password FROM pharmacy");
    $pharmacies = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($pharmacies as $pharmacy) {
        $id = $pharmacy['pharmacy_id'];
        $plainPassword = $pharmacy['p_password'];

        // If the password does not start with $2y$ (i.e., not yet encrypted), then encrypt it
        if (strpos($plainPassword, '$2y$') !== 0) {
            $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);

            // Update the encrypted password back to the pharmacy table
            $updateStmt = $conn->prepare("UPDATE pharmacy SET p_password = ? WHERE pharmacy_id = ?");
            $updateStmt->execute([$hashedPassword, $id]);

            echo "✅ Pharmacy ID {$id} password has been encrypted<br>";
        } else {
            echo "✔ Pharmacy ID {$id} password is already in encrypted format, skipping<br>";
        }
    }
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage();
}

$conn = null;
?>

