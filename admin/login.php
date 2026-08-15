<?php
session_start();
require_once '../config/db.php';

if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit();
}

$loginError = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login_submit'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = mysqli_prepare($conn, "SELECT id, username, password FROM admin WHERE username = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) == 1) {
        $admin = mysqli_fetch_assoc($result);
        $passwordMatches = password_verify($password, $admin['password']) || $password == $admin['password'];

        if ($passwordMatches) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            header("Location: dashboard.php");
            exit();
        } else {
            $loginError = "Incorrect username or password.";
        }
    } else {
        $loginError = "Incorrect username or password.";
    }

    mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Login | JD Printing Services</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="../font/css/all.min.css">
<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Arial, sans-serif;
    background-color: #eef1f5;
    color: #2b2f38;
    -webkit-font-smoothing: antialiased;
}

.login-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px;
    background-color: #eef1f5;
}

.login-panel {
    width: 100%;
    max-width: 420px;
    background-color: #ffffff;
    border: 1px solid #d7dce3;
    border-radius: 4px;
}

.login-panel-body {
    padding: 32px 28px 28px 28px;
}

.login-title {
    margin-bottom: 20px;
}

.login-title h1 {
    font-size: 16px;
    font-weight: 600;
    color: #1f2530;
}

.alert-message {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 11px 14px;
    border-radius: 4px;
    font-size: 13px;
    margin-bottom: 20px;
}

.alert-error {
    background-color: #fbeaea;
    border: 1px solid #eec7c7;
    color: #a13c3c;
}

.alert-error i {
    font-size: 14px;
}

.login-form {
    display: flex;
    flex-direction: column;
    gap: 18px;
}

.input-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.input-group label {
    font-size: 13px;
    font-weight: 600;
    color: #374151;
}

.input-control {
    position: relative;
    display: flex;
    align-items: center;
}

.input-control i {
    position: absolute;
    left: 13px;
    font-size: 13px;
    color: #8a93a3;
}

.input-control input {
    width: 100%;
    padding: 11px 14px 11px 38px;
    border: 1px solid #cbd2dc;
    border-radius: 4px;
    font-size: 14px;
    color: #1f2530;
    background-color: #fbfcfd;
    outline: none;
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
}

.input-control input:focus {
    border-color: #4472a8;
    box-shadow: 0 0 0 3px rgba(68, 114, 168, 0.12);
    background-color: #ffffff;
}

.input-control input::placeholder {
    color: #a3aab5;
    font-size: 13px;
}

.login-submit-btn {
    margin-top: 4px;
    padding: 12px;
    background-color: #1f3a5f;
    color: #ffffff;
    border: none;
    border-radius: 4px;
    font-size: 14px;
    font-weight: 600;
    letter-spacing: 0.3px;
    cursor: pointer;
    transition: background-color 0.15s ease;
}

.login-submit-btn:hover {
    background-color: #17304e;
}

.login-submit-btn:active {
    background-color: #122843;
}

.login-panel-footer {
    padding: 18px 28px 24px 28px;
    border-top: 1px solid #e7eaf0;
    margin-top: 22px;
    text-align: center;
}

.login-panel-footer p {
    font-size: 11px;
    color: #9aa1ab;
    line-height: 1.6;
}

@media (max-width: 480px) {
    .login-panel-body {
        padding: 26px 20px 8px 20px;
    }

    .login-panel-footer {
        padding: 16px 20px 20px 20px;
    }
}
</style>
</head>
<body>

<div class="login-page">
    <div class="login-panel">
        <div class="login-panel-body">
            <div class="login-title">
                <h1>Administrator Sign In</h1>
            </div>

            <?php if ($loginError != "") { ?>
                <div class="alert-message alert-error">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span><?php echo htmlspecialchars($loginError); ?></span>
                </div>
            <?php } ?>

            <form method="POST" action="login.php" class="login-form" autocomplete="off">
                <div class="input-group">
                    <label for="username">Username</label>
                    <div class="input-control">
                        <i class="fa-solid fa-user"></i>
                        <input type="text" id="username" name="username" placeholder="Enter your username" required autofocus>
                    </div>
                </div>

                <div class="input-group">
                    <label for="password">Password</label>
                    <div class="input-control">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    </div>
                </div>

                <button type="submit" name="login_submit" class="login-submit-btn">
                    Sign In
                </button>
            </form>
        </div>

    </div>
</div>

</body>
</html>