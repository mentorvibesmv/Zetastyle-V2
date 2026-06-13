<?php
require_once __DIR__ . '/../bootstrap.php';
require_admin();
$pdo = admin_db();
$adminTitle = 'WhatsApp Enquiries';

// Handle delete (verify CSRF from GET)
if (isset($_GET['delete'])) {
    $token = (string)($_GET['csrf_token'] ?? '');
    if (!hash_equals(csrf_token(), $token)) {
        http_response_code(419);
        exit('Invalid security token.');
    }
    $pdo->prepare('DELETE FROM enquiries WHERE id = :id')->execute([':id' => (int)$_GET['delete']]);
    redirect_with(admin_url('enquiries/'), 'Enquiry deleted.');
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    verify_csrf();
    $id = (int)($_POST['id'] ?? 0);
    $status = trim((string)($_POST['status'] ?? ''));
    if ($id > 0 && in_array($status, ['New', 'Contacted', 'Converted', 'Cancelled'], true)) {
        $pdo->prepare('UPDATE enquiries SET status = :s WHERE id = :id')->execute([':s' => $status, ':id' => $id]);
        redirect_with(admin_url('enquiries/'), 'Enquiry status updated.');
    }
}

// CSV Export
if (isset($_GET['export'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="zetastyle-enquiries.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID', 'Name', 'Phone', 'Address', 'City', 'State', 'Pincode', 'Payment', 'Total', 'Status', 'Date']);
    
    $stmt = $pdo->query('SELECT id, customer_name, phone, address, city, state, pincode, payment_method, total_amount, status, created_at FROM enquiries ORDER BY id DESC');
    while ($row = $stmt->fetch()) {
        fputcsv($out, $row);
    }
    exit;
}

$search = trim((string)($_GET['search'] ?? ''));
$where = $search !== '' ? 'WHERE customer_name LIKE :s OR phone LIKE :s OR city LIKE :s OR state LIKE :s' : '';
$stmt = $pdo->prepare("SELECT * FROM enquiries {$where} ORDER BY id DESC");
if ($search !== '') {
    $stmt->bindValue(':s', "%{$search}%");
}
$stmt->execute();
$rows = $stmt->fetchAll();

require_once __DIR__ . '/../header.php';
?>
<section class="panel">
    <div class="panel-head">
        <h2>WhatsApp Enquiries</h2>
        <form class="search-form" method="get">
            <input name="search" value="<?= e($search); ?>" placeholder="Search enquiries...">
            <button class="admin-btn">Search</button>
            <a class="admin-btn" href="?export=1">Export CSV</a>
        </form>
    </div>
    
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Customer Details</th>
                    <th>Ordered Items</th>
                    <th>Billing Summary</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): 
                    // Fetch items for this enquiry
                    $itemsStmt = $pdo->prepare('SELECT * FROM enquiry_items WHERE enquiry_id = :id');
                    $itemsStmt->execute([':id' => $row['id']]);
                    $items = $itemsStmt->fetchAll();
                ?>
                    <tr>
                        <td>#<?= $row['id']; ?></td>
                        <td>
                            <strong><?= e($row['customer_name']); ?></strong><br>
                            Phone: <?= e($row['phone']); ?><br>
                            Address: <?= e($row['address']); ?>, <?= e($row['city']); ?>, <?= e($row['state']); ?> - <?= e($row['pincode']); ?>
                        </td>
                        <td>
                            <ul style="padding-left: 16px; margin: 0; font-size: 0.85rem; color: var(--muted);">
                                <?php foreach ($items as $item): ?>
                                    <li><?= e($item['product_name']); ?> (x<?= $item['quantity']; ?>) @ <?= money((float)$item['price']); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </td>
                        <td>
                            Subtotal: <?= money((float)$row['total_amount'] - (float)$row['shipping_charge'] - (float)$row['cod_charge']); ?><br>
                            Shipping: <?= money((float)$row['shipping_charge']); ?><br>
                            COD Charge: <?= money((float)$row['cod_charge']); ?><br>
                            <strong>Grand Total: <?= money((float)$row['total_amount']); ?></strong><br>
                            <small>Method: <?= e($row['payment_method']); ?></small>
                        </td>
                        <td>
                            <form method="post" style="display: inline-flex; align-items: center; gap: 5px;">
                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">
                                <input type="hidden" name="action" value="update_status">
                                <input type="hidden" name="id" value="<?= $row['id']; ?>">
                                <select name="status" onchange="this.form.submit()" style="padding: 4px; border-radius: 4px; border: 1px solid var(--border); font-size: 0.85rem;">
                                    <?php foreach (['New', 'Contacted', 'Converted', 'Cancelled'] as $status): ?>
                                        <option value="<?= $status; ?>" <?= $row['status'] === $status ? 'selected' : ''; ?>><?= $status; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                        </td>
                        <td>
                            <div style="display: flex; flex-direction: column; gap: 6px;">
                                <?php if ($row['status'] !== 'Converted'): ?>
                                    <form action="convert.php" method="post">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">
                                        <input type="hidden" name="id" value="<?= $row['id']; ?>">
                                        <button class="admin-btn primary" style="padding: 4px 8px; font-size: 0.8rem; min-height: auto; width: 100%;">Convert to Order</button>
                                    </form>
                                <?php endif; ?>
                                <a class="danger-link" href="?delete=<?= (int)$row['id']; ?>&csrf_token=<?= e(csrf_token()); ?>" onclick="return confirm('Are you sure you want to delete this enquiry?')" style="text-align: center; font-size: 0.85rem; padding: 4px; border: 1px solid red; border-radius: 4px; color: red;">Delete</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php require_once __DIR__ . '/../footer.php'; ?>
