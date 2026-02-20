<?php
session_start();
include 'db.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1. IF ALREADY LOGGED IN, REDIRECT
if (isset($_SESSION['user']) && isset($_SESSION['role'])) {
    $role = $_SESSION['role'];
    if ($role == 'Landlord') header("Location: dashboard_landlord.php");
    elseif ($role == 'Tenant') header("Location: dashboard_tenant.php"); // <--- CHANGED TO DASHBOARD
    elseif ($role == 'Admin') header("Location: dashboard_admin.php");
    exit();
}

// 2. CHECK FOR MESSAGES
$success_msg = "";
$error_msg = "";

if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'reset_success') $success_msg = "Password reset successful! Please login.";
    if ($_GET['msg'] == 'registered') $success_msg = "Account created! Please login.";
}

// === SMART IMAGE FINDER ===
$bg_url = "assets/house.png"; 
if (file_exists('assets/house.jpg')) { $bg_url = "assets/house.jpg"; }
if (file_exists('assets/house.jpeg')) { $bg_url = "assets/house.jpeg"; }

// 3. HANDLE LOGIN
if(isset($_POST['login'])){
    $username = $_POST['username']; 
    $password = $_POST['password'];

    $query = $conn->prepare("SELECT * FROM users WHERE username=?");
    $query->bind_param("s", $username);
    $query->execute();
    $result = $query->get_result();

    if($result->num_rows > 0){
        $user = $result->fetch_assoc();
        if(password_verify($password, $user['password'])){
            
            // --- SET ALL SESSION VARIABLES ---
            $_SESSION['user'] = $user['username'];
            $_SESSION['user_id'] = $user['id'];  
            $_SESSION['role'] = $user['role'];   
            
            // REDIRECT BASED ON ROLE
            if (empty($user['first_name'])) {
                header("Location: profile_setup.php");
            } else {
                $role = $user['role'];
                if ($role === 'Admin') header("Location: dashboard_admin.php");
                elseif ($role === 'Landlord') header("Location: dashboard_landlord.php");
                elseif ($role === 'Tenant') header("Location: dashboard_tenant.php"); // <--- CHANGED TO DASHBOARD
            }
            exit();
        } else { $error_msg = "Wrong Password!"; }
    } else { $error_msg = "Username Not Found!"; }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - BoardHub</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background: #111; font-family: 'Arial', sans-serif; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .main-card { width: 1000px; max-width: 95%; height: 600px; background: #1c1c1c; border-radius: 12px; overflow: hidden; box-shadow: 0 0 40px rgba(0,0,0,0.8); }

        .image-side {
            background-image: url('<?php echo $bg_url; ?>?v=<?php echo time(); ?>');
            background-position: center; background-size: cover; background-repeat: no-repeat;
            position: relative; color: white; display: flex; flex-direction: column; justify-content: space-between;
        }
        .image-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.4); }
        .content-overlay { position: relative; z-index: 2; height: 100%; display: flex; flex-direction: column; justify-content: space-between; }
        .brand-logo { font-size: 24px; font-weight: 900; letter-spacing: 1px; color: white; }
        .brand-orange { color: #ff9000; }
        .back-btn { background: rgba(255, 255, 255, 0.2); color: white; padding: 5px 15px; border-radius: 20px; text-decoration: none; font-size: 12px; backdrop-filter: blur(5px); transition: 0.3s; }
        .back-btn:hover { background: rgba(255, 255, 255, 0.4); color: white; }

        .form-side { padding: 50px; display: flex; flex-direction: column; justify-content: center; background: #1c1c1c; color: white; }
        h3 { font-weight: 700; color: white; margin-bottom: 5px; }
        .text-muted-light { color: #888; font-size: 14px; }
        .text-link { color: #ff9000; text-decoration: none; font-weight: bold; }
        .text-link:hover { text-decoration: underline; color: #ffaa33; }
        .form-control { background: #2a2a2a; border: 1px solid #333; color: white !important; height: 45px; margin-bottom: 15px; border-radius: 6px; }
        .form-control:focus { background: #2a2a2a; border-color: #ff9000; box-shadow: none; }
        .form-control::placeholder { color: #666; }
        .btn-orange { background: #ff9000; border: none; font-weight: bold; height: 45px; width: 100%; border-radius: 6px; color: black; transition: 0.3s; }
        .btn-orange:hover { background: #e67e00; }
        
        .password-wrapper { position: relative; margin-bottom: 15px; }
        .toggle-password { position: absolute; right: 15px; top: 12px; color: #888; cursor: pointer; font-size: 18px; }
        .toggle-password:hover { color: #ff9000; }

        .divider { display: flex; align-items: center; color: #555; font-size: 12px; margin: 20px 0; }
        .divider::before, .divider::after { content: ""; flex: 1; border-bottom: 1px solid #333; }
        .divider::before { margin-right: 10px; } .divider::after { margin-left: 10px; }
        .social-btn { background: #2a2a2a; border: 1px solid #333; color: white; width: 100%; height: 40px; border-radius: 6px; font-size: 18px; transition: 0.3s; }
        .social-btn:hover { border-color: #ff9000; background: #333; }
        .btn-fb:hover { color: #4267B2; } .btn-google:hover { color: #DB4437; } .btn-insta:hover { color: #E1306C; }
    </style>
</head>
<body>
    <div class="main-card row g-0">
        <div class="col-lg-6 d-none d-lg-block image-side p-4">
            <div class="image-overlay"></div>
            <div class="content-overlay">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="brand-logo">Board<span class="brand-orange">Hub</span></div>
                    <a href="listings.php" class="back-btn">Back to website &rarr;</a>
                </div>
                <div>
                    <h2 class="fw-bold mb-2">Capturing Comfort,<br>Creating Homes.</h2>
                    <p class="small text-white-50">Find your perfect boarding house today.</p>
                </div>
            </div>
        </div>

        <div class="col-lg-6 form-side">
            <h3>Login to Account</h3>
            <p class="text-muted-light mb-4">Don't have an account? <a href="register.php" class="text-link">Register</a></p>
            
            <?php if($error_msg): ?>
                <div class='alert alert-danger py-2 border-0 bg-danger bg-opacity-10 text-danger'><?php echo $error_msg; ?></div>
            <?php endif; ?>
            
            <?php if($success_msg): ?>
                <div class='alert alert-success py-2 border-0 bg-success bg-opacity-10 text-success'><?php echo $success_msg; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <input type="text" name="username" class="form-control" placeholder="Username" required>
                
                <div class="password-wrapper">
                    <input type="password" name="password" id="loginPass" class="form-control" placeholder="Password" required>
                    <i class="bi bi-eye-slash toggle-password" id="toggleLoginPass"></i>
                </div>
                
                <div class="d-flex justify-content-between mb-3 text-muted-light small">
                    <div><input type="checkbox" id="rem"> <label for="rem">Remember me</label></div>
                    <a href="forgot_password.php" class="text-link text-white-50">Forgot Password?</a>
                </div>
                <button type="submit" name="login" class="btn btn-orange">Login</button>
            </form>

            <div class="divider">Or login with</div>
            <div class="row g-2">
                <div class="col"><button class="social-btn btn-fb"><i class="bi bi-facebook"></i></button></div>
                <div class="col"><button class="social-btn btn-google"><i class="bi bi-google"></i></button></div>
                <div class="col"><button class="social-btn btn-insta"><i class="bi bi-instagram"></i></button></div>
            </div>
        </div>
    </div>

    <script>
        const togglePass = document.querySelector('#toggleLoginPass');
        const loginPass = document.querySelector('#loginPass');
        togglePass.addEventListener('click', function (e) {
            const type = loginPass.getAttribute('type') === 'password' ? 'text' : 'password';
            loginPass.setAttribute('type', type);
            this.classList.toggle('bi-eye');
            this.classList.toggle('bi-eye-slash');
        });
    </script>
</body>
</html>