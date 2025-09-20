<?php
session_start();
include "../includes/db_connect.php";

// بررسی لاگین بودن
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin-login.php");
    exit;
}

// گرفتن صفحه از URL
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
?>
<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <title>پنل مدیریت</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            background-color: #f4f6f9;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .sidebar {
            width: 250px;
            height: 100vh;
            background: #fff;
            color: #000;
            padding: 20px 15px;
            position: fixed;
            top: 0;
            right: 0;
            overflow-y: auto;
            border-left: 2px solid #f0f0f0;
        }
        .sidebar::-webkit-scrollbar { display: none; }
        .sidebar { scrollbar-width: none; }
        .sidebar h4 {
            font-weight: bold;
            margin-bottom: 1rem;
            color: #333;
        }
        .sidebar a {
            color: #000;
            display: block;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 5px;
            margin-bottom: 5px;
            font-size: 14px;
            transition: 0.3s;
        }
        .sidebar a:hover {
            color: #ff7f00;
            text-shadow: 0 0 5px #ff7f00, 0 0 10px #ff7f00;
            background: rgba(255,127,0,0.05);
        }
        .submenu {
            display: none;
            margin-right: 15px;
            font-size: 13px;
        }
        .submenu a {
            padding-right: 25px;
        }
        .content {
            margin-right: 260px;
            padding: 20px;
        }
        .sidebar .menu-item {
            cursor: pointer;
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 5px;
            transition: 0.3s;
        }
        .sidebar .menu-item:hover {
            color: #ff7f00;
            text-shadow: 0 0 5px #ff7f00, 0 0 10px #ff7f00;
            background: rgba(255,127,0,0.05);
        }
        .submenu.show {
            display: block;
        }
        .toggle-icon {
            float: left;
            transition: transform 0.3s;
        }
        .rotate {
            transform: rotate(90deg);
        }
    </style>
</head>
<body>
    <!-- سایدبار -->
    <div class="sidebar">
        <h4>پنل مدیریت</h4>

        <a href="admin-panel.php?page=dashboard">📊 داشبورد</a>

        <div class="menu-item">📦 محصولات <span class="toggle-icon">▶</span></div>
        <div class="submenu">
            <a href="products/index.php">لیست محصولات</a>
            <a href="products/add.php">افزودن محصول</a>
            <a href="products/brands.php">مدیریت برندها</a>
            <a href="products/categories.php">مدیریت دسته‌بندی</a>
            <a href="products/subcategories.php">مدیریت زیرمجموعه</a>
            <a href="products/features.php">ویژگی‌ها</a>
            <a href="products/reviews.php">نظرات محصول</a>
        </div>

        <a href="offers.php">🔥 پیشنهادات شگفت‌انگیز</a>
        <a href="orders.php">🛒 سفارشات</a>

        <div class="menu-item">📈 گزارشات <span class="toggle-icon">▶</span></div>
        <div class="submenu">
            <a href="reports/sales.php">گزارش فروش</a>
            <a href="reports/stock.php">گزارش انبار</a>
            <a href="reports/customers.php">گزارش مشتریان</a>
            <a href="reports/wishlist.php">گزارش علاقه‌مندی‌ها</a>
        </div>

        <a href="customers.php">👥 مشتریان</a>

        <div class="menu-item">⚙️ تنظیمات <span class="toggle-icon">▶</span></div>
        <div class="submenu">
            <a href="settings/header.php">تنظیمات هدر</a>
            <a href="settings/homepage.php">صفحه اصلی</a>
            <a href="settings/pages.php">صفحات جانبی</a>
            <a href="settings/sms.php">پنل پیامکی</a>
            <a href="settings/footer.php">فوتر</a>
            <a href="settings/slider.php">اسلایدر</a>
            <a href="settings/checkout.php">صفحه پرداخت</a>
            <a href="settings/payment.php">درگاه پرداخت</a>
            <a href="settings/logo.php">لوگو</a>
        </div>

        <div class="menu-item">🎫 تیکت‌ها <span class="toggle-icon">▶</span></div>
        <div class="submenu">
            <a href="tickets/departments.php">دپارتمان‌ها</a>
            <a href="tickets/received.php">دریافتی‌ها</a>
            <a href="tickets/sent.php">ارسالی‌ها</a>
        </div>

        <a href="employees.php">👨‍💼 کارمندان</a>
        <a href="coupons.php">🏷️ کوپن تخفیف</a>

        <div class="menu-item">📝 وبلاگ <span class="toggle-icon">▶</span></div>
        <div class="submenu">
            <a href="blog/index.php">لیست وبلاگ‌ها</a>
            <a href="blog/add.php">نوشتن وبلاگ</a>
        </div>

        <a href="logout.php" class="text-danger">🚪 خروج</a>
    </div>

    <!-- محتوای اصلی -->
    <div class="content">
        <?php
        $file = $page . ".php";
        if (file_exists($file)) {
            include $file;
        } else {
            echo "<h2>صفحه مورد نظر یافت نشد.</h2>";
        }
        ?>
    </div>

    <script>
        const menuItems = document.querySelectorAll('.menu-item');
        menuItems.forEach(item => {
            item.addEventListener('click', () => {
                const submenu = item.nextElementSibling;
                const icon = item.querySelector('.toggle-icon');
                submenu.classList.toggle('show');
                icon.classList.toggle('rotate');
            });
        });
    </script>
</body>
</html>
