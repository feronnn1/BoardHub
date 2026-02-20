<?php
session_start();
include 'db.php';

// SECURITY
if (!isset($_SESSION['user'])) { header("Location: login.php"); exit(); }
if (!isset($_GET['id']) || !isset($_GET['action'])) { header("Location: dashboard_landlord.php"); exit(); }

$app_id = intval($_GET['id']);
$action = $_GET['action'];

// FETCH APPLICATION DETAILS (Need to know which room to update)
$app = $conn->query("SELECT * FROM applications WHERE id=$app_id")->fetch_assoc();
$room_id = $app['room_id'];

if ($action === 'approve') {
    // 1. CHECK IF ROOM IS FULL FIRST
    $room = $conn->query("SELECT * FROM room_units WHERE id=$room_id")->fetch_assoc();
    if ($room['occupied_beds'] >= $room['total_beds']) {
        // Room is full! Cannot approve.
        header("Location: dashboard_landlord.php?err=Room is already full");
        exit();
    }

    // 2. INCREMENT OCCUPIED BEDS
    $conn->query("UPDATE room_units SET occupied_beds = occupied_beds + 1 WHERE id=$room_id");

    // 3. SET STATUS TO APPROVED
    $stmt = $conn->prepare("UPDATE applications SET status='Approved' WHERE id=?");
} else {
    // REJECT
    $stmt = $conn->prepare("UPDATE applications SET status='Rejected' WHERE id=?");
}

$stmt->bind_param("i", $app_id);

if ($stmt->execute()) {
    header("Location: dashboard_landlord.php?msg=processed");
} else {
    echo "Error updating application.";
}
?>