<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'Tenant') {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tenant_id = $_SESSION['user_id'];
    $prop_id = intval($_POST['prop_id']);
    $room_id = intval($_POST['room_id']);

    // Check if already applied (Double check for security)
    $check = $conn->query("SELECT * FROM applications WHERE tenant_id = $tenant_id AND property_id = $prop_id AND status != 'Rejected'");
    
    if ($check->num_rows == 0) {
        $stmt = $conn->prepare("INSERT INTO applications (tenant_id, property_id, room_id, status) VALUES (?, ?, ?, 'Pending')");
        $stmt->bind_param("iii", $tenant_id, $prop_id, $room_id);
        
        if ($stmt->execute()) {
            header("Location: dashboard_tenant.php?msg=Applied Successfully");
        } else {
            header("Location: property_details.php?id=$prop_id&err=Failed");
        }
    } else {
        header("Location: dashboard_tenant.php?msg=Already Applied");
    }
}
?>