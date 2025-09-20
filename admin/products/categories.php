<?php
session_start();
include "../../includes/db_connect.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../admin-login.php");
    exit;
}

// حذف دسته‌بندی
if(isset($_GET['delete_id'])){
    $del_id = (int)$_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM categories WHERE id=?");
    $stmt->execute([$del_id]);
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

// افزودن / ویرایش دسته‌بندی
if(isset($_POST['add_category']) || isset($_POST['edit_category'])){
    $name = $_POST['name'];

    if(isset($_POST['add_category'])){
        $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->execute([$name]);
    }

    if(isset($_POST['edit_category'])){
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("UPDATE categories SET name=? WHERE id=?");
        $stmt->execute([$name, $id]);
    }

    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

// گرفتن دسته‌بندی‌ها
$categories = $conn->query("SELECT * FROM categories ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fa">
<head>
<meta charset="UTF-8">
<title>مدیریت دسته‌بندی‌ها</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<style>
body { background:#f4f6f9; margin:0; font-family: Tahoma, sans-serif; }
.sidebar {
    width: 250px; height: 100vh; background: #fff; color:#000; padding:20px 15px; position: fixed; top:0; right:0; overflow-y:auto; border-left:2px solid #ff6700;
}
.sidebar::-webkit-scrollbar { display: none; }
.sidebar { scrollbar-width: none; }
.sidebar h4 { font-weight:bold; margin-bottom:1rem; color:#ff6700; }
.sidebar a, .menu-item { color:#000; display:block; padding:10px 15px; text-decoration:none; border-radius:5px; margin-bottom:5px; cursor:pointer; transition:0.3s; }
.sidebar a:hover, .menu-item:hover { color:#ff7f00; text-shadow:0 0 5px #ff7f00,0 0 10px #ff7f00; background: rgba(255,127,0,0.05); }
.submenu { display:none; margin-right:15px; font-size:13px; }
.submenu a { padding-right:25px; }
.submenu.show { display:block; }
.toggle-icon { float:left; transition: transform 0.3s; }
.rotate { transform: rotate(90deg); }
.content { margin-right:260px; padding:20px; }
table { width:100%; border-collapse: collapse; margin-top:20px; background:#fff; color:#000; border-radius:8px; overflow:hidden; }
th, td { padding:10px; text-align:right; border-bottom:1px solid #ddd; }
th { background:#0f1423; color:#00f0ff; }
tr:hover { background:#ff6700; color:#fff; transition:0.3s; }
button { cursor:pointer; border:none; border-radius:6px; padding:8px 15px; margin:2px; }
.btn-add { background:#00f0ff; color:#fff; }
.btn-add:hover { background:#ff6700; }
#categoryModal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; }
.modal-content { background:#fff; width:400px; margin:100px auto; padding:20px; border-radius:10px; position:relative; }
.modal-content h3 { color:#ff6700; margin-bottom:15px; }
.modal-content label { display:block; margin-top:10px; margin-bottom:5px; color:#000; }
.modal-content input { width:100%; padding:8px; margin-bottom:10px; border-radius:5px; border:1px solid #ccc; }
.modal-content button { margin-top:5px; }
.close-modal { position:absolute; top:10px; right:15px; cursor:pointer; font-size:20px; color:#000; }
</style>
</head>
<body>

<!-- سایدبار -->
<div class="sidebar">
<h4>پنل مدیریت</h4>
<a href="../admin-panel.php?page=dashboard">📊 داشبورد</a>

<div class="menu-item">📦 محصولات <span class="toggle-icon">▶</span></div>
<div class="submenu">
<a href="index.php">لیست محصولات</a>
<a href="add.php">افزودن محصول</a>
<a href="brands.php">مدیریت برندها</a>
<a href="categories.php">مدیریت دسته‌بندی</a>
<a href="subcategories.php">مدیریت زیرمجموعه</a>
<a href="features.php">ویژگی‌ها</a>
<a href="reviews.php">نظرات محصول</a>
</div>

<a href="../orders.php">🛒 سفارشات</a>
<a href="../customers.php">👥 مشتریان</a>
<a href="../logout.php" class="text-danger">🚪 خروج</a>
</div>

<div class="content">
<h2>📂 مدیریت دسته‌بندی‌ها</h2>
<button class="btn-add" onclick="openModal()">➕ افزودن دسته‌بندی</button>

<table>
<tr>
<th>#</th>
<th>نام دسته‌بندی</th>
<th>عملیات</th>
</tr>
<?php foreach($categories as $c): ?>
<tr>
<td><?= $c['id'] ?></td>
<td><?= htmlspecialchars($c['name']) ?></td>
<td>
<button class="btn-add" onclick="openEditModal(<?= $c['id'] ?>, '<?= htmlspecialchars(addslashes($c['name'])) ?>')">ویرایش</button>
<button style="background:#ff6700; color:#fff;" onclick="if(confirm('آیا مطمئن هستید؟')){ window.location='?delete_id=<?= $c['id'] ?>'; }">حذف</button>
</td>
</tr>
<?php endforeach; ?>
</table>

<!-- مودال افزودن/ویرایش دسته‌بندی -->
<div id="categoryModal">
<div class="modal-content">
<span class="close-modal" onclick="closeModal()">&times;</span>
<h3 id="modalTitle">افزودن دسته‌بندی</h3>
<form method="post">
<input type="hidden" name="id" id="categoryId">
<label>نام دسته‌بندی</label>
<input type="text" name="name" id="categoryName" required>
<button type="submit" name="add_category" id="submitBtn" class="btn-add">ذخیره</button>
<button type="button" onclick="closeModal()" style="background:#ff6700; color:#fff; padding:8px 15px; border:none; border-radius:6px;">انصراف</button>
</form>
</div>
</div>

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

function openModal(){
    document.getElementById('modalTitle').innerText = 'افزودن دسته‌بندی';
    document.getElementById('submitBtn').name = 'add_category';
    document.getElementById('categoryId').value = '';
    document.getElementById('categoryName').value = '';
    document.getElementById('categoryModal').style.display = 'block';
}

function openEditModal(id, name){
    document.getElementById('modalTitle').innerText = 'ویرایش دسته‌بندی';
    document.getElementById('submitBtn').name = 'edit_category';
    document.getElementById('categoryId').value = id;
    document.getElementById('categoryName').value = name;
    document.getElementById('categoryModal').style.display = 'block';
}

function closeModal(){
    document.getElementById('categoryModal').style.display = 'none';
}
</script>
</body>
</html>
