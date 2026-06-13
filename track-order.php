<?php
require_once __DIR__ . '/includes/functions.php';

$meta = page_meta('Track Order', 'Track your ZetaStyle custom printed clothing order status.');
$trackingQuery = trim((string) ($_GET['track'] ?? $_POST['track'] ?? ''));
$trackedOrder = null;
$trackError = '';

if ($trackingQuery !== '') {
    $pdo = db();
    if ($pdo instanceof PDO) {
        $statement = $pdo->prepare(
            'SELECT * FROM orders WHERE order_id = :query OR phone = :query ORDER BY id DESC LIMIT 1'
        );
        $statement->execute([':query' => $trackingQuery]);
        $trackedOrder = $statement->fetch();

        if (!$trackedOrder) {
            $trackError = 'No order was found for that Order ID or phone number.';
        }
    } else {
        $trackError = 'Tracking is temporarily unavailable. Please try again later.';
    }
}

$statuses = ['Confirmed', 'Packing', 'Dispatched', 'Delivered'];
$statusLabels = [
    'Confirmed' => 'Order Confirmed',
    'Packing' => 'Packing & Printing',
    'Dispatched' => 'Dispatched',
    'Delivered' => 'Delivered',
];
$statusDescriptions = [
    'Confirmed' => 'Artwork and garment details received.',
    'Packing' => 'Your custom print is in production and packing.',
    'Dispatched' => 'Courier pickup and tracking assignment.',
    'Delivered' => 'Your order has arrived.',
];
$currentStatus = $trackedOrder['status'] ?? 'Packing';
$currentIndex = array_search($currentStatus, $statuses, true);
if ($currentStatus === 'Cancelled') {
    $currentIndex = -2;
}

require_once __DIR__ . '/includes/header.php';
?>
<section class="page-hero compact">
    <div class="container">
        <p class="eyebrow">Order tracking</p>
        <h1>Track your print order.</h1>
        <p>Enter your Order ID or phone number to view the latest production and delivery status.</p>
    </div>
</section>
<section class="section">
    <div class="container track-layout">
        <form class="track-form reveal" method="post">
            <label for="order-id">Order ID or Phone Number</label>
            <div>
                <input id="order-id" name="track" type="text" value="<?= e($trackingQuery); ?>" placeholder="ZS000001 or +15550199" required>
                <button class="btn btn-dark" type="submit">Track</button>
            </div>
        </form>

        <?php if ($trackError): ?>
            <div class="empty-cart reveal"><?= e($trackError); ?></div>
        <?php endif; ?>

        <?php if ($trackedOrder): ?>
            <article class="contact-panel reveal">
                <h2><?= e($trackedOrder['order_id']); ?></h2>
                <p><strong>Status:</strong> <?= e($trackedOrder['status']); ?></p>
                <p><strong>Courier:</strong> <?= e($trackedOrder['courier_name'] ?: 'Not assigned yet'); ?></p>
                <p><strong>Tracking ID:</strong> <?= e($trackedOrder['tracking_number'] ?: 'Not assigned yet'); ?></p>
                <?php if ($trackedOrder['tracking_url'] !== ''): ?>
                    <p><strong>Tracking Link:</strong> <a href="<?= e($trackedOrder['tracking_url']); ?>" target="_blank" rel="noopener" style="color: var(--accent); text-decoration: underline; font-weight: 500;">Track on Courier Site</a></p>
                <?php endif; ?>
                <?php if ($trackedOrder['dispatch_date'] !== null): ?>
                    <p><strong>Dispatch Date:</strong> <?= e(date('F j, Y', strtotime($trackedOrder['dispatch_date']))); ?></p>
                <?php endif; ?>
                <p><strong>Estimated Delivery:</strong> <?= e($trackedOrder['expected_delivery'] ?: 'To be confirmed'); ?></p>
            </article>
        <?php endif; ?>

        <ol class="timeline reveal">
            <?php foreach ($statuses as $index => $status): ?>
                <li class="<?= $currentIndex >= $index ? 'done' : ''; ?>">
                    <span></span>
                    <strong><?= e($statusLabels[$status]); ?></strong>
                    <small><?= e($statusDescriptions[$status]); ?></small>
                </li>
            <?php endforeach; ?>
            <?php if ($currentStatus === 'Cancelled'): ?>
                <li class="done"><span></span><strong>Cancelled</strong><small>This order has been cancelled by the studio.</small></li>
            <?php endif; ?>
        </ol>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
