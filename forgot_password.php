<?php
session_start();
include 'db.php';

$step = 1;
$error = "";

// STEP 1: CHECK USERNAME
if (isset($_POST['check_username'])) {
    $u = $conn->real_escape_string($_POST['username']);
    $q = $conn->query("SELECT * FROM users WHERE username='$u'");
    if ($q->num_rows > 0) {
        $_SESSION['reset_user'] = $u;
        $step = 2;
    } else {
        $error = "Username not found.";
    }
}

// STEP 2: VERIFY PHONE
if (isset($_POST['verify_phone'])) {
    $p = $conn->real_escape_string($_POST['phone']);
    $u = $_SESSION['reset_user'];
    
    // Check if phone matches the username
    $q = $conn->query("SELECT * FROM users WHERE username='$u' AND phone='$p'");
    if ($q->num_rows > 0) {
        $_SESSION['phone_verified'] = true;
        $step = 3;
    } else {
        $error = "Phone number does not match our records.";
        $step = 2; 
    }
}

// STEP 3: RESET PASSWORD
if (isset($_POST['reset_pass'])) {
    if (!isset($_SESSION['phone_verified'])) { header("Location: forgot_password.php"); exit(); }
    
    $new_pass = $_POST['new_pass'];
    $confirm_pass = $_POST['confirm_pass'];
    $u = $_SESSION['reset_user'];

    if ($new_pass === $confirm_pass) {
        // Basic Length Check
        if (strlen($new_pass) < 6) {
             $error = "Password must be at least 6 characters.";
             $step = 3;
        } else {
            // Hash and Update
            $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
            $conn->query("UPDATE users SET password='$hashed' WHERE username='$u'");
            
            session_destroy();
            header("Location: login.php?msg=reset_success");
            exit();
        }
    } else {
        $error = "Passwords do not match.";
        $step = 3;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Reset Password</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background: #121212; color: white; font-family: 'Inter', sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        
        .card-custom { 
            background: #1e1e1e; 
            border: 1px solid #333; 
            border-radius: 16px; 
            padding: 40px; 
            width: 420px; 
            max-width: 90%; 
            box-shadow: 0 0 40px rgba(0,0,0,0.6); 
        }

        /* PROGRESS STEPS */
        .step-container { display: flex; justify-content: space-between; margin-bottom: 30px; position: relative; }
        .step-container::before { content: ''; position: absolute; top: 14px; left: 0; right: 0; height: 2px; background: #333; z-index: 0; }
        .step-item { position: relative; z-index: 1; text-align: center; width: 30px; }
        .step-circle { 
            width: 30px; height: 30px; background: #333; border-radius: 50%; 
            display: flex; align-items: center; justify-content: center; 
            font-size: 12px; font-weight: bold; color: #888; border: 2px solid #1e1e1e;
            transition: 0.3s;
        }
        .step-active .step-circle { background: #ff9000; color: black; box-shadow: 0 0 10px rgba(255, 144, 0, 0.4); }
        .step-label { font-size: 10px; color: #666; position: absolute; width: 80px; left: -25px; top: 35px; }
        .step-active .step-label { color: #ff9000; font-weight: bold; }

        /* FORM ELEMENTS */
        .btn-orange { background: #ff9000; color: black; font-weight: 700; width: 100%; padding: 12px; border: none; border-radius: 8px; margin-top: 20px; transition: 0.3s; }
        .btn-orange:hover { background: #e08e00; }
        
        .form-control { background: #2a2a2a; border: 1px solid #444; color: white; padding: 12px; border-radius: 8px; }
        .form-control:focus { background: #2a2a2a; border-color: #ff9000; color: white; box-shadow: none; }

        /* PASSWORD STRENGTH */
        .strength-bar { height: 4px; background: #333; border-radius: 2px; margin-top: 8px; overflow: hidden; transition: 0.3s; }
        .strength-fill { height: 100%; width: 0%; transition: 0.3s; }
        .text-strength { font-size: 11px; margin-top: 4px; display: block; text-align: right; color: #666; }

        /* PASSWORD TOGGLE */
        .input-group-text { background: #2a2a2a; border: 1px solid #444; border-left: none; color: #888; cursor: pointer; }
        .input-group-text:hover { color: #fff; }
        .password-input { border-right: none; }

        .back-link { color: #888; text-decoration: none; font-size: 14px; display: block; text-align: center; margin-top: 20px; }
        .back-link:hover { color: #ff9000; }
    </style>
</head>
<body>

<div class="card-custom">
    
    <div class="step-container">
        <div class="step-item <?php if($step>=1) echo 'step-active'; ?>">
            <div class="step-circle">1</div>
            <span class="step-label">Identify</span>
        </div>
        <div class="step-item <?php if($step>=2) echo 'step-active'; ?>">
            <div class="step-circle">2</div>
            <span class="step-label">Verify</span>
        </div>
        <div class="step-item <?php if($step>=3) echo 'step-active'; ?>">
            <div class="step-circle">3</div>
            <span class="step-label">Reset</span>
        </div>
    </div>

    <h3 class="fw-bold text-center mb-1">Reset Password</h3>
    <p class="text-secondary text-center small mb-4">
        <?php 
            if($step==1) echo "Enter your username.";
            if($step==2) echo "Verify it's really you.";
            if($step==3) echo "Secure your account.";
        ?>
    </p>

    <?php if($error): ?>
        <div class="alert alert-danger py-2 text-center small border-0 bg-danger bg-opacity-10 text-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST">
        
        <?php if($step == 1): ?>
            <label class="small text-secondary fw-bold mb-2">USERNAME</label>
            <input type="text" name="username" class="form-control" placeholder="Enter username" required autofocus>
            <button type="submit" name="check_username" class="btn-orange">Next <i class="bi bi-arrow-right"></i></button>
        
        <?php elseif($step == 2): ?>
            <p class="text-center small text-secondary">Account found: <span class="text-warning"><?php echo htmlspecialchars($_SESSION['reset_user']); ?></span></p>
            <label class="small text-secondary fw-bold mb-2">PHONE NUMBER</label>
            <input type="text" name="phone" class="form-control" placeholder="09XXXXXXXXX" 
                   maxlength="11" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required autofocus>
            <button type="submit" name="verify_phone" class="btn-orange">Verify Identity</button>
        
        <?php elseif($step == 3): ?>
            <label class="small text-secondary fw-bold mb-2">NEW PASSWORD</label>
            
            <div class="input-group mb-1">
                <input type="password" name="new_pass" id="newPass" class="form-control password-input" placeholder="New password" required onkeyup="checkStrength(this.value)">
                <span class="input-group-text" onclick="togglePass('newPass')"><i class="bi bi-eye"></i></span>
            </div>
            
            <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
            <span class="text-strength" id="strengthText">Unknown</span>

            <label class="small text-secondary fw-bold mb-2 mt-3">CONFIRM PASSWORD</label>
            <div class="input-group">
                <input type="password" name="confirm_pass" id="confPass" class="form-control password-input" placeholder="Confirm password" required>
                <span class="input-group-text" onclick="togglePass('confPass')"><i class="bi bi-eye"></i></span>
            </div>
            
            <button type="submit" name="reset_pass" class="btn-orange">Update Password</button>
        <?php endif; ?>

    </form>

    <a href="login.php" class="back-link">Back to Login</a>
</div>

<script>
    // Toggle Password Visibility
    function togglePass(id) {
        let input = document.getElementById(id);
        let icon = input.nextElementSibling.querySelector('i');
        if (input.type === "password") {
            input.type = "text";
            icon.classList.replace("bi-eye", "bi-eye-slash");
        } else {
            input.type = "password";
            icon.classList.replace("bi-eye-slash", "bi-eye");
        }
    }

    // Password Strength Checker
    function checkStrength(password) {
        let fill = document.getElementById('strengthFill');
        let text = document.getElementById('strengthText');
        let strength = 0;

        if (password.length >= 6) strength++;
        if (password.length >= 10) strength++;
        if (/[A-Z]/.test(password)) strength++;
        if (/[0-9]/.test(password)) strength++;
        if (/[^A-Za-z0-9]/.test(password)) strength++;

        if (password.length === 0) {
            fill.style.width = '0%';
            fill.style.backgroundColor = '#333';
            text.innerText = "";
            text.style.color = "#666";
        } else if (strength < 2) {
            fill.style.width = '30%';
            fill.style.backgroundColor = '#dc3545'; // Red
            text.innerText = "Weak";
            text.style.color = "#dc3545";
        } else if (strength < 4) {
            fill.style.width = '60%';
            fill.style.backgroundColor = '#ffc107'; // Yellow
            text.innerText = "Medium";
            text.style.color = "#ffc107";
        } else {
            fill.style.width = '100%';
            fill.style.backgroundColor = '#198754'; // Green
            text.innerText = "Strong";
            text.style.color = "#198754";
        }
    }
</script>

</body>
</html>