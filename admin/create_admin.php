<?php
include "../includes/db_connect.php";

$username = "admin";
$password = "123456";

// هش کردن پسورد
$hashed = password_hash($password, PASSWORD_DEFAULT);

// درج در دیتابیس
$stmt = $conn->prepare("INSERT INTO admins (username, password) VALUES (?, ?)");
$stmt->execute([$username, $hashed]);

echo "ادمین ساخته شد: admin / 123456";
