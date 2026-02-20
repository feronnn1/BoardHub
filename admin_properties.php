<?php
session_start();
include 'db.php';
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'Admin') { header("Location: login.php"); exit(); }

// --- FUNCTIONAL DELETE SCRIPT ---
if (isset($_GET['delete_id'])) {
    $del_id = intval($_GET['delete_id']);
    
    // First, delete all rooms associated with this property to prevent errors
    $conn->query("DELETE FROM room_units WHERE property_id = $del_id");
    
    // Then, delete the property itself
    $conn->query("DELETE FROM properties WHERE id = $del_id");
    
    // Redirect to clear the URL and refresh
    header("Location: admin_properties.php");
    exit();
}
// --------------------------------

// Fetch Properties with Landlord Name
$sql = "SELECT p.*, u.first_name, u.last_name FROM properties p JOIN users u ON p.landlord_id = u.id";
$props = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Properties</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root { --bg-dark: #0f0f0f; --bg-card: #1a1a1a; --accent-orange: #ff9000; }
        body { background: var(--bg-dark); color: white; font-family: 'Plus Jakarta Sans', sans-serif; overflow-x: hidden; }
        .sidebar { width: 260px; height: 100vh; background: #050505; position: fixed; top: 0; left: 0; border-right: 1px solid #222; padding: 20px; display: flex; flex-direction: column; }
        .nav-label { font-size: 11px; text-transform: uppercase; color: #666; font-weight: 700; margin-bottom: 10px; padding-left: 15px; letter-spacing: 1px; }
        .nav-link { color: #888; padding: 12px 18px; border-radius: 12px; margin-bottom: 5px; display: flex; align-items: center; gap: 14px; font-weight: 500; transition: all 0.2s ease; text-decoration: none; }
        .nav-link:hover, .nav-link.active { background: rgba(255, 144, 0, 0.15); color: var(--accent-orange); }
        .main-content { margin-left: 260px; padding: 40px 50px; }
        
        .prop-card { background: #1a1a1a; border: 1px solid #333; border-radius: 12px; overflow: hidden; margin-bottom: 20px; display: flex; }
        .prop-img { width: 200px; height: 150px; object-fit: cover; }
        .prop-body { padding: 20px; flex-grow: 1; display: flex; justify-content: space-between; align-items: center; }
        .btn-view { background: rgba(255, 144, 0, 0.15); color: var(--accent-orange); border: none; padding: 8px 16px; border-radius: 8px; margin-right: 10px; text-decoration: none; font-size: 14px; font-weight: 600; }
        .btn-delete { background: rgba(220, 53, 69, 0.15); color: #dc3545; border: none; padding: 8px 16px; border-radius: 8px; text-decoration: none; font-size: 14px; font-weight: 600; }
    </style>
</head>
<body>

<div class="sidebar">
    <a href="#" class="nav-link" style="font-size: 24px; font-weight: 800; color: white; margin-bottom: 40px;"><i class="bi bi-shield-lock-fill text-primary-orange" style="color: var(--accent-orange);"></i> Admin</a>
    <div class="nav-label">Main</div>
    <a href="dashboard_admin.php" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <div class="nav-label mt-4">Management</div>
    <a href="admin_users.php?role=Landlord" class="nav-link"><i class="bi bi-person-tie"></i> Landlords</a>
    <a href="admin_users.php?role=Tenant" class="nav-link"><i class="bi bi-people"></i> Tenants</a>
    <a href="admin_properties.php" class="nav-link active"><i class="bi bi-houses"></i> Properties</a>
</div>

<div class="main-content">
    <h2 class="fw-bold mb-4">Manage Properties</h2>
    
    <?php while($p = $props->fetch_assoc()): 
        $imgs = json_decode($p['images'], true);
        $thumb = !empty($imgs) ? "assets/uploads/rooms/".$imgs[0] : "assets/default_room.jpg";
    ?>
    <div class="prop-card">
        <img src="<?php echo $thumb; ?>" class="prop-img">
        <div class="prop-body">
            <div>
                <h4 class="fw-bold m-0"><?php echo htmlspecialchars($p['title']); ?></h4>
                <p class="text-secondary small mb-2"><i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($p['location']); ?></p>
                <div class="small text-secondary">Owner: <span class="text-white"><?php echo htmlspecialchars($p['first_name'].' '.$p['last_name']); ?></span></div>
            </div>
            <div>
                <a href="admin_rooms.php?id=<?php echo $p['id']; ?>" class="btn-view">Manage Rooms</a>
                
                <a href="admin_properties.php?delete_id=<?php echo $p['id']; ?>" class="btn-delete" onclick="return confirm('WARNING: Are you sure you want to delete this property and all its rooms? This action cannot be undone.');">Delete</a>
            </div>
        </div>
    </div>
    <?php endwhile; ?>
</div>

</body>
</html>