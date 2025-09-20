<?php
// start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <title>فروشگاه لوازم خانگی</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <header>
        <h1>فروشگاه لوازم خانگی</h1>
        <nav>
            <a href="/index.php">خانه</a>
            <a href="/shop/category.php">دسته‌بندی‌ها</a>
            <a href="/shop/product.php">محصولات</a>
            <a href="/orders/cart.php">سبد خرید</a>
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="/user/user-panel.php">پنل کاربری</a>
                <a href="/user/logout.php">خروج</a>
            <?php else: ?>
                <a href="/user/login.php">ورود</a>
            <?php endif; ?>
        </nav>
    </header>
    <main>
