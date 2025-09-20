<?php
session_start();
include "../../includes/db_connect.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../admin-login.php");
    exit;
}

// حذف برند
if(isset($_GET['delete_id'])){
    $del_id = (int)$_GET['delete_id'];
    $logo = $conn->query("SELECT logo FROM brands WHERE id=$del_id")->fetchColumn();
    if($logo && file_exists("../../".$logo)){
        unlink("../../".$logo);
    }
    $stmt = $conn->prepare("DELETE FROM brands WHERE id=?");
    $stmt->execute([$del_id]);
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

// افزودن / ویرایش برند
if(isset($_POST['add_brand']) || isset($_POST['edit_brand'])){
    $name = $_POST['name'];
    $meta_title = $_POST['meta_title'];
    $description = $_POST['description'];

    $logo = '';
    if(isset($_FILES['logo']) && $_FILES['logo']['name'] != ''){
        $logo = 'uploads/' . time() . '_' . $_FILES['logo']['name'];
        move_uploaded_file($_FILES['logo']['tmp_name'], "../../" . $logo);
    }

    if(isset($_POST['add_brand'])){
        $stmt = $conn->prepare("INSERT INTO brands (name, logo, meta_title, description) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $logo, $meta_title, $description]);
    }

    if(isset($_POST['edit_brand'])){
        $id = (int)$_POST['id'];
        if($logo){
            $old_logo = $conn->query("SELECT logo FROM brands WHERE id=$id")->fetchColumn();
            if($old_logo && file_exists("../../".$old_logo)){
                unlink("../../".$old_logo);
            }
            $stmt = $conn->prepare("UPDATE brands SET name=?, logo=?, meta_title=?, description=? WHERE id=?");
            $stmt->execute([$name, $logo, $meta_title, $description, $id]);
        } else {
            $stmt = $conn->prepare("UPDATE brands SET name=?, meta_title=?, description=? WHERE id=?");
            $stmt->execute([$name, $meta_title, $description, $id]);
        }
    }

    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

// گرفتن لیست برندها
$brands = $conn->query("SELECT * FROM brands ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fa">
<head>
<meta charset="UTF-8">
<title>مدیریت برندها</title>
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
#brandModal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; }
.modal-content { background:#fff; width:400px; margin:100px auto; padding:20px; border-radius:10px; position:relative; }
.modal-content h3 { color:#ff6700; margin-bottom:15px; }
.modal-content label { display:block; margin-top:10px; margin-bottom:5px; color:#000; }
.modal-content input, .modal-content textarea { width:100%; padding:8px; margin-bottom:10px; border-radius:5px; border:1px solid #ccc; }
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
<h2>📦 برندها</h2>
<button class="btn-add" onclick="openModal()">➕ افزودن برند جدید</button>

<table>
<tr>
<th>#</th>
<th>نام</th>
<th>لوگو</th>
<th>گزینه‌ها</th>
</tr>
<?php foreach($brands as $i => $b): ?>
<tr>
<td><?= $i+1 ?></td>
<td><?= htmlspecialchars($b['name']) ?></td>
<td><?php if($b['logo']): ?><img src="../../<?= $b['logo'] ?>" width="120" height="80"><?php endif; ?></td>
<td>
<button class="btn-add" onclick="openEditModal(<?= $b['id'] ?>, '<?= htmlspecialchars(addslashes($b['name'])) ?>', '<?= htmlspecialchars(addslashes($b['meta_title'])) ?>', '<?= htmlspecialchars(addslashes($b['description'])) ?>')">ویرایش</button>
<button style="background:#ff6700; color:#fff;" onclick="if(confirm('آیا مطمئن هستید؟')){ window.location='?delete_id=<?= $b['id'] ?>'; }">حذف</button>
</td>
</tr>
<?php endforeach; ?>
</table>

<!-- مودال افزودن/ویرایش برند -->
<div id="brandModal">
<div class="modal-content">
<span class="close-modal" onclick="closeModal()">&times;</span>
<h3 id="modalTitle">افزودن برند جدید</h3>
<form method="post" enctype="multipart/form-data">
<input type="hidden" name="id" id="brandId">
<label>نام برند</label>
<input type="text" name="name" id="brandName" required>
<label>لوگو (120x80)</label>
<input type="file" name="logo">
<label>متای عنوان</label>
<input type="text" name="meta_title" id="brandMeta">
<label>توضیحات</label>
<textarea name="description" rows="4" id="brandDesc"></textarea>
<button type="submit" name="add_brand" id="submitBtn" class="btn-add">ذخیره</button>
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
    document.getElementById('modalTitle').innerText = 'افزودن برند جدید';
    document.getElementById('submitBtn').name = 'add_brand';
    document.getElementById('brandId').value = '';
    document.getElementById('brandName').value = '';
    document.getElementById('brandMeta').value = '';
    document.getElementById('brandDesc').value = '';
    document.getElementById('brandModal').style.display = 'block';
}

function openEditModal(id, name, meta, desc){
    document.getElementById('modalTitle').innerText = 'ویرایش برند';
    document.getElementById('submitBtn').name = 'edit_brand';
    document.getElementById('brandId').value = id;
    document.getElementById('brandName').value = name;
    document.getElementById('brandMeta').value = meta;
    document.getElementById('brandDesc').value = desc;
    document.getElementById('brandModal').style.display = 'block';
}

function closeModal(){
    document.getElementById('brandModal').style.display = 'none';
}
</script>
</body>
</html>
