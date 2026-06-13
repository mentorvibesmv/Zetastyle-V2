<?php
require_once __DIR__ . '/../bootstrap.php';
require_admin();
$pdo = admin_db();
$adminTitle = 'Products';
$categories = $pdo->query('SELECT id,name FROM categories WHERE status=1 ORDER BY name')->fetchAll();
$subcategories = $pdo->query('SELECT id,name,category_id FROM sub_categories WHERE status=1 ORDER BY name')->fetchAll();
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $pdo->prepare('DELETE FROM products WHERE id=:id')->execute([':id' => $id]);
    redirect_with(admin_url('products/'), 'Product deleted.');
}
if (isset($_GET['duplicate'])) {
    $id = (int) $_GET['duplicate'];
    $product = $pdo->prepare('SELECT * FROM products WHERE id=:id');
    $product->execute([':id' => $id]);
    $p = $product->fetch();
    if ($p) {
        $p['name'] .= ' Copy';
        $p['slug'] .= '-copy-' . time();
        $p['sku'] .= '-COPY';
        $stmt = $pdo->prepare('INSERT INTO products (category_id,sub_category_id,name,slug,sku,description,current_price,old_price,discount_badge,product_tag,image_url,available_sizes,available_colors,stock,is_trending,is_best_seller,is_new_arrival,is_featured,is_active) VALUES (:category_id,:sub_category_id,:name,:slug,:sku,:description,:current_price,:old_price,:discount_badge,:product_tag,:image_url,:available_sizes,:available_colors,:stock,:is_trending,:is_best_seller,:is_new_arrival,:is_featured,:is_active)');
        $stmt->execute([':category_id'=>$p['category_id'],':sub_category_id'=>$p['sub_category_id'],':name'=>$p['name'],':slug'=>$p['slug'],':sku'=>$p['sku'],':description'=>$p['description'],':current_price'=>$p['current_price'],':old_price'=>$p['old_price'],':discount_badge'=>$p['discount_badge'],':product_tag'=>$p['product_tag'],':image_url'=>$p['image_url'],':available_sizes'=>$p['available_sizes'],':available_colors'=>$p['available_colors'],':stock'=>$p['stock'],':is_trending'=>$p['is_trending'],':is_best_seller'=>$p['is_best_seller'],':is_new_arrival'=>$p['is_new_arrival'],':is_featured'=>$p['is_featured'],':is_active'=>$p['is_active']]);
    }
    redirect_with(admin_url('products/'), 'Product duplicated.');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id = (int) ($_POST['id'] ?? 0);
    $mainImage = upload_admin_image('image', 'products') ?? (string) ($_POST['current_image'] ?? '');
    $tag = !empty($_POST['is_trending']) ? 'trending' : (!empty($_POST['is_best_seller']) ? 'best' : 'new');
    $data = [':category_id'=>(int)$_POST['category_id'],':sub_category_id'=>(int)($_POST['sub_category_id'] ?: 0) ?: null,':name'=>trim($_POST['name']),':slug'=>trim($_POST['slug']),':sku'=>trim($_POST['sku']),':description'=>trim($_POST['description']),':current_price'=>(float)($_POST['offer_price'] ?: $_POST['price']),':old_price'=>(float)$_POST['price'],':discount_badge'=>trim($_POST['discount_badge'] ?: 'NEW'),':product_tag'=>$tag,':image_url'=>$mainImage,':available_sizes'=>trim($_POST['available_sizes']),':available_colors'=>trim($_POST['available_colors']),':stock'=>(int)$_POST['stock'],':is_trending'=>isset($_POST['is_trending'])?1:0,':is_best_seller'=>isset($_POST['is_best_seller'])?1:0,':is_new_arrival'=>isset($_POST['is_new_arrival'])?1:0,':is_featured'=>isset($_POST['is_featured'])?1:0,':is_active'=>(int)($_POST['is_active'] ?? 0)];
    if ($id > 0) {
        $data[':id'] = $id;
        $pdo->prepare('UPDATE products SET category_id=:category_id,sub_category_id=:sub_category_id,name=:name,slug=:slug,sku=:sku,description=:description,current_price=:current_price,old_price=:old_price,discount_badge=:discount_badge,product_tag=:product_tag,image_url=:image_url,available_sizes=:available_sizes,available_colors=:available_colors,stock=:stock,is_trending=:is_trending,is_best_seller=:is_best_seller,is_new_arrival=:is_new_arrival,is_featured=:is_featured,is_active=:is_active WHERE id=:id')->execute($data);
        $productId = $id;
    } else {
        $pdo->prepare('INSERT INTO products (category_id,sub_category_id,name,slug,sku,description,current_price,old_price,discount_badge,product_tag,image_url,available_sizes,available_colors,stock,is_trending,is_best_seller,is_new_arrival,is_featured,is_active) VALUES (:category_id,:sub_category_id,:name,:slug,:sku,:description,:current_price,:old_price,:discount_badge,:product_tag,:image_url,:available_sizes,:available_colors,:stock,:is_trending,:is_best_seller,:is_new_arrival,:is_featured,:is_active)')->execute($data);
        $productId = (int) $pdo->lastInsertId();
    }
    foreach (upload_admin_images('gallery', 'products') as $path) {
        $pdo->prepare('INSERT INTO product_images (product_id,image_url,sort_order) VALUES (:product_id,:image_url,0)')->execute([':product_id'=>$productId,':image_url'=>$path]);
    }
    redirect_with(admin_url('products/'), 'Product saved.');
}
$edit = null;
if (isset($_GET['edit'])) { $s=$pdo->prepare('SELECT * FROM products WHERE id=:id'); $s->execute([':id'=>(int)$_GET['edit']]); $edit=$s->fetch(); }
$search = trim((string)($_GET['search'] ?? ''));
$catFilter = (int)($_GET['category_id'] ?? 0);
$where = 'WHERE 1=1';
$params = [];
if ($search !== '') { $where .= ' AND (p.name LIKE :search OR p.sku LIKE :search)'; $params[':search']="%{$search}%"; }
if ($catFilter > 0) { $where .= ' AND p.category_id=:cat'; $params[':cat']=$catFilter; }
$stmt=$pdo->prepare("SELECT p.*,c.name AS category_name FROM products p JOIN categories c ON c.id=p.category_id {$where} ORDER BY p.id DESC LIMIT 30");
$stmt->execute($params);
$rows=$stmt->fetchAll();
require __DIR__ . '/../header.php';
?>
<section class="panel"><div class="panel-head"><h2><?= $edit?'Edit':'Add'; ?> Product</h2></div>
<form class="admin-form product-form" method="post" enctype="multipart/form-data">
<input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>"><input type="hidden" name="id" value="<?= e((string)($edit['id']??'')); ?>"><input type="hidden" name="current_image" value="<?= e((string)($edit['image_url']??'')); ?>">
<label>Product Name<input name="name" value="<?= e((string)($edit['name']??'')); ?>" required></label><label>SKU<input name="sku" value="<?= e((string)($edit['sku']??'')); ?>" required></label><label>Slug<input name="slug" value="<?= e((string)($edit['slug']??'')); ?>" required></label>
<label>Category<select name="category_id" required><?php foreach($categories as $cat): ?><option value="<?= (int)$cat['id']; ?>" <?= (int)($edit['category_id']??0)===(int)$cat['id']?'selected':''; ?>><?= e($cat['name']); ?></option><?php endforeach; ?></select></label>
<label>Sub Category<select name="sub_category_id"><option value="">None</option><?php foreach($subcategories as $sub): ?><option value="<?= (int)$sub['id']; ?>" <?= (int)($edit['sub_category_id']??0)===(int)$sub['id']?'selected':''; ?>><?= e($sub['name']); ?></option><?php endforeach; ?></select></label>
<label>Price<input type="number" step="0.01" name="price" value="<?= e((string)($edit['old_price']??'')); ?>" required></label><label>Offer Price<input type="number" step="0.01" name="offer_price" value="<?= e((string)($edit['current_price']??'')); ?>"></label><label>Discount Badge<input name="discount_badge" value="<?= e((string)($edit['discount_badge']??'NEW')); ?>"></label><label>Stock<input type="number" name="stock" value="<?= e((string)($edit['stock']??0)); ?>"></label>
<label>Available Sizes<input name="available_sizes" value="<?= e((string)($edit['available_sizes']??'S,M,L,XL')); ?>"></label><label>Available Colors<input name="available_colors" value="<?= e((string)($edit['available_colors']??'Black,White,Cream')); ?>"></label>
<label class="wide">Description<textarea name="description" rows="4"><?= e((string)($edit['description']??'')); ?></textarea></label>
<label>Main Image<input type="file" name="image" accept="image/*"></label><label>Gallery Images<input type="file" name="gallery[]" accept="image/*" multiple data-preview-input></label>
<div class="checks wide"><label><input type="checkbox" name="is_trending" <?= !empty($edit['is_trending'])?'checked':''; ?>> Trending</label><label><input type="checkbox" name="is_best_seller" <?= !empty($edit['is_best_seller'])?'checked':''; ?>> Best Seller</label><label><input type="checkbox" name="is_new_arrival" <?= !empty($edit['is_new_arrival'])?'checked':''; ?>> New Arrival</label><label><input type="checkbox" name="is_featured" <?= !empty($edit['is_featured'])?'checked':''; ?>> Featured</label><label><input type="checkbox" name="is_active" value="1" <?= (int)($edit['is_active']??1)===1?'checked':''; ?>> Active</label></div>
<div class="upload-preview wide" data-upload-preview></div><button class="admin-btn primary">Save Product</button></form></section>
<section class="panel"><div class="panel-head"><h2>Products</h2><form class="search-form"><input name="search" value="<?= e($search); ?>" placeholder="Search products"><select name="category_id"><option value="0">All Categories</option><?php foreach($categories as $cat): ?><option value="<?= (int)$cat['id']; ?>" <?= $catFilter===(int)$cat['id']?'selected':''; ?>><?= e($cat['name']); ?></option><?php endforeach; ?></select><button class="admin-btn">Filter</button></form></div><div class="table-wrap"><table><thead><tr><th>Image</th><th>Product</th><th>Category</th><th>Price</th><th>Stock</th><th>Actions</th></tr></thead><tbody><?php foreach($rows as $row): ?><tr><td><?php if($row['image_url']): ?><img class="thumb" src="<?= e(public_image($row['image_url'])); ?>" alt=""><?php endif; ?></td><td><?= e($row['name']); ?><small><?= e($row['sku']); ?></small></td><td><?= e($row['category_name']); ?></td><td><?= money((float)$row['current_price']); ?></td><td><?= (int)$row['stock']; ?></td><td><a href="?edit=<?= (int)$row['id']; ?>">Edit</a> <a href="?duplicate=<?= (int)$row['id']; ?>">Duplicate</a> <a class="danger-link" href="?delete=<?= (int)$row['id']; ?>" data-confirm>Delete</a> <a target="_blank" href="../../product.php?slug=<?= e($row['slug']); ?>">Preview</a></td></tr><?php endforeach; ?></tbody></table></div></section>
<?php require __DIR__ . '/../footer.php'; ?>
