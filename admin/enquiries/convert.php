<?php
declare(strict_types=1);
require_once __DIR__ . '/../bootstrap.php';
require_admin();
verify_csrf();

$id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
if ($id <= 0) {
    redirect_with(admin_url('enquiries/'), 'Invalid Enquiry ID.', 'error');
}

$pdo = admin_db();

// 1. Fetch enquiry
$stmt = $pdo->prepare('SELECT * FROM enquiries WHERE id = :id');
$stmt->execute([':id' => $id]);
$enquiry = $stmt->fetch();

if (!$enquiry) {
    redirect_with(admin_url('enquiries/'), 'Enquiry not found.', 'error');
}

if ($enquiry['status'] === 'Converted') {
    redirect_with(admin_url('enquiries/'), 'Enquiry has already been converted to an order.', 'error');
}

try {
    $pdo->beginTransaction();
    
    // 2. Generate sequential Order ID: ZS000001
    // We count or find maximum Order ID
    $maxStmt = $pdo->query("SELECT order_id FROM orders WHERE order_id REGEXP '^ZS[0-9]+$' ORDER BY order_id DESC LIMIT 1");
    $maxOrder = $maxStmt->fetchColumn();
    
    $nextNumber = 1;
    if ($maxOrder) {
        $numPart = preg_replace('/[^0-9]/', '', $maxOrder);
        if ($numPart !== '') {
            $nextNumber = (int)$numPart + 1;
        }
    }
    $orderId = 'ZS' . str_pad((string)$nextNumber, 6, '0', STR_PAD_LEFT);
    
    // 3. Move/Copy to orders
    $orderStmt = $pdo->prepare(
        'INSERT INTO orders (order_id, customer_name, phone, address, city, state, pincode, payment_method, shipping_charge, cod_charge, total_amount, status)
         VALUES (:order_id, :name, :phone, :address, :city, :state, :pincode, :payment_method, :shipping, :cod, :total, "Confirmed")'
    );
    $orderStmt->execute([
        ':order_id' => $orderId,
        ':name' => $enquiry['customer_name'],
        ':phone' => $enquiry['phone'],
        ':address' => $enquiry['address'],
        ':city' => $enquiry['city'],
        ':state' => $enquiry['state'],
        ':pincode' => $enquiry['pincode'],
        ':payment_method' => $enquiry['payment_method'],
        ':shipping' => $enquiry['shipping_charge'],
        ':cod' => $enquiry['cod_charge'],
        ':total' => $enquiry['total_amount']
    ]);
    
    $orderPk = (int)$pdo->lastInsertId();
    
    // 4. Copy items
    $itemsStmt = $pdo->prepare('SELECT * FROM enquiry_items WHERE enquiry_id = :id');
    $itemsStmt->execute([':id' => $id]);
    $items = $itemsStmt->fetchAll();
    
    $insertItem = $pdo->prepare(
        'INSERT INTO order_items (order_id, product_name, quantity, price)
         VALUES (:order_id, :name, :qty, :price)'
    );
    foreach ($items as $item) {
        $insertItem->execute([
            ':order_id' => $orderPk,
            ':name' => $item['product_name'],
            ':qty' => $item['quantity'],
            ':price' => $item['price']
        ]);
    }
    
    // 5. Update enquiry status
    $updateStmt = $pdo->prepare('UPDATE enquiries SET status = "Converted" WHERE id = :id');
    $updateStmt->execute([':id' => $id]);
    
    $pdo->commit();
    
    log_activity('converted_enquiry', 'enquiries', $id);
    redirect_with(admin_url('orders/'), 'Enquiry converted successfully! Generated Order ID: ' . $orderId);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    redirect_with(admin_url('enquiries/'), 'Failed to convert enquiry: ' . $e->getMessage(), 'error');
}
