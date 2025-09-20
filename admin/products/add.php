<?php
session_start();
include "../../includes/db_connect.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../admin-login.php");
    exit;
}

// گرفتن داده‌ها برای select
$categories = $conn->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);
$subcategories = $conn->query("SELECT * FROM subcategories")->fetchAll(PDO::FETCH_ASSOC);
$subsubcategories = $conn->query("SELECT * FROM sub_subcategories")->fetchAll(PDO::FETCH_ASSOC);
$brands = $conn->query("SELECT * FROM brands")->fetchAll(PDO::FETCH_ASSOC);
$features = $conn->query("SELECT * FROM features")->fetchAll(PDO::FETCH_ASSOC);

// ثبت محصول
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $category_id = $_POST['category_id'];
    $subcategory_id = $_POST['subcategory_id'];
    $subsub_id = $_POST['subsub_id'];
    $brand_id = $_POST['brand_id'];
    $unit = $_POST['unit'];
    $tags = $_POST['tags'];
    $price = $_POST['price'];
    $purchase_price = $_POST['purchase_price'];
    $tax = $_POST['tax'];
    $discount = $_POST['discount'];
    $stock = $_POST['stock'];
    $description = $_POST['description'];
    $shipping_cost = $_POST['shipping_cost'] ?? 0;
    $seo_title = $_POST['seo_title'];
    $seo_description = $_POST['seo_description'];
    $slug = $_POST['slug'];

    // بارگذاری تصویر شاخص
    $thumbnail = '';
    if(isset($_FILES['thumbnail']) && $_FILES['thumbnail']['name'] != '') {
        $thumbnail = 'uploads/' . time() . '_' . $_FILES['thumbnail']['name'];
        move_uploaded_file($_FILES['thumbnail']['tmp_name'], "../../" . $thumbnail);
    }

    // بارگذاری گالری
    $gallery_paths = [];
    if(isset($_FILES['gallery'])){
        foreach($_FILES['gallery']['tmp_name'] as $key => $tmp_name){
            $gallery_path = 'uploads/' . time() . '_' . $_FILES['gallery']['name'][$key];
            move_uploaded_file($tmp_name, "../../" . $gallery_path);
            $gallery_paths[] = $gallery_path;
        }
    }
    $gallery = implode(',', $gallery_paths);

    // کاتالوگ و ویدئو
    $catalog = '';
    if(isset($_FILES['catalog']) && $_FILES['catalog']['name'] != ''){
        $catalog = 'uploads/' . time() . '_' . $_FILES['catalog']['name'];
        move_uploaded_file($_FILES['catalog']['tmp_name'], "../../" . $catalog);
    }

    $video = '';
    if(isset($_FILES['video']) && $_FILES['video']['name'] != ''){
        $video = 'uploads/' . time() . '_' . $_FILES['video']['name'];
        move_uploaded_file($_FILES['video']['tmp_name'], "../../" . $video);
    }

    // ذخیره محصول
    $stmt = $conn->prepare("INSERT INTO products 
        (name, category_id, subcategory_id, subsub_id, brand_id, unit, tags, thumbnail, gallery, catalog, video, price, purchase_price, tax, discount, stock, description, shipping_cost, seo_title, seo_description, slug) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $name, $category_id, $subcategory_id, $subsub_id, $brand_id, $unit, $tags, $thumbnail, $gallery, $catalog, $video, 
        $price, $purchase_price, $tax, $discount, $stock, $description, $shipping_cost, $seo_title, $seo_description, $slug
    ]);

    $product_id = $conn->lastInsertId();

    // ذخیره ویژگی‌ها و قیمت‌ها
    $features_ids = $_POST['features_ids'] ?? [];
    $features_prices = $_POST['features_prices'] ?? [];
    foreach($features_ids as $index => $f_id){
        $f_price = $features_prices[$index] ?? 0;
        $stmt = $conn->prepare("INSERT INTO product_features (product_id, feature_id, price) VALUES (?, ?, ?)");
        $stmt->execute([$product_id, $f_id, $f_price]);
    }

    echo "<p style='color:#00f0ff;'>محصول با موفقیت اضافه شد!</p>";
}
?>

<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <title>افزودن محصول - پنل مدیریت</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- TinyMCE سایت صلی -->
    <script src="https://cdn.tiny.cloud/1/9two8jj4vfvguc6tf1xuumb9tzo1i8khetd6cdc24p8pdiem/tinymce/8/tinymce.min.js" referrerpolicy="origin" crossorigin="anonymous"></script>

    <style>
        body { background:#f4f6f9; font-family: 'Segoe UI', Tahoma, sans-serif; margin:0; }

        /* سایدبار */
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
        .sidebar h4 { font-weight: bold; margin-bottom: 1rem; color: #333; }
        .sidebar a { color: #000; display: block; padding: 10px 15px; text-decoration: none; border-radius: 5px; margin-bottom: 5px; font-size: 14px; transition: 0.3s; }
        .sidebar a:hover { color: #ff7f00; text-shadow: 0 0 5px #ff7f00, 0 0 12px #ff7f00; background: rgba(255,127,0,0.05); }
        .submenu { display: none; margin-right: 15px; font-size: 13px; }
        .submenu a { padding-right: 25px; }
        .sidebar .menu-item { cursor: pointer; padding: 10px 15px; border-radius: 5px; margin-bottom: 5px; transition: 0.3s; }
        .sidebar .menu-item:hover { color: #ff7f00; text-shadow: 0 0 5px #ff7f00, 0 0 12px #ff7f00; background: rgba(255,127,0,0.05); }
        .submenu.show { display: block; }
        .toggle-icon { float: left; transition: transform 0.3s; }
        .rotate { transform: rotate(90deg); }

        /* محتوای اصلی */
        .content { margin-right: 260px; padding: 20px; }

        /* فرم افزودن محصول */
        form { background:#fff; padding:20px; border-radius:12px; max-width:900px; color:#000; }
        form label { display:block; margin-top:10px; margin-bottom:5px; color:#333; }
        form input, form select, form textarea { width:100%; padding:8px; border-radius:6px; border:1px solid #ccc; margin-bottom:10px; }
        form button { background:#00f0ff; color:#000; padding:10px 20px; border:none; border-radius:6px; cursor:pointer; transition:0.3s; }
        form button:hover { background:#ff6700; color:#fff; }
        .feature-row { display:flex; gap:10px; margin-bottom:10px; }
        .feature-row select, .feature-row input { flex:1; }
        .feature-row button { background:#ff6700; color:#fff; border:none; border-radius:5px; cursor:pointer; }
        h2 { margin-bottom:20px; }
        h3 { color:#ff6700; margin-top:20px; }
    </style>
</head>
<body>
    <!-- سایدبار -->
    <div class="sidebar">
        <h4>پنل مدیریت</h4>

        <a href="../admin-panel.php?page=dashboard">📊 داشبورد</a>

        <div class="menu-item">📦 محصولات <span class="toggle-icon">▶</span></div>
        <div class="submenu show">
            <a href="index.php">لیست محصولات</a>
            <a href="add.php">افزودن محصول</a>
            <a href="brands.php">مدیریت برندها</a>
            <a href="categories.php">مدیریت دسته‌بندی</a>
            <a href="subcategories.php">مدیریت زیرمجموعه</a>
            <a href="features.php">ویژگی‌ها</a>
            <a href="reviews.php">نظرات محصول</a>
        </div>

        <a href="../offers.php">🔥 پیشنهادات شگفت‌انگیز</a>
        <a href="../orders.php">🛒 سفارشات</a>

        <div class="menu-item">📈 گزارشات <span class="toggle-icon">▶</span></div>
        <div class="submenu">
            <a href="../reports/sales.php">گزارش فروش</a>
            <a href="../reports/stock.php">گزارش انبار</a>
            <a href="../reports/customers.php">گزارش مشتریان</a>
            <a href="../reports/wishlist.php">گزارش علاقه‌مندی‌ها</a>
        </div>

        <a href="../customers.php">👥 مشتریان</a>

        <div class="menu-item">⚙️ تنظیمات <span class="toggle-icon">▶</span></div>
        <div class="submenu">
            <a href="../settings/header.php">تنظیمات هدر</a>
            <a href="../settings/homepage.php">صفحه اصلی</a>
            <a href="../settings/pages.php">صفحات جانبی</a>
            <a href="../settings/sms.php">پنل پیامکی</a>
            <a href="../settings/footer.php">فوتر</a>
            <a href="../settings/slider.php">اسلایدر</a>
            <a href="../settings/checkout.php">صفحه پرداخت</a>
            <a href="../settings/payment.php">درگاه پرداخت</a>
            <a href="../settings/logo.php">لوگو</a>
        </div>

        <div class="menu-item">🎫 تیکت‌ها <span class="toggle-icon">▶</span></div>
        <div class="submenu">
            <a href="../tickets/departments.php">دپارتمان‌ها</a>
            <a href="../tickets/received.php">دریافتی‌ها</a>
            <a href="../tickets/sent.php">ارسالی‌ها</a>
        </div>

        <a href="../employees.php">👨‍💼 کارمندان</a>
        <a href="../coupons.php">🏷️ کوپن تخفیف</a>

        <a href="../logout.php" class="text-danger">🚪 خروج</a>
    </div>

    <!-- محتوای اصلی -->
    <div class="content">
        <h2>➕ افزودن محصول جدید</h2>
        <form method="post" enctype="multipart/form-data">
            <label>نام محصول</label>
            <input type="text" name="name" required>

            <label>دسته‌بندی</label>
            <select name="category_id" required>
                <?php foreach($categories as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                <?php endforeach; ?>
            </select>

            <label>زیرمجموعه دسته‌بندی</label>
            <select name="subcategory_id">
                <?php foreach($subcategories as $s): ?>
                    <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                <?php endforeach; ?>
            </select>

            <label>زیرمجموعه زیرمجموعه</label>
            <select name="subsub_id">
                <?php foreach($subsubcategories as $ss): ?>
                    <option value="<?= $ss['id'] ?>"><?= htmlspecialchars($ss['name']) ?></option>
                <?php endforeach; ?>
            </select>

            <label>برند</label>
            <select name="brand_id">
                <?php foreach($brands as $b): ?>
                    <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
                <?php endforeach; ?>
            </select>

            <label>واحد محصول</label>
            <input type="text" name="unit">

            <label>تگ‌ها</label>
            <input type="text" name="tags" placeholder="مثلاً: دیجیتال, گوشی">

            <label>تصویر شاخص</label>
            <input type="file" name="thumbnail">

            <label>گالری محصولات</label>
            <input type="file" name="gallery[]" multiple>

            <label>کاتالوگ (اختیاری)</label>
            <input type="file" name="catalog">

            <label>ویدئو محصول (اختیاری)</label>
            <input type="file" name="video">

            <label>ویژگی‌ها و قیمت‌ها</label>
            <div id="feature-container">
                <div class="feature-row">
                    <select name="features_ids[]">
                        <?php foreach($features as $f): ?>
                            <option value="<?= $f['id'] ?>"><?= htmlspecialchars($f['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="number" name="features_prices[]" placeholder="قیمت این ویژگی">
                    <button type="button" onclick="removeRow(this)">❌</button>
                </div>
            </div>
            <button type="button" onclick="addRow()">➕ افزودن ویژگی دیگر</button>

            <label>قیمت واحد</label>
            <input type="number" name="price" required>

            <label>قیمت خرید</label>
            <input type="number" name="purchase_price">

            <label>مالیات</label>
            <input type="number" name="tax">

            <label>تخفیف</label>
            <input type="number" name="discount">

            <label>تعداد موجودی انبار</label>
            <input type="number" name="stock" required>

            <label>توضیحات محصول</label>
            <textarea name="description" rows="10"></textarea>

            <label>هزینه ارسال (اختیاری، اگر 0 رایگان می‌شود)</label>
            <input type="number" name="shipping_cost">

            <h3>SEO (برای گوگل)</h3>
            <label>عنوان SEO</label>
            <input type="text" name="seo_title">
            <label>توضیحات SEO</label>
            <textarea name="seo_description" rows="3"></textarea>
            <label>Slug (آدرس URL محصول)</label>
            <input type="text" name="slug">

            <button type="submit">افزودن محصول</button>
        </form>
    </div>

    <script>
        // سایدبار
        const menuItems = document.querySelectorAll('.menu-item');
        menuItems.forEach(item => {
            item.addEventListener('click', () => {
                const submenu = item.nextElementSibling;
                const icon = item.querySelector('.toggle-icon');
                submenu.classList.toggle('show');
                icon.classList.toggle('rotate');
            });
        });

        // ویژگی‌ها
        function addRow() {
            let container = document.getElementById('feature-container');
            let row = document.createElement('div');
            row.classList.add('feature-row');
            row.innerHTML = `
                <select name="features_ids[]">
                    <?php foreach($features as $f): ?>
                        <option value="<?= $f['id'] ?>"><?= htmlspecialchars($f['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="number" name="features_prices[]" placeholder="قیمت این ویژگی">
                <button type="button" onclick="removeRow(this)">❌</button>
            `;
            container.appendChild(row);
        }

        function removeRow(btn){
            btn.parentElement.remove();
        }

        // TinyMCE سایت صلی
        tinymce.init({
          selector: 'textarea[name="description"]',
          plugins: [
            'anchor', 'autolink', 'charmap', 'codesample', 'emoticons', 'link', 'lists', 'media', 'searchreplace', 'table', 'visualblocks', 'wordcount',
            'checklist', 'mediaembed', 'casechange', 'formatpainter', 'pageembed', 'a11ychecker', 'tinymcespellchecker', 'permanentpen', 'powerpaste', 'advtable',
            'advcode', 'advtemplate', 'ai', 'uploadcare', 'mentions', 'tinycomments', 'tableofcontents', 'footnotes', 'mergetags', 'autocorrect', 'typography', 'inlinecss', 'markdown','importword', 'exportword', 'exportpdf'
          ],
          toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link media table mergetags | addcomment showcomments | spellcheckdialog a11ycheck typography uploadcare | align lineheight | checklist numlist bullist indent outdent | emoticons charmap | removeformat',
          tinycomments_mode: 'embedded',
          tinycomments_author: 'Admin',
          mergetags_list: [
            { value: 'First.Name', title: 'First Name' },
            { value: 'Email', title: 'Email' }
          ],
          ai_request: (request, respondWith) => respondWith.string(() => Promise.reject('See docs to implement AI Assistant')),
          uploadcare_public_key: '668f150a7421f77559b8',
        });
    </script>
</body>
</html>
