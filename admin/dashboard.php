<?php
require_once __DIR__ . '/bootstrap.php';
require_admin();

$pdo = admin_db();
$adminTitle = 'Dashboard';
$cards = [
    'Total Products' => admin_count('products'),
    'Total Categories' => admin_count('categories'),
    'Total Orders' => admin_count('orders'),
    'Pending Orders' => admin_count('orders', "status = 'pending'"),
    'Printing Orders' => admin_count('orders', "status = 'printing'"),
    'Packed Orders' => admin_count('orders', "status = 'packed'"),
    'Dispatched Orders' => admin_count('orders', "status = 'shipped'"),
    'Delivered Orders' => admin_count('orders', "status = 'delivered'"),
    'Total Blogs' => admin_count('blogs'),
    'Contact Messages' => admin_count('contact_messages'),
];
$latestOrders = $pdo->query('SELECT order_id, customer_name, phone, status, total_amount, created_at FROM orders ORDER BY id DESC LIMIT 6')->fetchAll();
$latestEnquiries = $pdo->query('SELECT name, email, phone, created_at FROM contact_messages ORDER BY id DESC LIMIT 6')->fetchAll();
require __DIR__ . '/header.php';
?>
<section class="card-grid">
    <?php foreach ($cards as $label => $value): ?>
        <article class="stat-card">
            <span><?= e($label); ?></span>
            <strong><?= (int) $value; ?></strong>
        </article>
    <?php endforeach; ?>
</section>
<section class="two-col">
    <article class="panel">
        <div class="panel-head"><h2>Latest Orders</h2><a href="<?= admin_url('orders/'); ?>">Manage</a></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Order</th><th>Customer</th><th>Status</th><th>Total</th></tr></thead>
                <tbody>
                <?php foreach ($latestOrders as $order): ?>
                    <tr>
                        <td><?= e($order['order_id']); ?></td>
                        <td><?= e($order['customer_name']); ?><small><?= e($order['phone']); ?></small></td>
                        <td><span class="pill"><?= e($order['status']); ?></span></td>
                        <td><?= money((float) $order['total_amount']); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </article>
    <article class="panel">
        <div class="panel-head"><h2>Latest Enquiries</h2><a href="<?= admin_url('enquiries/'); ?>">View</a></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Name</th><th>Email</th><th>Phone</th></tr></thead>
                <tbody>
                <?php foreach ($latestEnquiries as $enquiry): ?>
                    <tr>
                        <td><?= e($enquiry['name']); ?></td>
                        <td><?= e($enquiry['email']); ?></td>
                        <td><?= e($enquiry['phone']); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </article>
</section>
<?php require __DIR__ . '/footer.php'; ?>
