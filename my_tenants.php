<?php
session_start();
include 'db.php';

// 1. SECURITY
if (!isset($_SESSION['user'])) { header("Location: login.php"); exit(); }
$username = $_SESSION['user'];
$user = $conn->query("SELECT * FROM users WHERE username='$username'")->fetch_assoc();

if ($user['role'] !== 'Landlord') { header("Location: login.php"); exit(); }
$landlord_id = $user['id'];

// 2. CHECK HOUSE
$house = $conn->query("SELECT id FROM properties WHERE landlord_id = $landlord_id LIMIT 1")->fetch_assoc();
if (!$house) { header("Location: dashboard_landlord.php"); exit(); }
$house_id = $house['id'];

// 3. HANDLE ACTIONS
if (isset($_GET['action']) && isset($_GET['id'])) {
    $app_id = intval($_GET['id']);

    if ($_GET['action'] == 'mark_paid') {
        // MARK AS PAID (Update date to today)
        $today = date('Y-m-d');
        $conn->query("UPDATE applications SET last_payment_date='$today' WHERE id=$app_id");
        header("Location: my_tenants.php?msg=paid");
        exit();
    }
    
    if ($_GET['action'] == 'move_out' && isset($_GET['room_id'])) {
        // MOVE OUT LOGIC
        $room_id = intval($_GET['room_id']);
        $conn->query("UPDATE applications SET status='Moved Out' WHERE id=$app_id");
        $conn->query("UPDATE room_units SET occupied_beds = occupied_beds - 1 WHERE id=$room_id AND occupied_beds > 0");
        header("Location: my_tenants.php?msg=moved_out");
        exit();
    }
}

// 4. FETCH ACTIVE TENANTS
$query = "SELECT applications.id as app_id, applications.created_at, applications.last_payment_date,
                 users.first_name, users.last_name, users.phone, users.profile_pic,
                 room_units.room_name, room_units.id as room_id
          FROM applications 
          JOIN users ON applications.tenant_id = users.id 
          JOIN room_units ON applications.room_id = room_units.id
          WHERE applications.property_id = $house_id 
          AND applications.status = 'Approved'
          ORDER BY room_units.room_name ASC";
$tenants = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>My Tenants</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background: #111; color: #e0e0e0; font-family: 'Segoe UI', sans-serif; }
        
        .sidebar { width: 260px; height: 100vh; background: #0a0a0a; position: fixed; top: 0; left: 0; border-right: 1px solid #222; padding: 25px; }
        .main-content { margin-left: 260px; padding: 40px; }
        .nav-link { color: #aaa; padding: 12px 15px; border-radius: 8px; font-weight: bold; margin-bottom: 5px; display: block; text-decoration: none; transition: 0.3s; }
        .nav-link:hover, .nav-link.active { background: #ff9000; color: black; }

        .tenant-card { background: #1c1c1c; border: 1px solid #333; border-radius: 10px; padding: 20px; margin-bottom: 15px; transition: 0.2s; }
        .tenant-card:hover { border-color: #555; }
        .t-img { width: 50px; height: 50px; border-radius: 50%; object-fit: cover; border: 2px solid #ff9000; margin-right: 15px; }
        
        .btn-pay { background: #198754; color: white; border: none; padding: 5px 15px; border-radius: 6px; font-size: 13px; font-weight: bold; text-decoration: none; display: inline-block; }
        .btn-pay:hover { background: #146c43; color: white; }
        
        .btn-kick { color: #dc3545; border: 1px solid #dc3545; padding: 5px 15px; border-radius: 6px; font-size: 13px; text-decoration: none; font-weight: bold; display: inline-block; margin-left: 5px; }
        .btn-kick:hover { background: #dc3545; color: white; }

        .badge-paid { background: rgba(25, 135, 84, 0.2); color: #198754; padding: 5px 10px; border-radius: 4px; font-size: 12px; font-weight: bold; border: 1px solid #198754; }
        .badge-unpaid { background: rgba(220, 53, 69, 0.2); color: #dc3545; padding: 5px 10px; border-radius: 4px; font-size: 12px; font-weight: bold; border: 1px solid #dc3545; }
    </style>
</head>
<body>

<div class="sidebar">
    <h4 class="fw-bold text-white mb-5">Board<span style="color:#ff9000">Hub</span></h4>
    <a href="dashboard_landlord.php" class="nav-link"><i class="bi bi-grid-fill me-2"></i> Dashboard</a>
    <a href="my_tenants.php" class="nav-link active"><i class="bi bi-people-fill me-2"></i> My Tenants</a>
    <a href="edit_room.php?id=<?php echo $house_id; ?>" class="nav-link"><i class="bi bi-house-gear-fill me-2"></i> Manage House</a>
    <a href="profile_setup.php" class="nav-link"><i class="bi bi-person-circle me-2"></i> Profile</a>
    <a href="logout.php" class="nav-link text-danger mt-5" onclick="return confirm('Log out?');"><i class="bi bi-box-arrow-right me-2"></i> Logout</a>
</div>

<div class="main-content">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-white">Active Tenants & Payments</h3>
        <span class="text-secondary small">Current Month: <?php echo date('F Y'); ?></span>
    </div>

    <?php if(isset($_GET['msg'])): ?>
        <?php if($_GET['msg']=='paid'): ?> <div class="alert alert-success">Payment recorded successfully!</div> <?php endif; ?>
        <?php if($_GET['msg']=='moved_out'): ?> <div class="alert alert-warning">Tenant moved out. Bed is available.</div> <?php endif; ?>
    <?php endif; ?>

    <?php if($tenants->num_rows > 0): ?>
        <div class="row">
            <?php while($t = $tenants->fetch_assoc()): 
                $pic = !empty($t['profile_pic']) ? "assets/uploads/" . $t['profile_pic'] : "assets/default.jpg";
                
                // PAYMENT LOGIC
                // Check if last_payment_date matches THIS month and THIS year
                $last_pay = $t['last_payment_date'];
                $is_paid = false;
                if ($last_pay && date('Y-m', strtotime($last_pay)) === date('Y-m')) {
                    $is_paid = true;
                }
            ?>
            <div class="col-md-6">
                <div class="tenant-card">
                    <div class="d-flex align-items-start">
                        <img src="<?php echo $pic; ?>" class="t-img">
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between">
                                <h5 class="text-white fw-bold mb-0"><?php echo htmlspecialchars($t['first_name'] . ' ' . $t['last_name']); ?></h5>
                                
                                <?php if($is_paid): ?>
                                    <span class="badge-paid"><i class="bi bi-check-circle-fill"></i> PAID</span>
                                <?php else: ?>
                                    <span class="badge-unpaid">UNPAID</span>
                                <?php endif; ?>
                            </div>
                            
                            <small class="text-warning fw-bold"><i class="bi bi-house-door-fill"></i> <?php echo htmlspecialchars($t['room_name']); ?></small>
                            <div class="text-muted small mt-1"><i class="bi bi-telephone"></i> <?php echo htmlspecialchars($t['phone']); ?></div>
                            <div class="text-secondary small mt-2" style="font-size: 11px;">
                                Last Payment: <?php echo ($last_pay) ? date("M d, Y", strtotime($last_pay)) : "Never"; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-end mt-3 pt-3 border-top border-secondary">
                        <?php if(!$is_paid): ?>
                            <a href="my_tenants.php?id=<?php echo $t['app_id']; ?>&action=mark_paid" 
                               class="btn-pay" onclick="return confirm('Mark this tenant as PAID for <?php echo date('F'); ?>?')">
                               <i class="bi bi-cash-stack"></i> Mark as Paid
                            </a>
                        <?php else: ?>
                            <button class="btn btn-secondary btn-sm" disabled style="opacity: 0.5; font-size: 12px;">Paid for <?php echo date('M'); ?></button>
                        <?php endif; ?>

                        <a href="my_tenants.php?id=<?php echo $t['app_id']; ?>&room_id=<?php echo $t['room_id']; ?>&action=move_out" 
                           class="btn-kick" 
                           onclick="return confirm('WARNING: Move out this tenant? This frees up a bed.')">
                           Move Out
                        </a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-5 border border-secondary rounded" style="border-style: dashed !important;">
            <h4 class="text-muted">No active tenants.</h4>
            <p class="text-secondary">Accept requests to populate this list.</p>
        </div>
    <?php endif; ?>

</div>

</body>
</html>