<?php
session_start();
include 'db.php';

// 1. Check if logged in as Tenant
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'Tenant') {
    header("Location: login.php");
    exit();
}

if (isset($_POST['submit_application'])) {
    $tenant_id = $_SESSION['user_id'];
    $property_id = intval($_POST['property_id']);
    $room_id = intval($_POST['room_id']);
    $move_in_date = $_POST['move_in_date'];
    $message = trim($_POST['message']);
    $status = 'Pending';

    // 2. Check for existing pending application for THIS room
    $check = $conn->prepare("SELECT id FROM applications WHERE tenant_id = ? AND room_id = ? AND status = 'Pending'");
    $check->bind_param("ii", $tenant_id, $room_id);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        header("Location: dashboard_tenant.php?error=You already applied for this room.");
        exit();
    }

    // 3. Insert Application
    $stmt = $conn->prepare("INSERT INTO applications (tenant_id, property_id, room_id, status, message, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("iiiss", $tenant_id, $property_id, $room_id, $status, $message);

    if ($stmt->execute()) {
        header("Location: dashboard_tenant.php?msg=Application Submitted");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
} else {
    header("Location: listings.php");
    exit();
}
?>