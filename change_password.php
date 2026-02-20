<?php
session_start();
include 'db.php';


if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['user'];
$message = "";

if (isset($_POST['update_password'])) {
    $current_pass = $_POST['current_password'];
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

   
    $stmt = $conn->prepare("SELECT password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

  
    if (password_verify($current_pass, $row['password'])) {
       
        if ($new_pass === $confirm_pass) {
          
            $new_hashed_pass = password_hash($new_pass, PASSWORD_DEFAULT);
            $update = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
            $update->bind_param("ss", $new_hashed_pass, $email);
            
            if ($update->execute()) {
                $message = "<div class='alert alert-success'>Password changed successfully!</div>";
            } else {
                $message = "<div class='alert alert-danger'>Database error.</div>";
            }
        } else {
            $message = "<div class='alert alert-danger'>New passwords do not match!</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'>Current password is incorrect!</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Change Password - BoardHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        
        body { background: #111; color: #e0e0e0; font-family: 'Arial Black', sans-serif; }
        
        .topbar { background: #000; padding: 15px; text-align: center; border-bottom: 4px solid #ff9000; }
        .brand-orange { background: #ff9000; color: black; padding: 2px 8px; border-radius: 4px; }
        h2 { color: white; margin: 0; }

        .auth-card { 
            background: #1c1c1c; 
            border-radius: 6px; 
            padding: 40px; 
            margin-top: 50px; 
            box-shadow: 0 0 30px rgba(0,0,0,0.8); 
        }

      
        h4 { color: white !important; }
        .text-muted { color: #aaa !important; }
        label { color: #e0e0e0 !important; font-weight: bold; font-family: Arial, sans-serif; font-size: 14px; margin-bottom: 5px; }

     
        .form-control { 
            background: #2a2a2a; 
            border: 1px solid #333; 
            color: white !important; /* Force text white */
            height: 50px; 
            font-weight: bold; 
        }
        .form-control:focus { 
            background: #2a2a2a; 
            border-color: #ff9000; 
            color: white; 
            box-shadow: none; 
        }

       
        .btn-orange { 
            background: #ff9000; 
            border: none; 
            font-weight: 900; 
            height: 55px; 
            font-size: 18px; 
            color: black; 
            text-transform: uppercase; 
        }
        .btn-orange:hover { background: #e67e00; color: black; }

       
        a { color: #ff9000; text-decoration: none; font-weight: bold; }
        a:hover { color: #ffa733; text-decoration: underline; }
    </style>
</head>
<body>

    <div class="topbar">
        <h2>Board<span class="brand-orange">Hub</span></h2>
    </div>

    <div class="container d-flex justify-content-center">
        <div class="auth-card" style="width: 500px;">
            <h4 class="text-center mb-4">CHANGE PASSWORD</h4>
            <p class="text-center text-muted mb-4">Update your account security.</p>

            <?php echo $message; ?>

            <form method="POST">
                
                <div class="mb-3">
                    <label>Current Password</label>
                    <input type="password" name="current_password" class="form-control" placeholder="Type your old password" required>
                </div>

                <hr style="border-color: #444; margin: 25px 0;">

                <div class="mb-3">
                    <label>New Password</label>
                    <input type="password" name="new_password" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>

                <button type="submit" name="update_password" class="btn btn-orange w-100 mt-2">
                    SAVE NEW PASSWORD
                </button>

            </form>

            <div class="text-center mt-4">
                <a href="dashboard.php">← Back to Dashboard</a>
            </div>
        </div>
    </div>

</body>
</html>