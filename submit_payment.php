<?php
session_start();
include 'db.php';

// 1. SECURITY: Ensure user is logged in as Tenant
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'Tenant') {
    header("Location: login.php");
    exit();
}

if (isset($_POST['submit_payment'])) {
    $tenant_id = $_SESSION['user_id'];
    
    // Sanitize inputs
    $amount = floatval($_POST['amount']);
    $payment_date = $_POST['payment_date'];
    
    // 2. INSERT INTO DATABASE
    // FIXED: Removed 'proof_image' column and its placeholder (?)
    $stmt = $conn->prepare("INSERT INTO payments (tenant_id, amount, payment_date, status) VALUES (?, ?, ?, 'Pending')");
    
    // FIXED: Updated types to "ids" (Integer, Double, String) and removed $proof_img variable
    $stmt->bind_param("ids", $tenant_id, $amount, $payment_date);

    if ($stmt->execute()) {
        header("Location: dashboard_tenant.php?msg=Payment Submitted Successfully");
    } else {
        header("Location: dashboard_tenant.php?error=Failed to submit payment");
    }
    
    $stmt->close();
    $conn->close();
} else {
    header("Location: dashboard_tenant.php");
    exit();
}
?>