<?php
session_start();
include 'db.php';

// 1. SECURITY: Only Landlords
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'Landlord') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id']) && isset($_GET['action'])) {
    $pay_id = intval($_GET['id']);
    $action = $_GET['action'];
    
    // Determine status
    $new_status = ($action === 'confirm') ? 'Confirmed' : 'Rejected';

    // 2. UPDATE DATABASE
    $stmt = $conn->prepare("UPDATE payments SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $pay_id);
    
    if ($stmt->execute()) {
        header("Location: dashboard_landlord.php?msg=Payment updated");
    } else {
        header("Location: dashboard_landlord.php?error=Error updating");
    }
    
    $stmt->close();
} else {
    header("Location: dashboard_landlord.php");
    exit();
}
?>