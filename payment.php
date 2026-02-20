<?php
session_start();
include 'db.php';
if (!isset($_SESSION['user'])) { header("Location: login.php"); exit(); }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Pay Rent</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #121212; color: white; display: flex; align-items: center; justify-content: center; height: 100vh; font-family: sans-serif; }
        .pay-card { background: #1e1e1e; padding: 40px; border-radius: 20px; width: 400px; border: 1px solid #333; text-align: center; }
        .amount { font-size: 40px; font-weight: bold; color: #ff9000; margin: 20px 0; }
        .btn-pay { background: #ff9000; border: none; padding: 15px; width: 100%; font-weight: bold; border-radius: 10px; margin-top: 20px; }
        .btn-pay:hover { background: #e08e00; }
        .method-opt { background: #2a2a2a; padding: 10px; border-radius: 8px; margin-bottom: 10px; border: 1px solid #333; cursor: pointer; display: flex; align-items: center; gap: 10px; }
        .method-opt:hover { border-color: #ff9000; }
        input[type="radio"] { accent-color: #ff9000; }
    </style>
</head>
<body>

<div class="pay-card">
    <h5>Complete Payment</h5>
    <div class="amount">₱3,500.00</div>
    <p class="text-secondary small mb-4">Rent for March 2026</p>

    <form action="dashboard_tenant.php">
        <label class="method-opt">
            <input type="radio" name="method" checked> 
            <span>GCash</span>
        </label>
        <label class="method-opt">
            <input type="radio" name="method"> 
            <span>Credit / Debit Card</span>
        </label>
        <label class="method-opt">
            <input type="radio" name="method"> 
            <span>Maya</span>
        </label>

        <button type="submit" class="btn-pay" onclick="alert('Payment Successful! (Demo)')">Pay Now</button>
        <a href="dashboard_tenant.php" class="text-secondary text-decoration-none small mt-3 d-block">Cancel</a>
    </form>
</div>

</body>
</html>