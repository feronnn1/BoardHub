<?php
session_start();
include 'db.php';

if (isset($_POST['register'])) {
    $fname = htmlspecialchars(trim($_POST['first_name']));
    $lname = htmlspecialchars(trim($_POST['last_name']));
    $email = htmlspecialchars(trim($_POST['email']));
    $fb_name = htmlspecialchars(trim($_POST['facebook_name']));
    $username = htmlspecialchars(trim($_POST['username']));
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    $phone = htmlspecialchars(trim($_POST['phone']));

    // Check if username exists
    $check = $conn->prepare("SELECT id FROM users WHERE username=?");
    $check->bind_param("s", $username);
    $check->execute();
    $check->store_result();
    
    if ($check->num_rows > 0) {
        $error = "Username already exists. Please choose another.";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, facebook_name, phone, username, password, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $fname, $lname, $email, $fb_name, $phone, $username, $password, $role);
        
        if ($stmt->execute()) {
            header("Location: login.php?msg=Registration successful! Please login.");
            exit();
        } else {
            $error = "Registration failed. Please try again.";
        }
        $stmt->close();
    }
    $check->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Create Account | BoardHub</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root { --accent-orange: #ff9000; --bg-dark: #0f0f0f; --bg-card: #1a1a1a; }
        body { background: var(--bg-dark); color: white; font-family: 'Plus Jakarta Sans', sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; padding: 20px; }
        
        /* SIDE-BY-SIDE LAYOUT CONTAINER */
        .auth-card { background: var(--bg-card); border-radius: 24px; overflow: hidden; display: flex; width: 100%; max-width: 1100px; box-shadow: 0 20px 40px rgba(0,0,0,0.6); border: 1px solid #2a2a2a; }
        
        /* LEFT SIDE - IMAGE */
        .auth-left { 
            width: 45%; 
            /* CHANGE THE IMAGE PATH BELOW TO MATCH YOUR LOGIN PAGE IMAGE */
            background: linear-gradient(to bottom, rgba(0,0,0,0.4), rgba(0,0,0,0.9)), url('assets/house.png') center/cover no-repeat; 
            background-color: #222; 
            padding: 50px; 
            display: flex; 
            flex-direction: column; 
            justify-content: space-between;
            position: relative;
        }
        .brand-logo { font-size: 26px; font-weight: 800; color: white; text-decoration: none; display: flex; align-items: center; gap: 10px; }
        .hero-text h1 { font-weight: 800; font-size: 36px; margin-bottom: 15px; line-height: 1.1; letter-spacing: -0.5px; }
        .hero-text p { color: #ccc; font-size: 16px; margin: 0; max-width: 90%; opacity: 0.9; }
        .btn-back-website { position: absolute; top: 50px; right: 50px; color: rgba(255,255,255,0.7); text-decoration: none; font-weight: 600; font-size: 14px; transition: 0.2s; display: flex; align-items: center; gap: 8px; }
        .btn-back-website:hover { color: white; }

        /* RIGHT SIDE - FORM */
        .auth-right { width: 55%; padding: 50px 60px; display: flex; flex-direction: column; justify-content: center; }
        .auth-header { margin-bottom: 30px; }
        .auth-header h3 { font-weight: 700; font-size: 28px; margin-bottom: 8px; }
        .auth-subtitle { font-size: 15px; color: #b0b0b0; } 
        .auth-subtitle a { color: var(--accent-orange); text-decoration: none; font-weight: 700; transition: 0.2s; }
        .auth-subtitle a:hover { color: #ffaa33; text-decoration: underline; }

        /* Form Styling */
        .form-label { font-size: 12px; font-weight: 700; text-transform: uppercase; color: #888; letter-spacing: 0.5px; margin-bottom: 8px; }
        .form-control, .form-select { background: #111; border: 2px solid #2a2a2a; color: white; padding: 14px 18px; border-radius: 12px; font-size: 15px; transition: all 0.2s ease; }
        .form-control:focus, .form-select:focus { background: #111; border-color: var(--accent-orange); box-shadow: 0 0 0 4px rgba(255, 144, 0, 0.1); color: white; }
        .form-control::placeholder { color: #888; opacity: 1; } 
        
        .btn-primary-auth { background: var(--accent-orange); border: none; padding: 16px; font-weight: 800; color: #000; width: 100%; border-radius: 12px; font-size: 16px; letter-spacing: 0.5px; transition: all 0.2s; margin-top: 20px; }
        .btn-primary-auth:hover { background: #ffaa33; transform: translateY(-2px); box-shadow: 0 10px 20px -10px rgba(255, 144, 0, 0.5); }

        /* Password Toggle Icon */
        .password-wrapper { position: relative; display: block; }
        .toggle-password { position: absolute; top: 50%; right: 18px; transform: translateY(-50%); color: #888; cursor: pointer; padding: 5px; z-index: 10; font-size: 18px; transition: 0.2s; }
        .toggle-password:hover { color: var(--accent-orange); }

        /* Improved Password Strength Segmented Bar */
        .strength-meter-container { display: flex; gap: 4px; height: 6px; margin-top: 10px; border-radius: 6px; overflow: hidden; }
        .strength-segment { flex: 1; background-color: #2a2a2a; transition: background-color 0.3s ease; border-radius: 4px; }
        .strength-label { font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; text-align: right; transition: color 0.3s ease; }

        /* Responsive */
        @media (max-width: 992px) {
            .auth-card { flex-direction: column; max-width: 550px; }
            .auth-left { width: 100%; height: 280px; padding: 40px; }
            .btn-back-website { top: 40px; right: 40px; }
            .auth-right { width: 100%; padding: 40px 35px; }
            .hero-text h1 { font-size: 28px; }
        }
    </style>
</head>
<body>

<div class="auth-card">
    <div class="auth-left">
        <a href="index.php" class="brand-logo">
            <i class="bi bi-building-fill text-warning" style="color: var(--accent-orange) !important;"></i> BoardHub
        </a>
        
        <div class="hero-text">
            <h1>Join Our Community<br>of Renters & Owners.</h1>
            <p>Create an account to start your journey with BoardHub today.</p>
        </div>
    </div>

    <div class="auth-right">
        <div class="auth-header">
            <h3>Create Account</h3>
            <div class="auth-subtitle">
                Already have an account? <a href="login.php">Log In here</a>
            </div>
        </div>

        <?php if(isset($error)): ?>
            <div class="alert alert-danger bg-danger bg-opacity-10 border-danger text-danger py-3 px-3 small fw-bold rounded-3 mb-4 d-flex align-items-center">
                <i class="bi bi-exclamation-circle-fill fs-5 me-2"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" autocomplete="off">
            <div class="row g-3 mb-3">
                <div class="col-sm-6">
                    <label class="form-label">First Name</label>
                    <input type="text" name="first_name" class="form-control" placeholder="Enter first name" required>
                </div>
                <div class="col-sm-6">
                    <label class="form-label">Last Name</label>
                    <input type="text" name="last_name" class="form-control" placeholder="Enter last name" required>
                </div>
            </div>

            <div class="mb-3">
                 <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="name@example.com" required>
            </div>
            
            <div class="mb-3">
                 <label class="form-label">Phone Number</label>
                <input type="tel" name="phone" class="form-control" placeholder="09XXXXXXXXX (11 digits)" 
                       maxlength="11" minlength="11" pattern="[0-9]{11}" 
                       oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
            </div>

            <div class="mb-3">
                 <label class="form-label">Facebook Name (Optional)</label>
                <input type="text" name="facebook_name" class="form-control" placeholder="e.g. Juan Dela Cruz">
            </div>

            <div class="mb-4">
                 <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" placeholder="Choose a username" required autocomplete="new-username">
            </div>

            <div class="mb-4">
                 <label class="form-label">Password</label>
                 <div class="password-wrapper">
                    <input type="password" name="password" id="passwordInput" class="form-control" placeholder="Create a strong password" required autocomplete="new-password">
                    <i class="bi bi-eye-slash toggle-password" onclick="toggleVisibility('passwordInput', this)"></i>
                 </div>
                
                <div id="strengthSection" class="d-none">
                    <div class="strength-meter-container">
                        <div class="strength-segment" id="seg1"></div>
                        <div class="strength-segment" id="seg2"></div>
                        <div class="strength-segment" id="seg3"></div>
                        <div class="strength-segment" id="seg4"></div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-1">
                        <span class="small text-secondary" style="font-size: 10px;">Must be at least 8 chars</span>
                        <span id="strengthLabel" class="strength-label"></span>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">I want to...</label>
                <select name="role" class="form-select" required>
                    <option value="" disabled selected>Select Account Type</option>
                    <option value="Tenant" class="fw-bold text-white">Find a Room (Tenant)</option>
                    <option value="Landlord" class="fw-bold text-white">List Properties (Landlord)</option>
                </select>
            </div>

            <button type="submit" name="register" class="btn-primary-auth">Create Account</button>
        </form>
    </div>
</div>

<script>
    // 1. Show/Hide Password Toggle
    function toggleVisibility(inputId, iconElement) {
        const input = document.getElementById(inputId);
        if (input.type === "password") {
            input.type = "text";
            iconElement.classList.remove("bi-eye-slash");
            iconElement.classList.add("bi-eye");
            iconElement.style.color = "var(--accent-orange)";
        } else {
            input.type = "password";
            iconElement.classList.remove("bi-eye");
            iconElement.classList.add("bi-eye-slash");
            iconElement.style.color = "#888";
        }
    }

    // 2. Segmented Password Strength Logic
    const passwordInput = document.getElementById('passwordInput');
    const strengthSection = document.getElementById('strengthSection');
    const seg1 = document.getElementById('seg1');
    const seg2 = document.getElementById('seg2');
    const seg3 = document.getElementById('seg3');
    const seg4 = document.getElementById('seg4');
    const strengthLabel = document.getElementById('strengthLabel');

    passwordInput.addEventListener('input', function() {
        const val = passwordInput.value;
        
        if (val.length === 0) {
            strengthSection.classList.add('d-none');
            return;
        }
        strengthSection.classList.remove('d-none');
        
        let score = 0;
        if (val.length >= 8) score++; // Minimum length
        if (val.match(/[a-z]/) && val.match(/[A-Z]/)) score++; // Mixed case
        if (val.match(/\d/)) score++; // Number
        if (val.match(/[^a-zA-Z\d]/)) score++; // Special char

        // Reset segments to dark gray
        seg1.style.backgroundColor = '#2a2a2a';
        seg2.style.backgroundColor = '#2a2a2a';
        seg3.style.backgroundColor = '#2a2a2a';
        seg4.style.backgroundColor = '#2a2a2a';

        if (val.length < 8) {
            // Too Short
            seg1.style.backgroundColor = '#ef4444'; // Red
            strengthLabel.innerText = 'Too Short';
            strengthLabel.style.color = '#ef4444';
        } else if (score === 1) {
            // Weak
            seg1.style.backgroundColor = '#ef4444';
            seg2.style.backgroundColor = '#ef4444';
            strengthLabel.innerText = 'Weak';
            strengthLabel.style.color = '#ef4444';
        } else if (score === 2 || score === 3) {
            // Medium
            seg1.style.backgroundColor = '#f59e0b'; // Yellow
            seg2.style.backgroundColor = '#f59e0b';
            seg3.style.backgroundColor = '#f59e0b';
            strengthLabel.innerText = 'Medium';
            strengthLabel.style.color = '#f59e0b';
        } else if (score === 4) {
            // Strong
            seg1.style.backgroundColor = '#10b981'; // Green
            seg2.style.backgroundColor = '#10b981';
            seg3.style.backgroundColor = '#10b981';
            seg4.style.backgroundColor = '#10b981';
            strengthLabel.innerText = 'Strong';
            strengthLabel.style.color = '#10b981';
        }
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>