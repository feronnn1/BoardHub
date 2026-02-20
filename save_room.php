<?php
session_start();
include 'db.php';

if (!isset($_POST['post_room'])) { header("Location: dashboard_landlord.php"); exit(); }
$landlord_id = $conn->query("SELECT id FROM users WHERE username='$_SESSION[user]'")->fetch_assoc()['id'];

// 1. SAVE HOUSE
$title = $_POST['title'];
$wifi = $_POST['wifi_type'];
$water = $_POST['water_type'];
$price = $_POST['price'] ?: 0;
$price_shared = $_POST['price_shared'] ?: 0;

// Convert Checkbox Arrays to String
$inclusions = isset($_POST['inclusions']) ? implode(", ", $_POST['inclusions']) : "None";
$paid_addons = isset($_POST['paid_addons']) ? implode(", ", $_POST['paid_addons']) : "None";

// Main House Images
$house_imgs = [];
$target_dir = "assets/uploads/rooms/";
if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }

if (!empty($_FILES['room_images']['name'][0])) {
    foreach ($_FILES['room_images']['name'] as $k => $v) {
        $fn = time() . "_house_" . basename($_FILES['room_images']['name'][$k]);
        move_uploaded_file($_FILES['room_images']['tmp_name'][$k], $target_dir . $fn);
        $house_imgs[] = $fn;
    }
}
$images_json = json_encode($house_imgs);

// INSERT QUERY
$stmt = $conn->prepare("INSERT INTO properties (landlord_id, title, location, description, wifi_type, water_type, price, price_shared, inclusions, paid_addons, images) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("issssdddsss", $landlord_id, $title, $_POST['location'], $_POST['description'], $wifi, $water, $price, $price_shared, $inclusions, $paid_addons, $images_json);
$stmt->execute();
$prop_id = $conn->insert_id;

// 2. SAVE ROOMS
$names = $_POST['room_names'];
$beds = $_POST['room_beds'];
$occ = $_POST['room_occupied'];

for ($i = 0; $i < count($names); $i++) {
    $r_img_name = "default_room.jpg";
    if (!empty($_FILES['room_specific_img']['name'][$i])) {
        $r_fn = time() . "_room_" . $i . "_" . basename($_FILES['room_specific_img']['name'][$i]);
        if (move_uploaded_file($_FILES['room_specific_img']['tmp_name'][$i], $target_dir . $r_fn)) {
            $r_img_name = $r_fn;
        }
    }
    $r_stmt = $conn->prepare("INSERT INTO room_units (property_id, room_name, total_beds, occupied_beds, room_image) VALUES (?, ?, ?, ?, ?)");
    $r_stmt->bind_param("isiis", $prop_id, $names[$i], $beds[$i], $occ[$i], $r_img_name);
    $r_stmt->execute();
}

header("Location: dashboard_landlord.php?msg=Posted");
?>