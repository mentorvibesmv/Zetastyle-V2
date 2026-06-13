<?php
require_once __DIR__ . '/bootstrap.php';
require_admin();
$pdo = admin_db();
$adminTitle = 'Analytics Dashboard';

// 1. Core counters
$totalSales = (float) $pdo->query("SELECT SUM(total_amount) FROM orders WHERE status != 'Cancelled'")->fetchColumn();
$totalOrders = (int) $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalEnquiries = (int) $pdo->query("SELECT COUNT(*) FROM enquiries")->fetchColumn();
$totalProducts = (int) $pdo->query("SELECT COUNT(*) FROM products WHERE is_active = 1")->fetchColumn();

// 2. Orders by Status
$orderStatuses = $pdo->query("SELECT status, COUNT(*) as cnt, SUM(total_amount) as amt FROM orders GROUP BY status")->fetchAll();

// 3. Enquiries by Status
$enquiryStatuses = $pdo->query("SELECT status, COUNT(*) as cnt FROM enquiries GROUP BY status")->fetchAll();

// 4. Sales by State
$stateSales = $pdo->query("SELECT state, COUNT(*) as cnt, SUM(total_amount) as amt FROM orders WHERE status != 'Cancelled' GROUP BY state ORDER BY amt DESC")->fetchAll();

// 5. Payment Methods
$paymentMethods = $pdo->query("SELECT payment_method, COUNT(*) as cnt, SUM(total_amount) as amt FROM orders WHERE status != 'Cancelled' GROUP BY payment_method")->fetchAll();

// 6. Recent Orders
$recentOrders = $pdo->query("SELECT * FROM orders ORDER BY id DESC LIMIT 5")->fetchAll();

// 7. Recent Enquiries
$recentEnquiries = $pdo->query("SELECT * FROM enquiries ORDER BY id DESC LIMIT 5")->fetchAll();

require_once __DIR__ . '/header.php';
?>
<section class="panel">
    <div class="panel-head">
        <h2>Sales & Analytics Overview</h2>
    </div>
    
    <!-- Stat Cards -->
    <div class="stats-grid" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px;">
        <div class="stat-card" style="padding: 24px; border: 1px solid var(--border); border-radius: var(--radius); background: var(--secondary); box-shadow: var(--shadow);">
            <small style="color: var(--muted); text-transform: uppercase; font-weight: 600; letter-spacing: 0.05em;">Total Sales</small>
            <h3 style="font-size: 2rem; margin-top: 8px; font-family: 'Poppins', sans-serif;"><?= money($totalSales); ?></h3>
        </div>
        <div class="stat-card" style="padding: 24px; border: 1px solid var(--border); border-radius: var(--radius); background: var(--secondary); box-shadow: var(--shadow);">
            <small style="color: var(--muted); text-transform: uppercase; font-weight: 600; letter-spacing: 0.05em;">Total Orders</small>
            <h3 style="font-size: 2rem; margin-top: 8px; font-family: 'Poppins', sans-serif;"><?= $totalOrders; ?></h3>
        </div>
        <div class="stat-card" style="padding: 24px; border: 1px solid var(--border); border-radius: var(--radius); background: var(--secondary); box-shadow: var(--shadow);">
            <small style="color: var(--muted); text-transform: uppercase; font-weight: 600; letter-spacing: 0.05em;">Total Enquiries</small>
            <h3 style="font-size: 2rem; margin-top: 8px; font-family: 'Poppins', sans-serif;"><?= $totalEnquiries; ?></h3>
        </div>
        <div class="stat-card" style="padding: 24px; border: 1px solid var(--border); border-radius: var(--radius); background: var(--secondary); box-shadow: var(--shadow);">
            <small style="color: var(--muted); text-transform: uppercase; font-weight: 600; letter-spacing: 0.05em;">Active Products</small>
            <h3 style="font-size: 2rem; margin-top: 8px; font-family: 'Poppins', sans-serif;"><?= $totalProducts; ?></h3>
        </div>
    </div>

    <!-- Data Distribution Rows -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px;">
        <!-- Orders by Status -->
        <div class="sub-panel" style="padding: 24px; border: 1px solid var(--border); border-radius: var(--radius); background: var(--secondary); box-shadow: var(--shadow);">
            <h4 style="margin-bottom: 16px; font-size: 1.2rem; font-family: 'Playfair Display', serif;">Orders by Status</h4>
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--border); text-align: left;">
                        <th style="padding: 8px 0; color: var(--muted);">Status</th>
                        <th style="padding: 8px 0; color: var(--muted);">Count</th>
                        <th style="padding: 8px 0; color: var(--muted); text-align: right;">Total Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orderStatuses as $status): ?>
                        <tr style="border-bottom: 1px dotted var(--border);">
                            <td style="padding: 10px 0;"><span class="pill" style="padding: 4px 8px; border-radius: 4px; font-size: 0.8rem; background: #eee;"><?= e($status['status']); ?></span></td>
                            <td style="padding: 10px 0;"><?= $status['cnt']; ?></td>
                            <td style="padding: 10px 0; text-align: right;"><?= money((float)$status['amt']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Enquiries Statuses -->
        <div class="sub-panel" style="padding: 24px; border: 1px solid var(--border); border-radius: var(--radius); background: var(--secondary); box-shadow: var(--shadow);">
            <h4 style="margin-bottom: 16px; font-size: 1.2rem; font-family: 'Playfair Display', serif;">Enquiries by Status</h4>
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--border); text-align: left;">
                        <th style="padding: 8px 0; color: var(--muted);">Status</th>
                        <th style="padding: 8px 0; color: var(--muted);">Count</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($enquiryStatuses as $status): ?>
                        <tr style="border-bottom: 1px dotted var(--border);">
                            <td style="padding: 10px 0;"><span class="pill" style="padding: 4px 8px; border-radius: 4px; font-size: 0.8rem; background: #eee;"><?= e($status['status']); ?></span></td>
                            <td style="padding: 10px 0;"><?= $status['cnt']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px;">
        <!-- Sales by State -->
        <div class="sub-panel" style="padding: 24px; border: 1px solid var(--border); border-radius: var(--radius); background: var(--secondary); box-shadow: var(--shadow);">
            <h4 style="margin-bottom: 16px; font-size: 1.2rem; font-family: 'Playfair Display', serif;">Sales by Region</h4>
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--border); text-align: left;">
                        <th style="padding: 8px 0; color: var(--muted);">Region</th>
                        <th style="padding: 8px 0; color: var(--muted);">Orders</th>
                        <th style="padding: 8px 0; color: var(--muted); text-align: right;">Sales</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stateSales as $state): ?>
                        <tr style="border-bottom: 1px dotted var(--border);">
                            <td style="padding: 10px 0;"><?= e($state['state'] ?: 'Not Specified'); ?></td>
                            <td style="padding: 10px 0;"><?= $state['cnt']; ?></td>
                            <td style="padding: 10px 0; text-align: right;"><?= money((float)$state['amt']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Payment Methods -->
        <div class="sub-panel" style="padding: 24px; border: 1px solid var(--border); border-radius: var(--radius); background: var(--secondary); box-shadow: var(--shadow);">
            <h4 style="margin-bottom: 16px; font-size: 1.2rem; font-family: 'Playfair Display', serif;">Payment Methods</h4>
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--border); text-align: left;">
                        <th style="padding: 8px 0; color: var(--muted);">Payment Method</th>
                        <th style="padding: 8px 0; color: var(--muted);">Count</th>
                        <th style="padding: 8px 0; color: var(--muted); text-align: right;">Total Sales</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($paymentMethods as $payment): ?>
                        <tr style="border-bottom: 1px dotted var(--border);">
                            <td style="padding: 10px 0;"><?= e($payment['payment_method']); ?></td>
                            <td style="padding: 10px 0;"><?= $payment['cnt']; ?></td>
                            <td style="padding: 10px 0; text-align: right;"><?= money((float)$payment['amt']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Data Logs -->
    <div style="display: grid; grid-template-columns: 1fr; gap: 30px;">
        <!-- Recent Orders -->
        <div class="sub-panel" style="padding: 24px; border: 1px solid var(--border); border-radius: var(--radius); background: var(--secondary); box-shadow: var(--shadow);">
            <h4 style="margin-bottom: 16px; font-size: 1.2rem; font-family: 'Playfair Display', serif;">Recent Orders</h4>
            <div class="table-wrap">
                <table style="width: 100%; border-collapse: collapse; text-align: left;">
                    <thead>
                        <tr style="border-bottom: 1px solid var(--border);">
                            <th style="padding: 10px 8px; color: var(--muted);">Order ID</th>
                            <th style="padding: 10px 8px; color: var(--muted);">Customer</th>
                            <th style="padding: 10px 8px; color: var(--muted);">Status</th>
                            <th style="padding: 10px 8px; color: var(--muted);">Date</th>
                            <th style="padding: 10px 8px; color: var(--muted); text-align: right;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentOrders as $order): ?>
                            <tr style="border-bottom: 1px dotted var(--border);">
                                <td style="padding: 12px 8px;"><strong><?= e($order['order_id']); ?></strong></td>
                                <td style="padding: 12px 8px;"><?= e($order['customer_name']); ?><br><small style="color: var(--muted);"><?= e($order['phone']); ?></small></td>
                                <td style="padding: 12px 8px;"><span class="pill" style="padding: 4px 8px; border-radius: 4px; font-size: 0.8rem; background: #eee;"><?= e($order['status']); ?></span></td>
                                <td style="padding: 12px 8px; font-size: 0.9rem;"><?= e($order['created_at']); ?></td>
                                <td style="padding: 12px 8px; text-align: right; font-weight: 600;"><?= money((float)$order['total_amount']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
<?php
require_once __DIR__ . '/footer.php';
?>
