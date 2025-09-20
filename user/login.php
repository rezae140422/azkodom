<?php
session_start();
include "../includes/db_connect.php";

if($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username=?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        header("Location: user-panel.php");
        exit;
    } else {
        $error = "نام کاربری یا رمز اشتباه است.";
    }
}
?>
<?php include "../includes/header.php"; ?>

<h2>ورود</h2>
<?php if(isset($error)) echo "<p>$error</p>"; ?>
<form method="post">
    <input type="text" name="username" placeholder="نام کاربری" required>
    <input type="password" name="password" placeholder="رمز عبور" required>
    <button type="submit">ورود</button>
</form>

<?php include "../includes/footer.php"; ?>
