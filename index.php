<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: " . $_SESSION['role'] . "/dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>AgroConnect — Login</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="login-wrapper">
    <div class="login-box">
        <h1>🌾 AgroConnect</h1>
        <p class="subtitle">Agro Management System</p>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-error"><?= htmlspecialchars($_GET['error']) ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_GET['msg']) ?></div>
        <?php endif; ?>

        <form action="auth/login.php" method="POST">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required placeholder="Enter your email">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required placeholder="Enter your password">
            </div>
            <button type="submit" class="btn btn-green" style="width:100%">Login</button>
        </form>
        <p style="text-align:center; margin-top:16px; font-size:13px; color:#888;">
            Don't have an account? <a href="auth/register.php" style="color:#2e7d32; font-weight:600;">Register</a>
        </p>
    </div>
</div>
</body>
</html>