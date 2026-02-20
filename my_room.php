<?php
session_start();
include 'db.php';

// 1. SECURITY: Only Tenants
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'Tenant') { header("Location: login.php"); exit(); }
$user_id = $_SESSION['user_id'];

// 2. FETCH ACTIVE RENTAL DETAILS
$sql = "
    SELECT 
        app.id as app_id, 
        app.room_id,
        app.created_at as start_date,
        p.title as house_name, 
        p.location, 
        p.description as house_desc,
        p.price, p.price_shared,
        p.contact_phone, p.contact_facebook, p.contact_email,
        p.inclusions, p.paid_addons,
        r.room_name, r.room_image, r.total_beds,
        u.first_name as l_fname, u.last_name as l_lname, u.profile_pic as l_pic, u.phone as l_phone
    FROM applications app
    JOIN properties p ON app.property_id = p.id
    JOIN room_units r ON app.room_id = r.id
    JOIN users u ON p.landlord_id = u.id
    WHERE app.tenant_id = ? AND app.status = 'Approved'
    LIMIT 1
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$rental = $stmt->get_result()->fetch_assoc();

if (!$rental) {
    header("Location: dashboard_tenant.php");
    exit();
}

// 3. FETCH ALL TENANTS IN THIS ROOM (Added phone to selection)
$room_id = $rental['room_id'];
$mate_sql = "
    SELECT u.id as uid, u.first_name, u.last_name, u.profile_pic, u.phone 
    FROM applications app
    JOIN users u ON app.tenant_id = u.id
    WHERE app.room_id = ? AND app.status = 'Approved'
";
$stmt2 = $conn->prepare($mate_sql);
$stmt2->bind_param("i", $room_id);
$stmt2->execute();
$roommates = $stmt2->get_result();

// 4. PARSE DATA
$r_imgs_raw = $rental['room_image'];
$room_images = (strpos($r_imgs_raw, '[') === 0) ? json_decode($r_imgs_raw, true) : [$r_imgs_raw];
$main_room_img = !empty($room_images[0]) ? "assets/uploads/rooms/".$room_images[0] : "assets/default_room.jpg";
$js_room_imgs = json_encode($room_images);

$amenities = !empty($rental['inclusions']) ? explode(',', $rental['inclusions']) : [];
$addons = !empty($rental['paid_addons']) ? explode(',', $rental['paid_addons']) : [];
$landlord_img = !empty($rental['l_pic']) ? "assets/uploads/".$rental['l_pic'] : "assets/default.jpg";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>My Room - <?php echo htmlspecialchars($rental['room_name']); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root { --bg-dark: #0f0f0f; --bg-card: #1a1a1a; --accent-orange: #ff9000; }
        body { background: var(--bg-dark); color: white; font-family: sans-serif; padding-bottom: 80px; }

        /* HERO IMAGE */
        .room-hero { position: relative; height: 350px; margin-bottom: 30px; }
        .hero-img { width: 100%; height: 100%; object-fit: cover; border-radius: 16px; cursor: pointer; filter: brightness(0.9); }
        .hero-img:hover { filter: brightness(1); }
        .view-btn { position: absolute; bottom: 20px; right: 20px; background: white; color: black; font-weight: 700; padding: 8px 20px; border-radius: 8px; border: none; box-shadow: 0 4px 15px rgba(0,0,0,0.5); }

        /* CARDS */
        .info-card { background: var(--bg-card); border: 1px solid #333; border-radius: 16px; padding: 25px; margin-bottom: 20px; }
        
        /* ROOMMATE CARDS */
        .mate-card { background: #222; border: 1px solid #333; border-radius: 12px; padding: 20px; text-align: center; transition: 0.2s; position: relative; }
        .mate-card:hover { border-color: #555; background: #252525; transform: translateY(-3px); }
        .mate-pic-lg { width: 90px; height: 90px; border-radius: 50%; object-fit: cover; border: 3px solid #333; margin-bottom: 15px; }
        .mate-name { font-weight: 700; font-size: 16px; margin-bottom: 2px; color: white; }
        .mate-phone { font-size: 13px; color: var(--accent-orange); margin-bottom: 5px; font-weight: 500; }
        .mate-role { font-size: 11px; color: #666; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px; }
        
        /* Highlight "You" */
        .is-me .mate-pic-lg { border-color: var(--accent-orange); }
        .is-me .mate-name { color: var(--accent-orange); }
        .is-me .mate-phone { color: #fff; }

        /* PILLS & CONTACTS */
        .pill { background: #222; border: 1px solid #333; padding: 6px 14px; border-radius: 30px; font-size: 13px; color: #ccc; display: inline-flex; align-items: center; gap: 8px; margin: 0 5px 8px 0; }
        .contact-row { display: flex; align-items: center; gap: 15px; padding: 12px; background: #222; border-radius: 8px; border: 1px solid #333; margin-bottom: 10px; }
        .contact-icon { font-size: 18px; width: 24px; text-align: center; }

        /* MODAL */
        .modal-content { background-color: #1a1a1a; border: 1px solid #333; color: white; }
    </style>
</head>
<body>

<div class="container mt-4">
    <a href="dashboard_tenant.php" class="text-secondary text-decoration-none fw-bold mb-3 d-inline-block">
        <i class="bi bi-arrow-left"></i> Back to Dashboard
    </a>

    <div class="room-hero">
        <img src="<?php echo $main_room_img; ?>" class="hero-img" onclick='openGallery(<?php echo $js_room_imgs; ?>)'>
        <button class="view-btn" onclick='openGallery(<?php echo $js_room_imgs; ?>)'>
            <i class="bi bi-images me-2"></i> View Room Photos
        </button>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="info-card">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h2 class="fw-bold mb-1 text-warning"><?php echo htmlspecialchars($rental['room_name']); ?></h2>
                        <h5 class="text-white mb-0"><?php echo htmlspecialchars($rental['house_name']); ?></h5>
                        <p class="text-secondary small mb-0"><i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($rental['location']); ?></p>
                    </div>
                </div>
                <hr class="border-secondary">
                <h6 class="fw-bold text-secondary text-uppercase small">About the Property</h6>
                <p class="text-light opacity-75 mt-2" style="line-height: 1.6;">
                    <?php echo nl2br(htmlspecialchars($rental['house_desc'])); ?>
                </p>
            </div>

            <div class="info-card">
                <div class="d-flex align-items-center mb-4">
                    <h5 class="fw-bold m-0"><i class="bi bi-people-fill me-2 text-warning"></i>Room Occupants</h5>
                    <span class="badge bg-dark border border-secondary ms-3 text-secondary"><?php echo $roommates->num_rows; ?> total</span>
                </div>
                
                <?php if ($roommates->num_rows > 0): ?>
                    <div class="row g-3">
                        <?php while($mate = $roommates->fetch_assoc()): 
                            $mate_pic = !empty($mate['profile_pic']) ? "assets/uploads/".$mate['profile_pic'] : "assets/default.jpg";
                            $is_me = ($mate['uid'] == $user_id);
                            $name = htmlspecialchars($mate['first_name'] . ' ' . $mate['last_name']);
                            if($is_me) $name .= " (You)";
                            $card_class = $is_me ? "mate-card is-me" : "mate-card";
                            $phone = !empty($mate['phone']) ? $mate['phone'] : "No number";
                        ?>
                        <div class="col-md-4 col-sm-6">
                            <div class="<?php echo $card_class; ?>">
                                <img src="<?php echo $mate_pic; ?>" class="mate-pic-lg">
                                <div class="mate-name"><?php echo $name; ?></div>
                                <div class="mate-phone"><i class="bi bi-telephone-fill me-1"></i> <?php echo htmlspecialchars($phone); ?></div>
                                <div class="mate-role">Tenant</div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4 border border-secondary border-dashed rounded text-secondary">
                        <i class="bi bi-person-slash fs-1 d-block mb-2 opacity-50"></i>
                        No tenants found.
                    </div>
                <?php endif; ?>
            </div>

            <div class="info-card">
                <h5 class="fw-bold mb-4">Your Amenities</h5>
                <div class="row">
                    <div class="col-md-6">
                        <small class="text-success fw-bold text-uppercase d-block mb-2">Included</small>
                        <?php if(!empty($amenities)): foreach($amenities as $am): ?>
                            <div class="pill"><i class="bi bi-check-circle-fill text-success"></i> <?php echo trim($am); ?></div>
                        <?php endforeach; else: echo '<span class="text-secondary small">None listed</span>'; endif; ?>
                    </div>
                    <div class="col-md-6 mt-3 mt-md-0">
                        <small class="text-info fw-bold text-uppercase d-block mb-2">Available Add-ons</small>
                        <?php if(!empty($addons)): foreach($addons as $ad): ?>
                            <div class="pill" style="border-color:#444;"><i class="bi bi-plus-circle-fill text-info"></i> <?php echo trim($ad); ?></div>
                        <?php endforeach; else: echo '<span class="text-secondary small">None listed</span>'; endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            
            <div class="info-card">
                <div class="d-flex align-items-center mb-4">
                    <img src="<?php echo $landlord_img; ?>" style="width:60px; height:60px; border-radius:50%; object-fit:cover; border: 2px solid var(--accent-orange);">
                    <div class="ms-3">
                        <div class="small text-secondary fw-bold">PROPERTY OWNER</div>
                        <h5 class="fw-bold m-0"><?php echo htmlspecialchars($rental['l_fname'] . ' ' . $rental['l_lname']); ?></h5>
                    </div>
                </div>
                <div class="contact-row">
                    <div class="contact-icon"><i class="bi bi-telephone-fill text-success"></i></div>
                    <div><div class="small text-secondary fw-bold" style="font-size:10px;">PHONE</div><div class="fw-bold"><?php echo !empty($rental['contact_phone']) ? htmlspecialchars($rental['contact_phone']) : 'N/A'; ?></div></div>
                </div>
                <div class="contact-row">
                    <div class="contact-icon"><i class="bi bi-facebook text-primary"></i></div>
                    <div><div class="small text-secondary fw-bold" style="font-size:10px;">FACEBOOK</div><a href="#" onclick="return false;" class="text-white text-decoration-none fw-bold"><?php echo !empty($rental['contact_facebook']) ? htmlspecialchars($rental['contact_facebook']) : 'N/A'; ?></a></div>
                </div>
                <div class="contact-row">
                    <div class="contact-icon"><i class="bi bi-envelope-fill text-danger"></i></div>
                    <div><div class="small text-secondary fw-bold" style="font-size:10px;">EMAIL</div><div class="fw-bold" style="font-size:13px;"><?php echo !empty($rental['contact_email']) ? htmlspecialchars($rental['contact_email']) : 'N/A'; ?></div></div>
                </div>
            </div>

            <div class="info-card border-warning border-opacity-25">
                <h6 class="fw-bold text-warning mb-3"><i class="bi bi-info-circle me-2"></i>Rental Status</h6>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-secondary">Start Date</span>
                    <span class="fw-bold"><?php echo date("M d, Y", strtotime($rental['start_date'])); ?></span>
                </div>
                <?php if($rental['price_shared'] > 0): ?>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-secondary">Price per Head</span>
                    <span class="fw-bold text-info">₱<?php echo number_format($rental['price_shared']); ?></span>
                </div>
                <?php endif; ?>
                <div class="d-flex justify-content-between pt-2 border-top border-secondary">
                    <span class="text-secondary">Whole Room</span>
                    <span class="fw-bold text-success">₱<?php echo number_format($rental['price']); ?></span>
                </div>
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="galleryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-transparent border-0 position-relative">
            <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3 z-3" data-bs-dismiss="modal"></button>
            <div id="carouselGallery" class="carousel slide">
                <div class="carousel-inner" id="galleryInner"></div>
                <button class="carousel-control-prev" type="button" data-bs-target="#carouselGallery" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button>
                <button class="carousel-control-next" type="button" data-bs-target="#carouselGallery" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function openGallery(images) {
    const inner = document.getElementById('galleryInner');
    inner.innerHTML = ''; 
    if(!images || images.length === 0) return;
    images.forEach((img, index) => {
        const active = index === 0 ? 'active' : '';
        inner.insertAdjacentHTML('beforeend', `<div class="carousel-item ${active}"><img src="assets/uploads/rooms/${img}" class="d-block w-100" style="border-radius:12px; max-height:80vh; object-fit:contain; background:black;"></div>`);
    });
    new bootstrap.Modal(document.getElementById('galleryModal')).show();
}
</script>
</body>
</html>