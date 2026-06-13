<?php
require_once __DIR__ . '/../bootstrap.php';
require_admin();
$pdo = admin_db();
$adminTitle = 'Sub Categories';
$categories = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id = (int) ($_POST['id'] ?? 0);
    $image = upload_admin_image('image', 'subcategories') ?? (string) ($_POST['current_image'] ?? '');
    $data = [':category_id' => (int) $_POST['category_id'], ':name' => trim($_POST['name']), ':slug' => trim($_POST['slug']), ':image_url' => $image, ':status' => (int) ($_POST['status'] ?? 0)];
    if ($id > 0) {
        $data[':id'] = $id;
        $pdo->prepare('UPDATE sub_categories SET category_id=:category_id,name=:name,slug=:slug,image_url=:image_url,status=:status WHERE id=:id')->execute($data);
        log_activity('updated', 'sub_category', $id);
    } else {
        $pdo->prepare('INSERT INTO sub_categories (category_id,name,slug,image_url,status) VALUES (:category_id,:name,:slug,:image_url,:status)')->execute($data);
        log_activity('created', 'sub_category', (int) $pdo->lastInsertId());
    }
    redirect_with(admin_url('subcategories/'), 'Sub category saved.');
}
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $pdo->prepare('DELETE FROM sub_categories WHERE id=:id')->execute([':id' => $id]);
    redirect_with(admin_url('subcategories/'), 'Sub category deleted.');
}
$edit = null;
if (isset($_GET['edit'])) {
    $s = $pdo->prepare('SELECT * FROM sub_categories WHERE id=:id');
    $s->execute([':id' => (int) $_GET['edit']]);
    $edit = $s->fetch();
}
$rows = $pdo->query('SELECT sc.*, c.name AS category_name FROM sub_categories sc JOIN categories c ON c.id=sc.category_id ORDER BY sc.id DESC')->fetchAll();
require __DIR__ . '/../header.php';
?>
<section class="panel"><div class="panel-head"><h2><?= $edit ? 'Edit' : 'Add'; ?> Sub Category</h2></div>
<form class="admin-form" method="post" enctype="multipart/form-data">
<input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>"><input type="hidden" name="id" value="<?= e((string)($edit['id'] ?? '')); ?>"><input type="hidden" name="current_image" value="<?= e((string)($edit['image_url'] ?? '')); ?>">
<label>Parent Category<select name="category_id" required><?php foreach ($categories as $cat): ?><option value="<?= (int)$cat['id']; ?>" <?= (int)($edit['category_id'] ?? 0)===(int)$cat['id']?'selected':''; ?>><?= e($cat['name']); ?></option><?php endforeach; ?></select></label>
<label>Name<input name="name" value="<?= e((string)($edit['name'] ?? '')); ?>" required></label>
<label>Slug<input name="slug" value="<?= e((string)($edit['slug'] ?? '')); ?>" required></label>
<label>Status<select name="status"><option value="1" <?= (int)($edit['status'] ?? 1)===1?'selected':''; ?>>Active</option><option value="0" <?= (int)($edit['status'] ?? 1)===0?'selected':''; ?>>Inactive</option></select></label>
<label>Image<input type="file" name="image" accept="image/*"></label><button class="admin-btn primary">Save</button></form></section>
<section class="panel"><div class="panel-head"><h2>Sub Category List</h2></div><div class="table-wrap"><table><thead><tr><th>Image</th><th>Name</th><th>Parent</th><th>Status</th><th>Actions</th></tr></thead><tbody><?php foreach ($rows as $row): ?><tr><td><?php if ($row['image_url']): ?><img class="thumb" src="<?= e(public_image($row['image_url'])); ?>" alt=""><?php endif; ?></td><td><?= e($row['name']); ?><small><?= e($row['slug']); ?></small></td><td><?= e($row['category_name']); ?></td><td><span class="pill"><?= $row['status']?'Active':'Inactive'; ?></span></td><td><a href="?edit=<?= (int)$row['id']; ?>">Edit</a> <a class="danger-link" href="?delete=<?= (int)$row['id']; ?>" data-confirm>Delete</a></td></tr><?php endforeach; ?></tbody></table></div></section>
<?php require __DIR__ . '/../footer.php'; ?>
