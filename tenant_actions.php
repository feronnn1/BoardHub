<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'Landlord') { header("Location: login.php"); exit(); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $action = $_POST['action'];
    $app_id = intval($_POST['app_id']); // This is the application ID (Tenant record)
    
    // 1. RECORD PAYMENT
    if ($action === 'pay') {
        $amount = floatval($_POST['amount']);
        $next_due = $_POST['next_due_date'];
        $prop_id = intval($_POST['property_id']);
        
        // Get Tenant ID from App ID
        $app = $conn->query("SELECT tenant_id FROM applications WHERE id=$app_id")->fetch_assoc();
        $tenant_id = $app['tenant_id'];

        // Insert into Payments History
        $stmt = $conn->prepare("INSERT INTO payments (tenant_id, property_id, amount, payment_date, next_due_date) VALUES (?, ?, ?, NOW(), ?)");
        $stmt->bind_param("iids", $tenant_id, $prop_id, $amount, $next_due);
        $stmt->execute();

        // Update Tenant's Next Due Date
        $conn->query("UPDATE applications SET next_due_date = '$next_due' WHERE id = $app_id");
        
        header("Location: manage_rooms.php?msg=Payment Recorded");
    }

    // 2. JUST UPDATE DUE DATE (Customizable)
    if ($action === 'update_date') {
        $new_date = $_POST['new_due_date'];
        $conn->query("UPDATE applications SET next_due_date = '$new_date' WHERE id = $app_id");
        header("Location: manage_rooms.php?msg=Date Updated");
    }

    // 3. MOVE OUT TENANT
    if ($action === 'move_out') {
        // First, find which room they are in to decrease count
        $app_q = $conn->query("SELECT room_id FROM applications WHERE id = $app_id");
        $app = $app_q->fetch_assoc();
        $room_id = $app['room_id'];

        // Update Application Status
        $conn->query("UPDATE applications SET status = 'Moved Out' WHERE id = $app_id");

        // Decrease Room Occupancy
        $conn->query("UPDATE room_units SET occupied_beds = occupied_beds - 1 WHERE id = $room_id AND occupied_beds > 0");

        header("Location: manage_rooms.php?msg=Tenant Moved Out");
    }
}
?>