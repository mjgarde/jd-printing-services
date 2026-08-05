<?php
session_start();
require_once '../config/db.php';

if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit();
}

$loginError = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login_submit'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM admin WHERE username = '$username'";
    $result = mysqli_query($conn, $sql);

    if ($result === false) {
        $loginError = "Login failed: " . mysqli_error($conn);
    } elseif (mysqli_num_rows($result) == 1) {
        $admin = mysqli_fetch_assoc($result);

        if ($password == $admin['password']) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            header("Location: dashboard.php");
            exit();
        } else {
            $loginError = "Incorrect password.";
        }
    } else {
        $loginError = "Username not found.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../font/css/all.min.css">
    <link rel="stylesheet" href="admin.css">
</head>
<body>

<div class="login-wrapper">
    <div class="login-box">
        <div class="login-brand">
            <div class="brand-rule"></div>
            <h1>JD Printing Admin</h1>
            <p>Sign in to access the admin panel.</p>
        </div>

        <?php if ($loginError != "") { ?>
            <div class="login-error"><?php echo $loginError; ?></div>
        <?php } ?>

        <form method="POST" action="login.php" class="login-form">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" name="login_submit" class="login-submit">Login</button>
        </form>
    </div>
</div>

</body>
</html>