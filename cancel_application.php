<?php
session_start();
include 'db.php';

// Security check
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'Tenant') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $app_id = intval($_GET['id']);
    $user_id = $_SESSION['user_id'];

    // STRICT DELETE: Only delete if it belongs to the logged-in user AND is still Pending
    $stmt = $conn->prepare("DELETE FROM applications WHERE id = ? AND tenant_id = ? AND status = 'Pending'");
    $stmt->bind_param("ii", $app_id, $user_id);
    
    if ($stmt->execute()) {
        header("Location: dashboard_tenant.php?msg=Request Cancelled");
    } else {
        header("Location: dashboard_tenant.php?error=Could not cancel request");
    }
} else {
    header("Location: dashboard_tenant.php");
}
?>