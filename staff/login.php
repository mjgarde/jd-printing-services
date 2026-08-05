<?php
session_start();
require_once '../config/db.php';

if (isset($_SESSION['staff_id'])) {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM staff WHERE username = '$username'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {
        $staff = mysqli_fetch_assoc($result);

        if ($password == $staff['password']) {
            $_SESSION['staff_id'] = $staff['id'];
            $_SESSION['staff_username'] = $staff['username'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "Username not found.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - JD Printing Services</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background-color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        .login-box {
            background-color: #ffffff;
            padding: 30px 28px;
            border-radius: 10px;
            width: 320px;
            border: 1px solid #000000;
        }

        .login-box h1 {
            font-size: 18px;
            margin-bottom: 4px;
            color: #000000;
        }

        .login-box p {
            font-size: 12px;
            color: #555555;
            margin-bottom: 20px;
        }

        .login-box input {
            width: 100%;
            padding: 10px 12px;
            margin-bottom: 12px;
            border: 1px solid #000000;
            border-radius: 6px;
            font-size: 14px;
            color: #000000;
        }

        .login-box button {
            width: 100%;
            padding: 10px;
            background-color: #000000;
            color: #ffffff;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
        }

        .login-box button:hover {
            background-color: #333333;
        }

        .error-msg {
            background-color: #000000;
            color: #ffffff;
            font-size: 12px;
            padding: 8px 10px;
            border-radius: 6px;
            margin-bottom: 12px;
        }
    </style>
</head>
<body>

<div class="login-box">
    <h1>JD Printing Services</h1>
    <p>Sign in to continue</p>

    <?php if (isset($error)) { ?>
        <div class="error-msg"><?php echo $error; ?></div>
    <?php } ?>

    <form method="POST" action="login.php">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>
</div>

</body>
</html>