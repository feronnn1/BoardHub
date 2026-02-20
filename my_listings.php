<?php
session_start();
include 'db.php';

// 1. SEARCH LOGIC
$where = "WHERE status = 'Active'";
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $s = $conn->real_escape_string($_GET['search']);
    $where .= " AND (title LIKE '%$s%' OR location LIKE '%$s%' OR description LIKE '%$s%')";
}
if (isset($_GET['max_price']) && !empty($_GET['max_price'])) {
    $p = intval($_GET['max_price']);
    $where .= " AND (price <= $p OR price_shared <= $p)";
}

// 2. FETCH PROPERTIES
$query = "SELECT * FROM properties $where ORDER BY id DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Find a Home - BoardHub</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --bg-dark: #0f0f0f;
            --bg-card: #181818;
            --accent-orange: #ff9000;
            --text-primary: #ffffff;
            --text-muted: #a0a0a0;
        }
        body { background: var(--bg-dark); color: var(--text-primary); font-family: 'Plus Jakarta Sans', sans-serif; }
        
        /* NAVBAR */
        .navbar { background: rgba(15, 15, 15, 0.9); backdrop-filter: blur(10px); border-bottom: 1px solid #222; padding: 15px 0; z-index: 1000; }
        .navbar-brand { font-weight: 800; font-size: 22px; color: white; letter-spacing: -0.5px; }
        .text-orange { color: var(--accent-orange); }
        .btn-login { background: white; color: black; font-weight: 700; padding: 8px 24px; border-radius: 50px; text-decoration: none; transition: 0.3s; }
        .btn-login:hover { background: var(--accent-orange); transform: translateY(-2px); }
        .nav-link-custom { color: #ccc; text-decoration: none; font-weight: 500; font-size: 14px; margin-left: 20px; transition: 0.2s; }
        .nav-link-custom:hover { color: white; }

        /* HERO SECTION */
        .hero-section { 
            position: relative; 
            padding: 100px 20px 80px; 
            text-align: center; 
            background: radial-gradient(circle at top, #222 0%, #0f0f0f 70%);
            overflow: hidden;
        }
        /* Subtle glow effect behind search */
        .hero-glow { position: absolute; top: -50px; left: 50%; transform: translateX(-50%); width: 600px; height: 400px; background: var(--accent-orange); opacity: 0.08; filter: blur(80px); border-radius: 50%; pointer-events: none; }

        .search-container { max-width: 700px; margin: 0 auto; position: relative; z-index: 2; }
        .search-box { 
            background: rgba(255,255,255,0.05); 
            border: 1px solid rgba(255,255,255,0.1); 
            border-radius: 50px; 
            padding: 6px 6px 6px 25px; 
            display: flex; align-items: center; 
            transition: 0.3s;
            backdrop-filter: blur(5px);
        }
        .search-box:focus-within { border-color: var(--accent-orange); background: rgba(255,255,255,0.1); box-shadow: 0 0 20px rgba(255, 144, 0, 0.15); }
        .search-input { background: transparent; border: none; color: white; flex-grow: 1; outline: none; font-size: 16px; }
        .btn-search { background: var(--accent-orange); color: black; border: none; width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px; transition: 0.3s; }
        .btn-search:hover { transform: scale(1.05); background: white; }

        .filter-tags { margin-top: 25px; display: flex; justify-content: center; gap: 10px; flex-wrap: wrap; }
        .tag { border: 1px solid #333; color: #ccc; padding: 8px 18px; border-radius: 30px; font-size: 13px; text-decoration: none; transition: 0.2s; background: rgba(255,255,255,0.02); }
        .tag:hover { border-color: var(--accent-orange); color: white; }
        .tag.active { background: var(--accent-orange); color: black; border-color: var(--accent-orange); font-weight: 700; }

        /* PROPERTY CARDS */
        .prop-card { 
            background: var(--bg-card); 
            border-radius: 20px; 
            overflow: hidden; 
            border: 1px solid #2a2a2a; 
            transition: all 0.3s ease; 
            height: 100%; 
            display: flex; flex-direction: column;
            position: relative;
        }
        .prop-card:hover { transform: translateY(-8px); border-color: #444; box-shadow: 0 15px 30px rgba(0,0,0,0.3); }
        
        .card-img-wrap { height: 240px; position: relative; overflow: hidden; }
        .prop-img { width: 100%; height: 100%; object-fit: cover; transition: 0.5s; }
        .prop-card:hover .prop-img { transform: scale(1.08); }
        
        .price-badge { 
            position: absolute; bottom: 15px; left: 15px; 
            background: rgba(0,0,0,0.85); padding: 8px 14px; 
            border-radius: 12px; backdrop-filter: blur(4px); 
            border: 1px solid rgba(255,255,255,0.1);
        }
        .price-text { font-weight: 700; color: white; font-size: 16px; }
        
        .card-body { padding: 22px; flex-grow: 1; display: flex; flex-direction: column; }
        .prop-title { font-size: 18px; font-weight: 700; margin-bottom: 5px; color: white; }
        .prop-loc { font-size: 13px; color: #888; display: flex; align-items: center; gap: 6px; margin-bottom: 15px; }
        
        .amenities-row { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 20px; }
        .amenity-pill { font-size: 11px; background: #252525; color: #ccc; padding: 5px 10px; border-radius: 6px; }

        .btn-view { 
            margin-top: auto; 
            width: 100%; 
            padding: 12px; 
            border-radius: 12px; 
            background: #222; 
            color: white; 
            text-align: center; 
            text-decoration: none; 
            font-weight: 600; 
            transition: 0.2s; 
            border: 1px solid #333;
        }
        .btn-view:hover { background: var(--accent-orange); color: black; border-color: var(--accent-orange); }

        .empty-state { text-align: center; padding: 60px; color: #666; }
    </style>
</head>
<body>

<nav class="navbar fixed-top">
    <div class="container">
        <a class="navbar-brand" href="listings.php">Board<span class="text-orange">Hub</span></a>
        
        <div class="d-flex align-items-center">
            <?php if(isset($_SESSION['user'])): ?>
                <?php 
                    $role = $_SESSION['role'] ?? ''; 
                    $dash = ($role == 'Landlord') ? "dashboard_landlord.php" : "dashboard_tenant.php";
                    if ($role == 'Admin') $dash = "dashboard_admin.php";
                ?>
                <a href="<?php echo $dash; ?>" class="nav-link-custom">My Dashboard</a>
                <a href="logout.php" class="nav-link-custom text-danger">Logout</a>
            <?php else: ?>
                <a href="login.php" class="btn-login">Sign In</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<div class="hero-section">
    <div class="hero-glow"></div>
    <div class="container">
        <h1 class="display-5 fw-bold mb-3 text-white">Find your next home.</h1>
        <p class="text-muted mb-5 fs-5">Search available boarding houses and rooms near you.</p>
        
        <div class="search-container">
            <form class="search-box" method="GET">
                <i class="bi bi-search ms-2 text-secondary fs-5"></i>
                <input type="text" name="search" class="search-input ms-3" placeholder="Search by location, name..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <button type="submit" class="btn-search"><i class="bi bi-arrow-right"></i></button>
            </form>
        </div>

        <div class="filter-tags">
            <a href="listings.php" class="tag <?php echo !isset($_GET['max_price']) ? 'active' : ''; ?>">All</a>
            <a href="listings.php?max_price=2000" class="tag">Under ₱2k</a>
            <a href="listings.php?max_price=4000" class="tag">Under ₱4k</a>
            <a href="listings.php?max_price=6000" class="tag">Under ₱6k</a>
        </div>
    </div>
</div>

<div class="container pb-5">
    <div class="row g-4">
        <?php if($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): 
                $images = json_decode($row['images'], true);
                $thumb = !empty($images) ? "assets/uploads/rooms/" . $images[0] : "assets/default_room.jpg";
                // FIX: Filter out empty amenities
                $raw_amenities = explode(',', $row['inclusions']);
                $amenities = array_filter($raw_amenities, fn($value) => !is_null($value) && $value !== '');
                $amenities = array_slice($amenities, 0, 3); // Take top 3
            ?>
            <div class="col-md-6 col-lg-4">
                <div class="prop-card">
                    <div class="card-img-wrap">
                        <img src="<?php echo $thumb; ?>" class="prop-img">
                        <div class="price-badge">
                            <span class="price-text">₱<?php echo number_format($row['price']); ?></span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="prop-title"><?php echo htmlspecialchars($row['title']); ?></div>
                        <div class="prop-loc"><i class="bi bi-geo-alt-fill text-orange"></i> <?php echo htmlspecialchars($row['location']); ?></div>
                        
                        <div class="amenities-row">
                            <?php foreach($amenities as $am): ?>
                                <span class="amenity-pill"><?php echo trim($am); ?></span>
                            <?php endforeach; ?>
                        </div>

                        <a href="property_details.php?id=<?php echo $row['id']; ?>" class="btn-view">View Property</a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 empty-state">
                <i class="bi bi-search fs-1 mb-3 d-block"></i>
                <h4>No results found</h4>
                <p>Try adjusting your search criteria.</p>
                <a href="listings.php" class="btn btn-outline-light btn-sm mt-2">Clear Filters</a>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>