<?php
session_start();
include 'db.php';

// 1. Check Login & Role
if (!isset($_SESSION['user'])) { header("Location: login.php"); exit(); }
$username = $_SESSION['user'];

$user_q = $conn->query("SELECT * FROM users WHERE username='$username'");
$user = $user_q->fetch_assoc();

if ($user['role'] !== 'Admin') { header("Location: login.php"); exit(); }

// 2. Handle Admin Delete
if (isset($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    $conn->query("DELETE FROM properties WHERE id=$del_id");
    header("Location: admin_listings.php?msg=deleted");
    exit();
}

// 3. Fetch ALL Listings
$query = "SELECT properties.*, users.first_name, users.last_name 
          FROM properties 
          JOIN users ON properties.landlord_id = users.id 
          ORDER BY properties.created_at DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage All Listings - Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background: #111; color: #e0e0e0; font-family: 'Arial', sans-serif; padding-bottom: 50px; }
        .container-custom { max-width: 900px; margin: 0 auto; padding-top: 40px; }
        
        .list-card { 
            background: #1c1c1c; border: 1px solid #333; padding: 20px; 
            margin-bottom: 20px; border-radius: 10px; display: flex; 
            gap: 20px; align-items: center; transition: 0.2s;
        }
        .list-card:hover { border-color: #ff4d4d; transform: translateY(-2px); }
        
        .thumb { width: 120px; height: 100px; object-fit: cover; border-radius: 8px; background: #333; }
        
        .landlord-badge { font-size: 11px; background: #333; padding: 2px 8px; border-radius: 4px; color: #aaa; }
        
        .btn-edit { 
            background: #004085; color: white; border: 1px solid #004085; 
            padding: 8px 15px; border-radius: 6px; text-decoration: none; font-size: 14px; margin-right: 5px;
        }
        .btn-edit:hover { background: #0056b3; border-color: #0056b3; }

        .btn-del { 
            background: rgba(255, 77, 77, 0.1); color: #ff4d4d; border: 1px solid #ff4d4d; 
            padding: 8px 15px; border-radius: 6px; text-decoration: none; font-size: 14px;
        }
        .btn-del:hover { background: #ff4d4d; color: white; }
    </style>
</head>
<body>

<div class="container-custom px-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-white mb-0">Manage Listings</h2>
            <p class="text-muted m-0 small">Admin Moderation Panel</p>
        </div>
        <a href="dashboard_admin.php" class="btn btn-outline-secondary btn-sm">&larr; Dashboard</a>
    </div>

    <?php if(isset($_GET['msg'])): ?>
        <div class="alert alert-warning py-2">Listing deleted by Admin.</div>
    <?php endif; ?>

    <?php if($result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
            <?php 
                $images = json_decode($row['images'], true);
                $thumb = !empty($images) ? "assets/uploads/rooms/" . $images[0] : "assets/default_room.jpg";
            ?>
            <div class="list-card">
                <img src="<?php echo $thumb; ?>" class="thumb">
                
                <div class="flex-grow-1">
                    <span class="landlord-badge"><i class="bi bi-person"></i> <?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></span>
                    <h5 class="text-white fw-bold mt-1 mb-0"><?php echo htmlspecialchars($row['title']); ?></h5>
                    <p class="text-muted m-0 small"><?php echo htmlspecialchars($row['location']); ?></p>
                    <div class="text-warning fw-bold">₱<?php echo number_format($row['price']); ?></div>
                </div>
                
                <div class="d-flex align-items-center">
                    <a href="edit_room.php?id=<?php echo $row['id']; ?>" class="btn-edit">
                        <i class="bi bi-pencil-square"></i> Edit
                    </a>

                    <a href="admin_listings.php?delete=<?php echo $row['id']; ?>" class="btn-del" onclick="return confirm('ADMIN WARNING: Permanently delete this listing?')">
                        <i class="bi bi-trash"></i> Delete
                    </a>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p class="text-center text-muted mt-5">No listings in the system.</p>
    <?php endif; ?>

</div>
</body>
</html>