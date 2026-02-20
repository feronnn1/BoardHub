<?php
session_start();
include 'db.php';

// 1. SECURITY CHECK
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'Landlord') { header("Location: login.php"); exit(); }
$landlord_id = $_SESSION['user_id'];

// 2. FETCH PROPERTY & COMMON IMAGES
$prop_q = $conn->query("SELECT id, images FROM properties WHERE landlord_id = $landlord_id LIMIT 1");
$prop = $prop_q->fetch_assoc();
$prop_id = $prop['id'] ?? 0;
$common_images = !empty($prop['images']) ? json_decode($prop['images'], true) : [];

// 3. FETCH ROOMS
$rooms_q = $conn->query("SELECT * FROM room_units WHERE property_id = $prop_id");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Rooms</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root { --bg-dark: #0f0f0f; --bg-card: #1a1a1a; --accent-orange: #ff9000; --danger: #dc3545; --success: #198754; }
        body { background: var(--bg-dark); color: white; font-family: 'Plus Jakarta Sans', sans-serif; }
        
        /* SIDEBAR */
        .sidebar { width: 250px; height: 100vh; background: #050505; position: fixed; top: 0; left: 0; border-right: 1px solid #222; padding: 20px; display: flex; flex-direction: column; }
        .brand { font-size: 20px; font-weight: 800; margin-bottom: 40px; color: white; text-decoration: none; padding-left: 10px; }
        .nav-link { color: #888; padding: 12px 15px; border-radius: 10px; margin-bottom: 5px; display: flex; align-items: center; gap: 12px; text-decoration: none; font-weight: 500; transition: 0.2s; }
        .nav-link:hover, .nav-link.active { background: rgba(255, 144, 0, 0.1); color: var(--accent-orange); }
        .main-content { margin-left: 250px; padding: 40px; }

        /* ROOM WIDGET (3-COLUMN LAYOUT) */
        .room-widget { background: var(--bg-card); border: 1px solid #333; border-radius: 16px; margin-bottom: 25px; overflow: hidden; display: flex; }
        
        /* COLUMN 1: VISUALS */
        .rw-visual { width: 280px; position: relative; background: #000; flex-shrink: 0; }
        .rw-img { width: 100%; height: 100%; object-fit: cover; opacity: 0.8; transition: 0.3s; }
        .rw-overlay-title { position: absolute; top: 15px; left: 15px; text-shadow: 0 2px 10px black; pointer-events: none; }
        .room-name-overlay { font-size: 18px; font-weight: 800; margin: 0; }
        
        /* GALLERY TRIGGER ICON */
        .gallery-trigger { 
            position: absolute; bottom: 15px; right: 15px; 
            background: rgba(0,0,0,0.6); color: white; 
            width: 40px; height: 40px; border-radius: 50%; 
            display: flex; align-items: center; justify-content: center; 
            border: 1px solid rgba(255,255,255,0.3); 
            cursor: pointer; transition: 0.2s; backdrop-filter: blur(4px); 
        }
        .gallery-trigger:hover { background: var(--accent-orange); color: black; transform: scale(1.1); }

        /* RIGHT CONTENT CONTAINER */
        .rw-content { flex-grow: 1; padding: 25px; display: flex; flex-direction: column; }
        
        /* OCCUPANCY STATS */
        .occ-stats { display: flex; gap: 20px; margin-bottom: 20px; border-bottom: 1px solid #333; padding-bottom: 20px; }
        .occ-item { display: flex; flex-direction: column; }
        .occ-label { font-size: 11px; text-transform: uppercase; color: #888; font-weight: 700; letter-spacing: 0.5px; }
        .occ-val { font-size: 18px; font-weight: 700; }
        .text-avail { color: #198754; } .text-occ { color: #ffc107; }

        /* TENANT TABLE */
        .custom-table { width: 100%; border-collapse: collapse; }
        .custom-table th { text-align: left; font-size: 12px; text-transform: uppercase; color: #666; padding-bottom: 10px; font-weight: 700; }
        .custom-table td { padding: 12px 10px 12px 0; border-top: 1px solid #2a2a2a; color: #ddd; font-size: 14px; vertical-align: middle; }
        
        .t-profile { display: flex; align-items: center; gap: 10px; }
        .t-avatar { width: 32px; height: 32px; border-radius: 50%; object-fit: cover; border: 1px solid #444; }

        /* BADGES & ACTION BUTTONS */
        .badge-status { font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 6px; }
        .bg-paid { background: rgba(25, 135, 84, 0.2); color: #198754; }
        .bg-late { background: rgba(220, 53, 69, 0.2); color: #dc3545; }
        
        .btn-icon-action { background: #252525; border: 1px solid #333; color: white; width: 32px; height: 32px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; transition: 0.2s; cursor: pointer; text-decoration: none; }
        .btn-icon-action:hover { background: #333; border-color: #555; }
        .btn-pay { color: var(--success); border-color: rgba(25, 135, 84, 0.3); }
        .btn-pay:hover { background: var(--success); color: white; }
        .btn-moveout { color: var(--danger); border-color: rgba(220, 53, 69, 0.3); }
        .btn-moveout:hover { background: var(--danger); color: white; }

        .date-editable { cursor: pointer; border-bottom: 1px dashed #666; transition: 0.2s; }
        .date-editable:hover { color: var(--accent-orange); border-bottom-color: var(--accent-orange); }

        /* MODAL STYLING */
        .modal-content { background: #222; color: white; border: 1px solid #444; }
        .modal-header { border-bottom: 1px solid #333; }
        .modal-footer { border-top: 1px solid #333; }
        .form-control { background: #111; border: 1px solid #444; color: white; }
        .form-control:focus { background: #111; color: white; border-color: var(--accent-orange); box-shadow: none; }
    </style>
</head>
<body>

<div class="sidebar">
    <a href="#" class="brand">Board<span class="text-warning">Hub</span></a>
    
    <a href="dashboard_landlord.php" class="nav-link"><i class="bi bi-grid-fill"></i> Dashboard</a>
    <a href="edit_room.php?id=<?php echo $prop_id; ?>" class="nav-link"><i class="bi bi-house-gear-fill"></i> Manage House</a>
    <a href="manage_rooms.php" class="nav-link active"><i class="bi bi-door-open-fill"></i> Manage Rooms</a>
    <a href="profile_setup.php" class="nav-link"><i class="bi bi-person-circle"></i> Profile</a>
    
    <div style="margin-top: auto;">
        <a href="logout.php" class="nav-link text-danger"><i class="bi bi-box-arrow-right"></i> Logout</a>
    </div>
</div>

<div class="main-content">
    <h3 class="fw-bold mb-4">Manage Rooms</h3>
    <?php if(isset($_GET['msg'])): ?><div class="alert alert-success bg-opacity-25 bg-success text-white border-0"><?php echo htmlspecialchars($_GET['msg']); ?></div><?php endif; ?>

    <?php if($rooms_q->num_rows > 0): ?>
        <?php while($room = $rooms_q->fetch_assoc()): 
            $rid = $room['id'];
            $room_img = !empty($room['room_image']) ? "assets/uploads/rooms/" . $room['room_image'] : "assets/default_room.jpg";
            $vacant = $room['total_beds'] - $room['occupied_beds'];
            
            // Prepare Gallery Images
            $gallery = array_merge([$room['room_image']], $common_images);
            $gallery_json = htmlspecialchars(json_encode($gallery));

            // Fetch Approved Tenants
            $tenants = $conn->query("
                SELECT applications.id as app_id, applications.next_due_date, users.first_name, users.last_name, users.phone, users.profile_pic, applications.created_at as start_date 
                FROM applications 
                JOIN users ON applications.tenant_id = users.id 
                WHERE applications.room_id = $rid AND applications.status = 'Approved'");
        ?>
        
        <div class="room-widget">
            <div class="rw-visual">
                <img src="<?php echo $room_img; ?>" class="rw-img">
                <div class="rw-overlay-title">
                    <h5 class="room-name-overlay"><?php echo $room['room_name']; ?></h5>
                </div>
                <div class="gallery-trigger" onclick="openGallery('<?php echo $room['room_name']; ?>', <?php echo $gallery_json; ?>)">
                    <i class="bi bi-images"></i>
                </div>
            </div>

            <div class="rw-content">
                <div class="occ-stats">
                    <div class="occ-item">
                        <span class="occ-label">Occupied</span>
                        <span class="occ-val text-occ"><?php echo $room['occupied_beds']; ?></span>
                    </div>
                    <div class="occ-item">
                        <span class="occ-label">Available</span>
                        <span class="occ-val text-avail"><?php echo $vacant; ?></span>
                    </div>
                </div>

                <?php if($tenants->num_rows > 0): ?>
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>Tenant</th>
                                <th>Contact</th>
                                <th>Due Date</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($t = $tenants->fetch_assoc()): 
                                $t_pic = !empty($t['profile_pic']) ? "assets/uploads/" . $t['profile_pic'] : "assets/default.jpg";
                                
                                // Logic for Due Date
                                if (!empty($t['next_due_date'])) {
                                    $due_date = $t['next_due_date'];
                                } else {
                                    $due_date = date('Y-m-d', strtotime($t['start_date'] . ' +1 month'));
                                }

                                $is_late = (strtotime($due_date) < time());
                                $due_display = date("M d, Y", strtotime($due_date));
                                
                                $status_badge = $is_late 
                                    ? '<span class="badge-status bg-late">Overdue</span>' 
                                    : '<span class="badge-status bg-paid">Paid</span>';
                            ?>
                            <tr>
                                <td>
                                    <div class="t-profile">
                                        <img src="<?php echo $t_pic; ?>" class="t-avatar">
                                        <span><?php echo $t['first_name'] . ' ' . $t['last_name']; ?></span>
                                    </div>
                                </td>
                                <td><?php echo $t['phone']; ?></td>
                                <td>
                                    <span class="date-editable" onclick="openDateModal(<?php echo $t['app_id']; ?>, '<?php echo $due_date; ?>')">
                                        <?php echo $due_display; ?> <i class="bi bi-pencil-fill" style="font-size: 10px; margin-left: 4px;"></i>
                                    </span>
                                </td>
                                <td><?php echo $status_badge; ?></td>
                                <td class="text-end">
                                    <button class="btn-icon-action btn-pay" title="Confirm Payment" onclick="openPayModal(<?php echo $t['app_id']; ?>, '<?php echo $due_date; ?>')"><i class="bi bi-cash-stack"></i></button>
                                    <button class="btn-icon-action btn-moveout" title="Move Out" onclick="openMoveOutModal(<?php echo $t['app_id']; ?>, '<?php echo $t['first_name']; ?>')"><i class="bi bi-box-arrow-right"></i></button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="text-center py-4 text-secondary fst-italic">No tenants here yet.</div>
                <?php endif; ?>
            </div>
        </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p class="text-secondary">No rooms added yet. <a href="edit_room.php?id=<?php echo $prop_id; ?>" class="text-warning">Add Room</a></p>
    <?php endif; ?>
</div>

<div class="modal fade" id="galleryModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content bg-dark">
            <div class="modal-body p-0">
                <div id="carouselExample" class="carousel slide">
                    <div class="carousel-inner" id="galleryInner"></div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#carouselExample" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button>
                    <button class="carousel-control-next" type="button" data-bs-target="#carouselExample" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="payModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="tenant_actions.php" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Payment</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="action" value="pay">
                <input type="hidden" name="app_id" id="payAppId">
                <input type="hidden" name="property_id" value="<?php echo $prop_id; ?>">
                
                <div class="mb-3">
                    <label class="form-label text-secondary small">Amount Paid</label>
                    <input type="number" name="amount" class="form-control" required placeholder="0.00">
                </div>
                <div class="mb-3">
                    <label class="form-label text-secondary small">Next Due Date</label>
                    <input type="date" name="next_due_date" id="payNextDate" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success w-100">Record Payment</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="dateModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="tenant_actions.php" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Change Due Date</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="action" value="update_date">
                <input type="hidden" name="app_id" id="dateAppId">
                
                <label class="form-label text-secondary small">New Due Date</label>
                <input type="date" name="new_due_date" id="dateInput" class="form-control" required>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary w-100">Update Date</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="moveOutModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="tenant_actions.php" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">Move Out Tenant?</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="action" value="move_out">
                <input type="hidden" name="app_id" id="moveAppId">
                <p>Are you sure <strong id="moveTenantName"></strong> is moving out?</p>
                <p class="small text-secondary">This will free up a bed in this room.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger">Confirm Move Out</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function openGallery(roomName, images) {
        const container = document.getElementById('galleryInner');
        container.innerHTML = '';
        if(!images || images.length === 0) images = ['default_room.jpg'];
        images.forEach((img, index) => {
            if(!img) img = 'default_room.jpg';
            const src = img.includes('/') ? img : 'assets/uploads/rooms/' + img;
            container.insertAdjacentHTML('beforeend', `<div class="carousel-item ${index === 0 ? 'active' : ''}"><img src="${src}" class="d-block w-100 carousel-img" style="height:500px; object-fit:contain;"></div>`);
        });
        new bootstrap.Modal(document.getElementById('galleryModal')).show();
    }

    function openPayModal(appId, currentDate) {
        document.getElementById('payAppId').value = appId;
        let d = new Date(currentDate);
        d.setMonth(d.getMonth() + 1); // Suggest next month
        document.getElementById('payNextDate').value = d.toISOString().split('T')[0];
        new bootstrap.Modal(document.getElementById('payModal')).show();
    }

    function openDateModal(appId, currentDate) {
        document.getElementById('dateAppId').value = appId;
        document.getElementById('dateInput').value = currentDate;
        new bootstrap.Modal(document.getElementById('dateModal')).show();
    }

    function openMoveOutModal(appId, name) {
        document.getElementById('moveAppId').value = appId;
        document.getElementById('moveTenantName').innerText = name;
        new bootstrap.Modal(document.getElementById('moveOutModal')).show();
    }
</script>

</body>
</html>