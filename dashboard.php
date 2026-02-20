<?php
session_start();
include 'db.php';


if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['user'];


$stmt = $conn->prepare("SELECT first_name, role FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();


if (empty($user['first_name'])) {
    header("Location: profile_setup.php");
    exit();
}

$first_name = $user['first_name'];
$role = $user['role'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BoardHub - Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
       
        body { background: #111; color: #e0e0e0; font-family: 'Arial', sans-serif; }

        
        h1, h2, h3, h4, h5, h6 { color: #ffffff !important; }
        .text-muted { color: #b0b0b0 !important; }
        
      
        .navbar { background: #000; border-bottom: 4px solid #ff9000; padding: 15px 0; }
        .brand-orange { background: #ff9000; color: black; padding: 2px 8px; border-radius: 4px; font-weight: 900; }
        .text-orange { color: #ff9000; }

      
        .card-custom {
            background: #1c1c1c;
            border: 1px solid #333;
            border-radius: 8px;
            padding: 30px;
            transition: 0.3s;
            text-align: center;
            height: 100%;
        }
        .card-custom:hover { 
            transform: translateY(-5px); 
            border-color: #ff9000; 
            box-shadow: 0 0 15px rgba(255, 144, 0, 0.2);
        }
        .card-icon { font-size: 40px; color: #ff9000; margin-bottom: 15px; }

       
        .btn-outline-orange { color: #ff9000; border: 2px solid #ff9000; font-weight: bold; }
        .btn-outline-orange:hover { background: #ff9000; color: black; }
        
        .role-badge {
            background: #333; color: #fff; padding: 2px 8px; border-radius: 4px; 
            font-size: 12px; text-transform: uppercase; letter-spacing: 1px; margin-left: 10px;
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-dark">
        <div class="container d-flex justify-content-between">
            <h3 class="m-0 fw-bold text-white">Board<span class="brand-orange">Hub</span></h3>
            <div class="d-flex align-items-center gap-3">
                <span class="text-white d-none d-md-block">Hello, <b><?php echo htmlspecialchars($first_name); ?></b></span>
                <a href="logout.php" class="btn btn-sm btn-outline-orange">LOGOUT</a>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row mb-4">
            <div class="col-md-12">
                <h2>Welcome back, <span class="text-orange"><?php echo htmlspecialchars($first_name); ?></span> 👋</h2>
                <p class="text-muted">Account Type: <span class="role-badge"><?php echo $role; ?></span></p>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="card-custom">
                    <i class="bi bi-search card-icon"></i>
                    <h4>Find a Room</h4>
                    <p class="text-muted small">Browse available boarding houses.</p>
                    <a href="#" class="btn btn-outline-orange w-100 mt-2">Search Now</a>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card-custom">
                    <i class="bi bi-person-circle card-icon"></i>
                    <h4>My Profile</h4>
                    <p class="text-muted small">Update your personal information.</p>
                    <a href="profile_setup.php" class="btn btn-outline-orange w-100 mt-2">Edit Info</a>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card-custom">
                    <i class="bi bi-shield-lock card-icon"></i>
                    <h4>Security</h4>
                    <p class="text-muted small">Change your password.</p>
                    <a href="change_password.php" class="btn btn-outline-orange w-100 mt-2">Change Password</a>
                </div>
            </div>
        </div>
    </div>

</body>
</html>