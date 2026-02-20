<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user'])) { header("Location: login.php"); exit(); }

$user_id = $_SESSION['user_id'];
$msg = "";
$msg_type = "";

// 1. HANDLE PROFILE UPDATE
if (isset($_POST['update_profile'])) {
    $fname = htmlspecialchars(trim($_POST['first_name']));
    $lname = htmlspecialchars(trim($_POST['last_name']));
    $email = htmlspecialchars(trim($_POST['email']));
    $fb_name = htmlspecialchars(trim($_POST['facebook_name']));
    $phone = htmlspecialchars(trim($_POST['phone']));
    
    // Handle Profile Pic Upload
    $profile_pic = $_POST['old_pp']; 
    if (!empty($_FILES['profile_pic']['name'])) {
        $target = "assets/uploads/" . basename($_FILES['profile_pic']['name']);
        if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target)) {
            $profile_pic = basename($_FILES['profile_pic']['name']);
        }
    }

    $stmt = $conn->prepare("UPDATE users SET first_name=?, last_name=?, email=?, facebook_name=?, phone=?, profile_pic=? WHERE id=?");
    $stmt->bind_param("ssssssi", $fname, $lname, $email, $fb_name, $phone, $profile_pic, $user_id);

    if ($stmt->execute()) {
        $msg = "Profile updated successfully!";
        $msg_type = "success";
    } else {
        $msg = "Error updating profile.";
        $msg_type = "danger";
    }
    $stmt->close();
}

// 2. HANDLE PASSWORD CHANGE
if (isset($_POST['change_password'])) {
    $old_pass = $_POST['old_password'];
    $new_pass = $_POST['new_password'];

    // Get current password from DB
    $stmt = $conn->prepare("SELECT password FROM users WHERE id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_data = $result->fetch_assoc();
    $stmt->close();

    // Verify old password
    if (password_verify($old_pass, $user_data['password'])) {
        $new_hash = password_hash($new_pass, PASSWORD_DEFAULT);
        
        $update_pw = $conn->prepare("UPDATE users SET password=? WHERE id=?");
        $update_pw->bind_param("si", $new_hash, $user_id);
        
        if ($update_pw->execute()) {
            $msg = "Password updated successfully!";
            $msg_type = "success";
        } else {
            $msg = "Failed to update password.";
            $msg_type = "danger";
        }
        $update_pw->close();
    } else {
        $msg = "Incorrect current password. Please try again.";
        $msg_type = "danger";
    }
}

// FETCH CURRENT DATA
$u = $conn->query("SELECT * FROM users WHERE id=$user_id")->fetch_assoc();

// Determine Dashboard Link
if ($u['role'] == 'Admin') { $dash_link = 'dashboard_admin.php'; }
elseif ($u['role'] == 'Landlord') { $dash_link = 'dashboard_landlord.php'; }
else { $dash_link = 'dashboard_tenant.php'; }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Account Settings | BoardHub</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root { --bg-dark: #0f0f0f; --bg-card: #1a1a1a; --bg-input: #111111; --accent-orange: #ff9000; --text-muted: #888888; }
        body { background: var(--bg-dark); color: white; font-family: 'Plus Jakarta Sans', sans-serif; overflow-x: hidden; }
        
        /* RESTORED SIDEBAR DESIGN */
        .sidebar { width: 250px; height: 100vh; background: #050505; position: fixed; top: 0; left: 0; border-right: 1px solid #222; padding: 20px; display: flex; flex-direction: column; z-index: 1000; }
        .brand { font-size: 20px; font-weight: 800; margin-bottom: 40px; color: white; text-decoration: none; padding-left: 10px; }
        .nav-link { color: #888; padding: 12px 15px; border-radius: 10px; margin-bottom: 5px; display: flex; align-items: center; gap: 12px; text-decoration: none; font-weight: 500; transition: 0.2s; }
        .nav-link:hover, .nav-link.active { background: rgba(255, 144, 0, 0.1); color: var(--accent-orange); }
        .main-content { margin-left: 250px; padding: 40px; }

        /* PROFILE UI */
        .settings-container { max-width: 800px; }
        .section-card { background: var(--bg-card); border: 1px solid #333; border-radius: 20px; padding: 35px; margin-bottom: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
        .section-title { font-size: 16px; font-weight: 700; color: white; margin-bottom: 25px; display: flex; align-items: center; gap: 10px; text-transform: uppercase; letter-spacing: 0.5px; }
        .section-title i { color: var(--accent-orange); font-size: 20px; }

        /* FORMS */
        .form-label { font-size: 12px; color: var(--text-muted); font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; }
        .form-control { background: var(--bg-input); border: 1px solid #333; color: white; padding: 14px 18px; border-radius: 12px; font-size: 14px; transition: all 0.2s; }
        .form-control:focus { background: var(--bg-input); border-color: var(--accent-orange); color: white; box-shadow: 0 0 0 4px rgba(255, 144, 0, 0.1); }
        .form-control::placeholder { color: #555; }
        
        /* BETTER PROFILE UPLOAD CLICKABLE UI */
        .profile-upload-wrapper { position: relative; width: 90px; height: 90px; border-radius: 50%; border: 3px solid #333; overflow: hidden; cursor: pointer; background: #222; display: inline-block; }
        .profile-img-preview { width: 100%; height: 100%; object-fit: cover; }
        .profile-upload-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); display: flex; align-items: center; justify-content: center; opacity: 0; transition: 0.3s; }
        .profile-upload-wrapper:hover .profile-upload-overlay { opacity: 1; }
        .profile-upload-overlay i { color: white; font-size: 24px; }
        .btn-upload { background: transparent; border: 1px solid #555; color: white; padding: 8px 16px; font-size: 13px; font-weight: 600; border-radius: 8px; transition: 0.2s; }
        .btn-upload:hover { border-color: var(--accent-orange); color: var(--accent-orange); }

        /* Buttons */
        .btn-primary-custom { background: var(--accent-orange); border: none; padding: 12px 24px; font-weight: 700; color: #000; border-radius: 10px; transition: 0.2s; }
        .btn-primary-custom:hover { background: #ffaa33; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(255, 144, 0, 0.3); }
        
        .btn-secondary-custom { background: #222; border: 1px solid #444; color: white; padding: 12px 24px; font-weight: 600; border-radius: 10px; transition: 0.2s; }
        .btn-secondary-custom:hover { background: #333; }

        /* Toggle Password Eye Icon */
        .password-wrapper { position: relative; }
        .toggle-password { position: absolute; top: 50%; right: 15px; transform: translateY(-50%); color: #888; cursor: pointer; padding: 5px; z-index: 10; font-size: 18px; transition: 0.2s; }
        .toggle-password:hover { color: var(--accent-orange); }

        /* Password Strength UI */
        .strength-meter-container { background: #2a2a2a; height: 5px; border-radius: 5px; margin-top: 10px; overflow: hidden; }
        .strength-bar { height: 100%; width: 0%; border-radius: 5px; transition: all 0.3s ease; }
        .strength-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 8px; text-align: right; display: block; }
        .strength-weak { background-color: #ef4444; color: #ef4444; }
        .strength-medium { background-color: #f59e0b; color: #f59e0b; }
        .strength-strong { background-color: #10b981; color: #10b981; }

        /* Role Badge */
        .role-badge { background: #222; border: 1px solid #444; padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 700; color: var(--accent-orange); letter-spacing: 0.5px; text-transform: uppercase; }
    </style>
</head>
<body>

<div class="sidebar">
    <a href="<?php echo $dash_link; ?>" class="brand">Board<span class="text-warning">Hub</span></a>
    <a href="<?php echo $dash_link; ?>" class="nav-link"><i class="bi bi-grid-fill"></i> Dashboard</a>
    <?php if($u['role'] != 'Admin'): ?>
        <a href="listings.php" class="nav-link"><i class="bi bi-search"></i> Browse Rooms</a>
    <?php endif; ?>
    <a href="profile_setup.php" class="nav-link active"><i class="bi bi-person-circle"></i> My Profile</a>
    <a href="logout.php" class="nav-link text-danger mt-auto"><i class="bi bi-box-arrow-right"></i> Sign Out</a>
</div>

<div class="main-content">
    <div class="settings-container">
        <div class="d-flex justify-content-between align-items-end mb-5">
            <div>
                <h2 class="fw-bold mb-1">Account Settings</h2>
                <p class="text-secondary m-0">Manage your profile details and security preferences.</p>
            </div>
            <div class="role-badge"><i class="bi bi-shield-check me-1"></i> <?php echo $u['role']; ?> Account</div>
        </div>
        
        <?php if($msg): ?>
            <div class="alert alert-<?php echo $msg_type; ?> bg-<?php echo $msg_type; ?> bg-opacity-10 border-<?php echo $msg_type; ?> text-<?php echo $msg_type; ?> py-3 px-4 rounded-3 mb-4 d-flex align-items-center fw-bold">
                <i class="bi <?php echo ($msg_type == 'success') ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill'; ?> fs-5 me-3"></i> 
                <?php echo $msg; ?>
            </div>
        <?php endif; ?>

        <div class="section-card">
            <h4 class="section-title"><i class="bi bi-person-vcard"></i> Personal Information</h4>
            <form method="POST" enctype="multipart/form-data">
                
                <div class="d-flex align-items-center gap-4 mb-4">
                    <div class="profile-upload-wrapper" onclick="document.getElementById('profilePicInput').click()">
                        <?php $pp = !empty($u['profile_pic']) ? "assets/uploads/".$u['profile_pic'] : "assets/default.jpg"; ?>
                        <img src="<?php echo $pp; ?>" class="profile-img-preview" id="imgPreview">
                        <div class="profile-upload-overlay">
                            <i class="bi bi-camera"></i>
                        </div>
                    </div>
                    <div>
                        <input type="hidden" name="old_pp" value="<?php echo $u['profile_pic']; ?>">
                        <input type="file" name="profile_pic" id="profilePicInput" class="d-none" accept="image/*" onchange="previewImage(event)">
                        <button type="button" class="btn-upload mb-2" onclick="document.getElementById('profilePicInput').click()">Change Photo</button>
                        <div class="small text-secondary" style="font-size: 11px;">Recommended: Square JPG or PNG, max 2MB.</div>
                    </div>
                </div>

                <div class="row g-4 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">First Name</label>
                        <input type="text" name="first_name" class="form-control" value="<?php echo htmlspecialchars($u['first_name']); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Last Name</label>
                        <input type="text" name="last_name" class="form-control" value="<?php echo htmlspecialchars($u['last_name']); ?>" required>
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($u['email'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Phone Number</label>
                        <input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($u['phone']); ?>" 
                               maxlength="11" minlength="11" pattern="[0-9]{11}" 
                               oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Facebook Name <span class="text-secondary text-lowercase fw-normal">(Optional)</span></label>
                    <input type="text" name="facebook_name" class="form-control" value="<?php echo htmlspecialchars($u['facebook_name'] ?? ''); ?>" placeholder="e.g. Juan Dela Cruz">
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" name="update_profile" class="btn-primary-custom">
                        Save Profile Changes
                    </button>
                </div>
            </form>
        </div>

        <div class="section-card">
            <h4 class="section-title"><i class="bi bi-shield-lock"></i> Security & Password</h4>
            <form method="POST" autocomplete="off">
                <div class="row g-4">
                    <div class="col-md-12">
                        <label class="form-label">Current Password</label>
                        <div class="password-wrapper">
                            <input type="password" name="old_password" id="oldPassword" class="form-control" placeholder="Enter current password" required>
                            <i class="bi bi-eye-slash toggle-password" onclick="toggleVisibility('oldPassword', this)"></i>
                        </div>
                    </div>
                    
                    <div class="col-md-12">
                        <label class="form-label">New Password</label>
                        <div class="password-wrapper">
                            <input type="password" name="new_password" id="newPasswordInput" class="form-control" placeholder="Create a new strong password" required autocomplete="new-password">
                            <i class="bi bi-eye-slash toggle-password" onclick="toggleVisibility('newPasswordInput', this)"></i>
                        </div>
                        
                        <div id="strengthSection" class="d-none mt-2">
                            <div class="strength-meter-container">
                                <div id="strengthBar" class="strength-bar"></div>
                            </div>
                            <span id="strengthLabel" class="strength-label"></span>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" name="change_password" class="btn-secondary-custom">
                        Update Password
                    </button>
                </div>
            </form>
        </div>

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

    // 2. Real-time Password Strength Checker
    const passwordInput = document.getElementById('newPasswordInput');
    const strengthSection = document.getElementById('strengthSection');
    const strengthBar = document.getElementById('strengthBar');
    const strengthLabel = document.getElementById('strengthLabel');

    passwordInput.addEventListener('input', function() {
        const val = passwordInput.value;
        
        if (val.length === 0) {
            strengthSection.classList.add('d-none');
            return;
        }
        strengthSection.classList.remove('d-none');
        
        let score = 0;
        if (val.length >= 8) score++;
        if (val.match(/[a-z]/) && val.match(/[A-Z]/)) score++;
        if (val.match(/\d/)) score++;
        if (val.match(/[^a-zA-Z\d]/)) score++;

        strengthBar.className = 'strength-bar'; 
        strengthLabel.className = 'strength-label';

        if (val.length < 6) {
            strengthBar.style.width = '25%';
            strengthBar.classList.add('strength-weak');
            strengthLabel.classList.add('strength-weak');
            strengthLabel.innerText = 'Weak (Too short)';
        } else if (score < 2) {
            strengthBar.style.width = '50%';
            strengthBar.classList.add('strength-weak');
            strengthLabel.classList.add('strength-weak');
            strengthLabel.innerText = 'Weak';
        } else if (score < 4) {
            strengthBar.style.width = '75%';
            strengthBar.classList.add('strength-medium');
            strengthLabel.classList.add('strength-medium');
            strengthLabel.innerText = 'Medium';
        } else {
            strengthBar.style.width = '100%';
            strengthBar.classList.add('strength-strong');
            strengthLabel.classList.add('strength-strong');
            strengthLabel.innerText = 'Strong';
        }
    });

    // 3. Image Preview before upload
    function previewImage(event) {
        var reader = new FileReader();
        reader.onload = function() {
            var output = document.getElementById('imgPreview');
            output.src = reader.result;
        }
        if(event.target.files[0]) {
            reader.readAsDataURL(event.target.files[0]);
        }
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>