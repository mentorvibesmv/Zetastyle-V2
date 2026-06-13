<?php
require_once __DIR__ . '/../bootstrap.php';
require_admin();
$pdo = admin_db();
$adminTitle = 'Categories';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id = (int) ($_POST['id'] ?? 0);
    $image = upload_admin_image('image', 'categories') ?? (string) ($_POST['current_image'] ?? '');
    $data = [
        ':name' => trim((string) $_POST['name']),
        ':slug' => trim((string) $_POST['slug']),
        ':image_url' => $image,
        ':display_order' => (int) $_POST['display_order'],
        ':status' => (int) ($_POST['status'] ?? 0),
    ];
    if ($id > 0) {
        $data[':id'] = $id;
        $pdo->prepare('UPDATE categories SET name=:name, slug=:slug, image_url=:image_url, display_order=:display_order, status=:status WHERE id=:id')->execute($data);
        log_activity('updated', 'category', $id);
    } else {
        $pdo->prepare('INSERT INTO categories (name, slug, image_url, display_order, status) VALUES (:name, :slug, :image_url, :display_order, :status)')->execute($data);
        log_activity('created', 'category', (int) $pdo->lastInsertId());
    }
    redirect_with(admin_url('categories/'), 'Category saved.');
}

if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $pdo->prepare('DELETE FROM categories WHERE id=:id')->execute([':id' => $id]);
    log_activity('deleted', 'category', $id);
    redirect_with(admin_url('categories/'), 'Category deleted.');
}

$edit = null;
if (isset($_GET['edit'])) {
    $statement = $pdo->prepare('SELECT * FROM categories WHERE id=:id');
    $statement->execute([':id' => (int) $_GET['edit']]);
    $edit = $statement->fetch();
}
$search = trim((string) ($_GET['search'] ?? ''));
$page = max(1, (int) ($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;
$where = $search !== '' ? 'WHERE name LIKE :search OR slug LIKE :search' : '';
$count = $pdo->prepare("SELECT COUNT(*) FROM categories {$where}");
if ($search !== '') $count->bindValue(':search', "%{$search}%");
$count->execute();
$total = (int) $count->fetchColumn();
$statement = $pdo->prepare("SELECT * FROM categories {$where} ORDER BY display_order, id DESC LIMIT {$limit} OFFSET {$offset}");
if ($search !== '') $statement->bindValue(':search', "%{$search}%");
$statement->execute();
$rows = $statement->fetchAll();
require __DIR__ . '/../header.php';
?>
<section class="panel">
    <div class="panel-head"><h2><?= $edit ? 'Edit Category' : 'Add Category'; ?></h2></div>
    <form class="admin-form" method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">
        <input type="hidden" name="id" value="<?= e((string) ($edit['id'] ?? '')); ?>">
        <input type="hidden" name="current_image" value="<?= e((string) ($edit['image_url'] ?? '')); ?>">
        <label>Name<input name="name" value="<?= e((string) ($edit['name'] ?? '')); ?>" required></label>
        <label>Slug<input name="slug" value="<?= e((string) ($edit['slug'] ?? '')); ?>" required></label>
        <label>Display Order<input type="number" name="display_order" value="<?= e((string) ($edit['display_order'] ?? 0)); ?>"></label>
        <label>Status<select name="status"><option value="1" <?= (int)($edit['status'] ?? 1) === 1 ? 'selected' : ''; ?>>Active</option><option value="0" <?= (int)($edit['status'] ?? 1) === 0 ? 'selected' : ''; ?>>Inactive</option></select></label>
        <label>Category Image<input type="file" name="image" accept="image/*"></label>
        <button class="admin-btn primary">Save Category</button>
    </form>
</section>
<section class="panel">
    <div class="panel-head">
        <h2>Category List</h2>
        <form class="search-form"><input name="search" value="<?= e($search); ?>" placeholder="Search categories"><button class="admin-btn">Search</button></form>
    </div>
    <div class="table-wrap"><table><thead><tr><th>Image</th><th>Name</th><th>Order</th><th>Status</th><th>Actions</th></tr></thead><tbody>
        <?php foreach ($rows as $row): ?>
        <tr>
            <td><?php if ($row['image_url']): ?><img class="thumb" src="<?= e(public_image($row['image_url'])); ?>" alt=""><?php endif; ?></td>
            <td><?= e($row['name']); ?><small><?= e($row['slug']); ?></small></td>
            <td><?= (int) $row['display_order']; ?></td>
            <td><span class="pill"><?= $row['status'] ? 'Active' : 'Inactive'; ?></span></td>
            <td><a href="?edit=<?= (int) $row['id']; ?>">Edit</a> <a class="danger-link" href="?delete=<?= (int) $row['id']; ?>" data-confirm>Delete</a></td>
        </tr>
        <?php endforeach; ?>
    </tbody></table></div>
    <div class="pagination"><?php for ($i = 1; $i <= max(1, ceil($total / $limit)); $i++): ?><a class="<?= $i === $page ? 'active' : ''; ?>" href="?page=<?= $i; ?>&search=<?= e($search); ?>"><?= $i; ?></a><?php endfor; ?></div>
</section>
<?php require __DIR__ . '/../footer.php'; ?>
