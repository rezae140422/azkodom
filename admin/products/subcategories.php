<?php
session_start();
include "../../includes/db_connect.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../admin-login.php");
    exit;
}

// حذف زیرمجموعه
if(isset($_GET['delete_id'])){
    $del_id = (int)$_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM subcategories WHERE id=?");
    $stmt->execute([$del_id]);
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

// افزودن یا ویرایش زیرمجموعه
if($_SERVER['REQUEST_METHOD']=='POST'){
    $name = $_POST['name'];
    $category_id = $_POST['category_id'];

    if(isset($_POST['add_subcategory'])){
        $stmt = $conn->prepare("INSERT INTO subcategories (name, category_id) VALUES (?, ?)");
        $stmt->execute([$name, $category_id]);
    }

    if(isset($_POST['edit_subcategory'])){
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("UPDATE subcategories SET name=?, category_id=? WHERE id=?");
        $stmt->execute([$name, $category_id, $id]);
    }

    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

// گرفتن دسته‌بندی‌ها و زیرمجموعه‌ها
$categories = $conn->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);
$subcategories = $conn->query("SELECT s.*, c.name as category_name FROM subcategories s LEFT JOIN categories c ON s.category_id=c.id ORDER BY s.id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fa">
<head>
<meta charset="UTF-8">
<title>مدیریت زیرمجموعه‌ها</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
<style>
body { background:#f4f6f9; margin:0; font-family: Tahoma, sans-serif; }
.sidebar { width: 240px; height: 100vh; background: #fff; color:#000; padding:20px; position: fixed; top:0; right:0; overflow-y:auto; border-left:2px solid #ff6700; }
.sidebar h4 { margin-bottom:20px; color:#ff6700; }
.sidebar a { display:block; padding:10px; margin-bottom:5px; color:#000; text-decoration:none; border-radius:5px; }
.sidebar a:hover { background:#ff6700; color:#fff; transition:0.3s; }
.submenu { display:none; margin-right:15px; font-size:14px; margin-bottom:10px; }
.submenu.show { display:block; }
.menu-item { cursor:pointer; padding:10px; border-radius:5px; margin-bottom:5px; transition:0.3s; }
.menu-item:hover { color:#ff7f00; background: rgba(255,127,0,0.05); }
.toggle-icon { float:left; transition:0.3s; }
.rotate { transform: rotate(90deg); }
.content { margin-right:260px; padding:20px; }
table { width:100%; border-collapse: collapse; margin-top:20px; background:#fff; color:#000; border-radius:8px; overflow:hidden; }
th, td { padding:10px; text-align:right; border-bottom:1px solid #ddd; }
th { background:#0f1423; color:#00f0ff; }
tr:hover { background:#ff6700; color:#fff; transition:0.3s; }
button { cursor:pointer; border:none; border-radius:6px; padding:6px 12px; margin:2px; }
.btn-add { background:#00f0ff; color:#fff; }
.btn-add:hover { background:#ff6700; }
#subcategoryModal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; }
.modal-content { background:#fff; width:400px; margin:100px auto; padding:20px; border-radius:10px; position:relative; }
.modal-content h3 { color:#ff6700; margin-bottom:15px; }
.modal-content label { display:block; margin-top:10px; margin-bottom:5px; color:#000; }
.modal-content input, .modal-content select { width:100%; padding:8px; margin-bottom:10px; border-radius:5px; border:1px solid #ccc; }
.modal-content button { margin-top:5px; }
.close-modal { position:absolute; top:10px; right:15px; cursor:pointer; font-size:20px; color:#000; }
</style>
</head>
<body>

<!-- سایدبار کامل -->
<div class="sidebar">
<h4>پنل مدیریت</h4>

<a href="../admin-panel.php?page=dashboard">📊 داشبورد</a>

<div class="menu-item">📦 محصولات <span class="toggle-icon">▶</span></div>
<div class="submenu">
<a href="../products/index.php">لیست محصولات</a>
<a href="../products/add.php">افزودن محصول</a>
<a href="../products/brands.php">مدیریت برندها</a>
<a href="../products/categories.php">مدیریت دسته‌بندی</a>
<a href="../products/subcategories.php">مدیریت زیرمجموعه</a>
</div>

<a href="../offers.php">🔥 پیشنهادات</a>
<a href="../orders.php">🛒 سفارشات</a>

<div class="menu-item">👥 کاربران <span class="toggle-icon">▶</span></div>
<div class="submenu">
<a href="../customers.php">مشتریان</a>
<a href="../employees.php">کارمندان</a>
</div>

<div class="menu-item">⚙️ تنظیمات <span class="toggle-icon">▶</span></div>
<div class="submenu">
<a href="../settings/header.php">هدر</a>
<a href="../settings/homepage.php">صفحه اصلی</a>
<a href="../settings/footer.php">فوتر</a>
<a href="../settings/logo.php">لوگو</a>
</div>

<a href="../logout.php" class="text-danger">🚪 خروج</a>
</div>

<div class="content">
<h2>📂 زیرمجموعه‌ها</h2>
<button class="btn-add" onclick="openModal()">➕ افزودن زیرمجموعه</button>

<table>
<tr>
<th>آیدی</th>
<th>نام زیرمجموعه</th>
<th>دسته‌بندی</th>
<th>عملیات</th>
</tr>
<?php foreach($subcategories as $s): ?>
<tr>
<td><?= $s['id'] ?></td>
<td><?= htmlspecialchars($s['name']) ?></td>
<td><?= htmlspecialchars($s['category_name']) ?></td>
<td>
<button class="btn-add" onclick="openEditModal(<?= $s['id'] ?>, '<?= htmlspecialchars(addslashes($s['name'])) ?>', <?= $s['category_id'] ?>)">ویرایش</button>
<button style="background:#ff6700; color:#fff;" onclick="if(confirm('آیا مطمئن هستید؟')){ window.location='subcategories.php?delete_id=<?= $s['id'] ?>'; }">حذف</button>
</td>
</tr>
<?php endforeach; ?>
</table>

<!-- مودال افزودن/ویرایش -->
<div id="subcategoryModal">
<div class="modal-content">
<span class="close-modal" onclick="closeModal()">&times;</span>
<h3 id="modalTitle">افزودن زیرمجموعه</h3>
<form method="post">
<input type="hidden" name="id" id="subcatId">
<label>نام زیرمجموعه</label>
<input type="text" name="name" id="subcatName" required>
<label>انتخاب دسته‌بندی</label>
<select name="category_id" id="subcatCategory" required>
<?php foreach($categories as $c): ?>
<option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
<?php endforeach; ?>
</select>
<button type="submit" name="add_subcategory" id="submitBtn" class="btn-add">ذخیره</button>
<button type="button" onclick="closeModal()" style="background:#ff6700; color:#fff; padding:6px 12px; border:none; border-radius:6px;">انصراف</button>
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
    document.getElementById('modalTitle').innerText = 'افزودن زیرمجموعه';
    document.getElementById('submitBtn').name = 'add_subcategory';
    document.getElementById('subcatId').value = '';
    document.getElementById('subcatName').value = '';
    document.getElementById('subcatCategory').selectedIndex = 0;
    document.getElementById('subcategoryModal').style.display = 'block';
}

function openEditModal(id, name, category_id){
    document.getElementById('modalTitle').innerText = 'ویرایش زیرمجموعه';
    document.getElementById('submitBtn').name = 'edit_subcategory';
    document.getElementById('subcatId').value = id;
    document.getElementById('subcatName').value = name;
    document.getElementById('subcatCategory').value = category_id;
    document.getElementById('subcategoryModal').style.display = 'block';
}

function closeModal(){
    document.getElementById('subcategoryModal').style.display = 'none';
}
</script>

</body>
</html>
