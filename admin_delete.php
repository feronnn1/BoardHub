<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'Admin') { header("Location: login.php"); exit(); }

if(isset($_GET['type']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $type = $_GET['type'];
    
    if($type == 'user') {
        // Delete user (Cascading DB rules usually handle the rest, but let's be safe)
        $conn->query("DELETE FROM users WHERE id=$id");
        header("Location: admin_users.php?msg=User deleted");
    } 
    elseif($type == 'property') {
        // Delete property
        $conn->query("DELETE FROM properties WHERE id=$id");
        header("Location: admin_properties.php?msg=Property deleted");
    }
    elseif($type == 'room') {
        // Delete room (Redirects back to admin_rooms.php needs prop_id usually, simplifies to back)
        $conn->query("DELETE FROM room_units WHERE id=$id");
        header("Location: admin_properties.php?msg=Room deleted"); 
    }
}
?>