<?php 
include "../includes/db_connect.php"; 
include "../includes/header.php"; 

$cat_id = isset($_GET['cat']) ? intval($_GET['cat']) : 0;
if($cat_id > 0){
    $stmt = $conn->prepare("SELECT * FROM products WHERE category_id = ?");
    $stmt->execute([$cat_id]);
} else {
    $stmt = $conn->query("SELECT * FROM products");
}
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>محصولات</h2>
<div class="products">
<?php foreach($products as $p): ?>
    <div class="product">
        <h3><?php echo htmlspecialchars($p['name']); ?></h3>
        <p>قیمت: <?php echo number_format($p['price']); ?> تومان</p>
        <a href="../orders/cart.php?add=<?php echo $p['id']; ?>">افزودن به سبد</a>
    </div>
<?php endforeach; ?>
</div>

<?php include "../includes/footer.php"; ?>
