<?php
session_start();
include "../includes/db_connect.php";

// اگر ادمین لاگین بود مستقیم بفرست به پنل
if (isset($_SESSION['admin_id'])) {
    header("Location: admin-panel.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        header("Location: admin-panel.php");
        exit;
    } else {
        $error = "نام کاربری یا رمز عبور اشتباه است!";
    }
}
?>
<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <title>ورود مدیر</title>
    <style>
        body { font-family: Tahoma, sans-serif; background: #f4f4f4; }
        .login-box {
            width: 300px; margin: 100px auto; padding: 20px;
            background: #fff; box-shadow: 0 0 10px rgba(0,0,0,.2);
            border-radius: 8px;
        }
        h2 { text-align: center; }
        input {
            width: 100%; padding: 10px; margin: 10px 0;
            border: 1px solid #ccc; border-radius: 5px;
        }
        button {
            width: 100%; padding: 10px;
            background: #007bff; color: #fff;
            border: none; border-radius: 5px;
            cursor: pointer;
        }
        button:hover { background: #0056b3; }
        .error { color: red; text-align: center; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>ورود ادمین</h2>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="post">
            <input type="text" name="username" placeholder="نام کاربری" required>
            <input type="password" name="password" placeholder="رمز عبور" required>
            <button type="submit">ورود</button>
        </form>
    </div>
</body>
</html>
