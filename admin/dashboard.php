<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin-login.php");
    exit;
}

include "../includes/db_connect.php";

// آمار کلی
$total_categories = $conn->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$total_subcategories = $conn->query("SELECT COUNT(*) FROM subcategories")->fetchColumn();
$total_sub_subcategories = $conn->query("SELECT COUNT(*) FROM sub_subcategories")->fetchColumn();
$total_products = $conn->query("SELECT COUNT(*) FROM products")->fetchColumn();
$total_orders = $conn->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$total_users = $conn->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetchColumn();
?>

<style>
body {
    font-family: Tahoma, sans-serif;
    background-color: #0f1423;
    color: #fff;
    margin: 0;
    padding: 20px;
}
h1, h2 { color: #00f0ff; }

.cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
}

.card {
    background-color: #1b1f3b;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,255,255,0.2);
    text-align: center;
    transition: transform 0.3s, box-shadow 0.3s;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,255,255,0.4);
}

.card h3 {
    color: #ff6700;
    margin-bottom: 10px;
}

.card p {
    font-size: 24px;
    margin: 10px 0;
}

.card a {
    display: inline-block;
    margin-top: 10px;
    padding: 8px 14px;
    background-color: #00f0ff;
    color: #1b1f3b;
    text-decoration: none;
    border-radius: 6px;
    transition: background 0.3s;
}

.card a:hover {
    background-color: #ff6700;
    color: #fff;
}

table {
    width: 100%;
    border-collapse: collapse;
    background-color: #1b1f3b;
    border-radius: 12px;
    overflow: hidden;
}

table th, table td {
    padding: 12px;
    text-align: center;
}

table th {
    background-color: #0f1423;
    color: #00f0ff;
}

table tr:nth-child(even) {
    background-color: #161a36;
}

table tr:hover {
    background-color: #ff6700;
    color: #1b1f3b;
    font-weight: bold;
}
</style>

<h1>📊 داشبورد مدیریت</h1>

<div class="cards">
    <div class="card">
        <h3>دسته‌بندی‌ها</h3>
        <p><?= $total_categories ?></p>
        <a href="products/categories.php">ایجاد دسته‌بندی</a>
    </div>

    <div class="card">
        <h3>زیرمجموعه‌ها</h3>
        <p><?= $total_subcategories ?></p>
        <a href="products/subcategories.php">ایجاد زیرمجموعه</a>
    </div>

    <div class="card">
        <h3>زیرمجموعه زیرمجموعه‌ها</h3>
        <p><?= $total_sub_subcategories ?></p>
        <a href="products/sub_subcategories.php">ایجاد زیرمجموعه زیرمجموعه</a>
    </div>

    <div class="card">
        <h3>محصولات</h3>
        <p><?= $total_products ?></p>
        <a href="products/index.php">مدیریت محصولات</a>
    </div>

    <div class="card">
        <h3>سفارش‌ها</h3>
        <p><?= $total_orders ?></p>
        <a href="orders.php">مدیریت سفارش‌ها</a>
    </div>

    <div class="card">
        <h3>کاربران</h3>
        <p><?= $total_users ?></p>
        <a href="customers.php">مدیریت کاربران</a>
    </div>
</div>

<h2>🛒 جدول سفارش‌ها</h2>
<table>
<tr>
    <th>شماره سفارش</th>
    <th>کاربر</th>
    <th>مبلغ کل</th>
    <th>وضعیت</th>
    <th>تاریخ</th>
</tr>
<?php
$stmt = $conn->query("SELECT o.*, u.username FROM orders o LEFT JOIN users u ON o.user_id=u.id ORDER BY o.id DESC LIMIT 10");
foreach ($stmt as $row): ?>
<tr>
    <td><?= $row['id'] ?></td>
    <td><?= htmlspecialchars($row['username']) ?></td>
    <td><?= number_format($row['total_amount']) ?> تومان</td>
    <td><?= $row['status'] ?></td>
    <td><?= $row['created_at'] ?></td>
</tr>
<?php endforeach; ?>
</table>
