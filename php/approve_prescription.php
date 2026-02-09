<?php
session_start();
require 'connectdb.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['prescription_id'], $_POST['action'])) {
        exit("Lack Attributes");
    }

    $prescription_id = $_POST['prescription_id'];
    $action = $_POST['action'];

    if ($action === 'approve') {
        $status = 'valid';
    } elseif ($action === 'reject') {
        $status = 'expired';
    } else {
        exit("Illegal Operations");
    }

    $stmt = $conn->prepare("UPDATE Prescription SET p_status = ? WHERE prescription_id = ?");
    $stmt->execute([$status, $prescription_id]);

    header("Location: pending_prescription.php");
    exit;
}
?>

