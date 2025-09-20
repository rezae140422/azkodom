<?php 
session_start();
include "../includes/db_connect.php"; 
include "../includes/header.php"; 

if(!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// افزودن به سبد
if(isset($_GET['add'])) {
    $id = intval($_GET['add']);
    if(isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id]++;
    } else {
        $_SESSION['cart'][$id] = 1;
    }
}

if(!empty($_SESSION['cart'])) {
    $ids = implode(",", array_keys($_SESSION['cart']));
    $stmt = $conn->query("SELECT * FROM products WHERE id IN ($ids)");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $products = [];
}
?>

<h2>سبد خرید</h2>
<?php if(empty($products)): ?>
    <p>سبد خرید شما خالی است.</p>
<?php else: ?>
    <ul>
    <?php foreach($products as $p): ?>
        <li>
            <?php echo htmlspecialchars($p['name']); ?> 
            - تعداد: <?php echo $_SESSION['cart'][$p['id']]; ?> 
            - قیمت: <?php echo number_format($p['price'] * $_SESSION['cart'][$p['id']]); ?> تومان
        </li>
    <?php endforeach; ?>
    </ul>
    <a href="checkout.php">تسویه حساب</a>
<?php endif; ?>

<?php include "../includes/footer.php"; ?>
