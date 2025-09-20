<?php 
include "../includes/db_connect.php"; 
include "../includes/header.php"; 

$stmt = $conn->query("SELECT * FROM categories");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>دسته‌بندی‌ها</h2>
<ul>
<?php foreach($categories as $cat): ?>
    <li>
        <a href="product.php?cat=<?php echo $cat['id']; ?>">
            <?php echo htmlspecialchars($cat['name']); ?>
        </a>
    </li>
<?php endforeach; ?>
</ul>

<?php include "../includes/footer.php"; ?>
