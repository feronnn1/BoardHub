<?php
session_start();
include 'db.php';

// SECURITY
if (!isset($_SESSION['user'])) { header("Location: login.php"); exit(); }
$username = $_SESSION['user'];
$user = $conn->query("SELECT id FROM users WHERE username='$username'")->fetch_assoc();
$landlord_id = $user['id'];

// CHECK EXISTING
$check = $conn->query("SELECT id FROM properties WHERE landlord_id = $landlord_id LIMIT 1");
if ($check->num_rows > 0) {
    $house_id = $check->fetch_assoc()['id'];
    header("Location: edit_room.php?id=$house_id");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Post Boarding House</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body { background: #111; color: #e0e0e0; font-family: 'Segoe UI', sans-serif; padding-bottom: 50px; }
        .container-custom { max-width: 900px; margin: 40px auto; }
        .section-card { background: #1c1c1c; padding: 30px; border-radius: 12px; border: 1px solid #333; margin-bottom: 20px; }
        
        /* DARK INPUTS */
        .form-control, .form-select { background-color: #2a2a2a !important; border: 1px solid #444; color: white !important; }
        .form-control:focus, .form-select:focus { border-color: #ff9000; box-shadow: none; }
        
        /* PILL CHECKBOXES */
        .feature-checkbox { display: none; }
        .feature-label { 
            display: inline-block; padding: 8px 16px; background: #2a2a2a; border: 1px solid #444; 
            border-radius: 30px; cursor: pointer; margin: 0 5px 10px 0; font-size: 13px; transition: 0.2s; color: #aaa;
        }
        .feature-checkbox:checked + .feature-label { background: #ff9000; color: #000; border-color: #ff9000; font-weight: bold; }
        
        /* BLUE PILLS FOR PAID ITEMS */
        .paid-checkbox { display: none; }
        .paid-label { 
            display: inline-block; padding: 8px 16px; background: #2a2a2a; border: 1px dashed #0dcaf0; 
            border-radius: 30px; cursor: pointer; margin: 0 5px 10px 0; font-size: 13px; transition: 0.2s; color: #0dcaf0;
        }
        .paid-checkbox:checked + .paid-label { background: #0dcaf0; color: #000; font-weight: bold; }

        .room-row { background: #252525; padding: 20px; border-radius: 8px; margin-bottom: 15px; border: 1px solid #333; }
        .btn-add { background: #333; color: #ff9000; border: 1px dashed #ff9000; width: 100%; padding: 10px; }
        .btn-submit { background: #ff9000; color: black; font-weight: bold; width: 100%; height: 50px; border: none; font-size: 18px; }
    </style>
</head>
<body>

<div class="container-custom px-3">
    <h3 class="fw-bold text-white mb-4 text-center">Post Boarding House</h3>
    
    <form action="save_room.php" method="POST" enctype="multipart/form-data">

        <div class="section-card">
            <h5 class="text-warning mb-3">General Information</h5>
            <div class="mb-3"><label>Boarding House Name</label><input type="text" name="title" class="form-control" required></div>
            <div class="mb-3"><label>Location</label><input type="text" name="location" class="form-control" required></div>
            <div class="mb-3"><label>Description</label><textarea name="description" class="form-control" rows="3"></textarea></div>
        </div>

        <div class="section-card">
            <h5 class="text-warning mb-3">Utilities & Pricing</h5>
            <div class="row g-3">
                <div class="col-md-6"><label>WiFi Type</label><select name="wifi_type" class="form-select"><option value="None">None</option><option value="Free WiFi">Free WiFi (Included)</option><option value="Piso WiFi">Piso WiFi (Pay per use)</option><option value="Shared Payment">Shared Payment</option></select></div>
                <div class="col-md-6"><label>Water Source</label><select name="water_type" class="form-select"><option value="Tap/Cleaning Only">Tap (Cleaning Only)</option><option value="Mineral/Drinking">Mineral (Free Drinking)</option></select></div>
                <div class="col-md-6"><label>Whole Room Price</label><input type="number" name="price" class="form-control" placeholder="0.00"></div>
                <div class="col-md-6"><label>Per Head Price</label><input type="number" name="price_shared" class="form-control" placeholder="0.00"></div>
            </div>
        </div>

        <div class="section-card">
            <h5 class="text-warning mb-3">Inclusions & Add-ons</h5>
            
            <label class="d-block mb-2 text-white small">Included in Monthly Rent (Free)</label>
            <div class="mb-3">
                <?php 
                $free_opts = ["Water", "Electricity", "WiFi", "Gas", "Beddings", "Cabinet", "Study Table"];
                foreach($free_opts as $opt) {
                    echo "<input type='checkbox' name='inclusions[]' value='$opt' id='inc_$opt' class='feature-checkbox'>
                          <label for='inc_$opt' class='feature-label'>$opt</label>";
                }
                ?>
            </div>

            <label class="d-block mb-2 text-info small">Available with Extra Payment (Paid Add-ons)</label>
            <div class="mb-3">
                <?php 
                $paid_opts = ["Drinking Water", "Refrigerator Use", "Rice Cooker", "Heater", "Laptop Charging", "Electric Fan"];
                foreach($paid_opts as $opt) {
                    echo "<input type='checkbox' name='paid_addons[]' value='$opt' id='paid_$opt' class='paid-checkbox'>
                          <label for='paid_$opt' class='paid-label'>+ $opt</label>";
                }
                ?>
            </div>
        </div>

        <div class="section-card">
            <h5 class="text-warning mb-3">Rooms</h5>
            <div id="room-list">
                <div class="room-row">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-4"><label class="small text-muted">Name</label><input type="text" name="room_names[]" class="form-control" placeholder="Room 1" required></div>
                        <div class="col-md-2"><label class="small text-muted">Total</label><input type="number" name="room_beds[]" class="form-control bed-total" value="4" min="1" required oninput="calculateVacancy(this)"></div>
                        <div class="col-md-2"><label class="small text-muted">Occupied</label><input type="number" name="room_occupied[]" class="form-control bed-occ" value="0" min="0" max="4" required oninput="calculateVacancy(this)"></div>
                        <div class="col-md-4"><label class="small text-muted">Photo</label><input type="file" name="room_specific_img[]" class="form-control form-control-sm" accept="image/*"></div>
                    </div>
                </div>
            </div>
            <button type="button" class="btn-add" onclick="addRoomField()">+ Add Room</button>
        </div>

        <div class="section-card">
            <h5 class="text-warning mb-3">General House Photos</h5>
            <input type="file" name="room_images[]" class="form-control" multiple accept="image/*" required>
        </div>

        <button type="submit" name="post_room" class="btn-submit rounded">Publish</button>

    </form>
</div>

<script>
    function calculateVacancy(input) {
        const row = input.closest('.room-row');
        const total = parseInt(row.querySelector('.bed-total').value) || 0;
        const occInput = row.querySelector('.bed-occ');
        let occupied = parseInt(occInput.value) || 0;
        if (occupied > total) { occupied = total; occInput.value = total; }
        occInput.setAttribute('max', total);
    }

    function addRoomField() {
        const html = `
            <div class="room-row">
                <div class="row g-2 align-items-end">
                    <div class="col-md-4"><input type="text" name="room_names[]" class="form-control" placeholder="Room X" required></div>
                    <div class="col-md-2"><input type="number" name="room_beds[]" class="form-control bed-total" value="4" min="1" required oninput="calculateVacancy(this)"></div>
                    <div class="col-md-2"><input type="number" name="room_occupied[]" class="form-control bed-occ" value="0" min="0" max="4" required oninput="calculateVacancy(this)"></div>
                    <div class="col-md-3"><input type="file" name="room_specific_img[]" class="form-control form-control-sm" accept="image/*"></div>
                    <div class="col-md-1"><button type="button" class="btn btn-sm btn-danger w-100" onclick="this.closest('.room-row').remove()">X</button></div>
                </div>
            </div>`;
        document.getElementById('room-list').insertAdjacentHTML('beforeend', html);
    }
</script>

</body>
</html>