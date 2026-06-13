<?php
require_once __DIR__ . '/../bootstrap.php';
require_admin();
$pdo = admin_db();
$adminTitle = 'Orders';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id = (int)($_POST['id'] ?? 0);
    
    $items = array_values(array_filter((array)($_POST['items'] ?? []), function($i) {
        return trim((string)($i['product_name'] ?? '')) !== '';
    }));
    
    $subtotal = 0.0;
    foreach ($items as $item) {
        $subtotal += (float)$item['price'] * (int)$item['quantity'];
    }
    
    $shipping_charge = (float)($_POST['shipping_charge'] ?? 0.0);
    $cod_charge = (float)($_POST['cod_charge'] ?? 0.0);
    $total = $subtotal + $shipping_charge + $cod_charge;
    
    $orderId = trim((string)($_POST['order_id'] ?? '')) ?: 'ZS' . str_pad((string)random_int(1, 999999), 6, '0', STR_PAD_LEFT);
    
    $data = [
        ':order_id' => $orderId,
        ':customer_name' => trim($_POST['customer_name']),
        ':phone' => trim($_POST['phone']),
        ':address' => trim($_POST['address']),
        ':city' => trim($_POST['city'] ?? ''),
        ':state' => trim($_POST['state'] ?? ''),
        ':pincode' => trim($_POST['pincode'] ?? ''),
        ':payment_method' => trim($_POST['payment_method'] ?? 'COD'),
        ':shipping_charge' => $shipping_charge,
        ':cod_charge' => $cod_charge,
        ':notes' => trim($_POST['notes'] ?? ''),
        ':status' => $_POST['status'],
        ':courier_name' => trim($_POST['courier_name'] ?? ''),
        ':tracking_number' => trim($_POST['tracking_number'] ?? ''),
        ':tracking_url' => trim($_POST['tracking_url'] ?? ''),
        ':dispatch_date' => $_POST['dispatch_date'] ?: null,
        ':expected_delivery' => $_POST['expected_delivery'] ?: null,
        ':total_amount' => $total
    ];
    
    if ($id > 0) {
        $data[':id'] = $id;
        $pdo->prepare('UPDATE orders SET order_id=:order_id,customer_name=:customer_name,phone=:phone,address=:address,city=:city,state=:state,pincode=:pincode,payment_method=:payment_method,shipping_charge=:shipping_charge,cod_charge=:cod_charge,notes=:notes,status=:status,courier_name=:courier_name,tracking_number=:tracking_number,tracking_url=:tracking_url,dispatch_date=:dispatch_date,expected_delivery=:expected_delivery,total_amount=:total_amount WHERE id=:id')->execute($data);
        $pdo->prepare('DELETE FROM order_items WHERE order_id=:id')->execute([':id' => $id]);
        $orderPk = $id;
    } else {
        $pdo->prepare('INSERT INTO orders (order_id,customer_name,phone,address,city,state,pincode,payment_method,shipping_charge,cod_charge,notes,status,courier_name,tracking_number,tracking_url,dispatch_date,expected_delivery,total_amount) VALUES (:order_id,:customer_name,:phone,:address,:city,:state,:pincode,:payment_method,:shipping_charge,:cod_charge,:notes,:status,:courier_name,:tracking_number,:tracking_url,:dispatch_date,:expected_delivery,:total_amount)')->execute($data);
        $orderPk = (int)$pdo->lastInsertId();
    }
    
    foreach ($items as $item) {
        $pdo->prepare('INSERT INTO order_items (order_id,product_name,quantity,price) VALUES (:order_id,:product_name,:quantity,:price)')->execute([
            ':order_id' => $orderPk,
            ':product_name' => trim($item['product_name']),
            ':quantity' => (int)$item['quantity'],
            ':price' => (float)$item['price']
        ]);
    }
    
    redirect_with(admin_url('orders/'), 'Order saved successfully. Order ID: ' . $orderId);
}

// Handle delete (verify CSRF in GET manually)
if (isset($_GET['delete'])) {
    $token = (string)($_GET['csrf_token'] ?? '');
    if (!hash_equals(csrf_token(), $token)) {
        http_response_code(419);
        exit('Invalid security token.');
    }
    $pdo->prepare('DELETE FROM orders WHERE id=:id')->execute([':id' => (int)$_GET['delete']]);
    redirect_with(admin_url('orders/'), 'Order deleted.');
}

$edit = null;
$editItems = [];
if (isset($_GET['edit'])) {
    $s = $pdo->prepare('SELECT * FROM orders WHERE id=:id');
    $s->execute([':id' => (int)$_GET['edit']]);
    $edit = $s->fetch();
    
    $it = $pdo->prepare('SELECT * FROM order_items WHERE order_id=:id');
    $it->execute([':id' => (int)$_GET['edit']]);
    $editItems = $it->fetchAll();
}

$rows = $pdo->query('SELECT * FROM orders ORDER BY id DESC LIMIT 50')->fetchAll();
require __DIR__ . '/../header.php';
?>
<section class="panel">
    <div class="panel-head">
        <h2><?= $edit ? 'Edit' : 'Create'; ?> Order</h2>
    </div>
    <form class="admin-form product-form" method="post" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">
        <input type="hidden" name="id" value="<?= e((string)($edit['id'] ?? '')); ?>">
        
        <label>Order ID
            <input name="order_id" value="<?= e((string)($edit['order_id'] ?? '')); ?>" placeholder="e.g. ZS000001 (Auto generated if empty)">
        </label>
        
        <label>Customer Name
            <input name="customer_name" value="<?= e((string)($edit['customer_name'] ?? '')); ?>" required>
        </label>
        
        <label>Phone Number
            <input name="phone" value="<?= e((string)($edit['phone'] ?? '')); ?>" required>
        </label>
        
        <label>Status
            <select name="status">
                <?php foreach(order_statuses() as $status): ?>
                    <option value="<?= e($status); ?>" <?= ($edit['status'] ?? 'Confirmed') === $status ? 'selected' : ''; ?>><?= e($status); ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        
        <label class="wide">Address
            <textarea name="address" rows="2" required><?= e((string)($edit['address'] ?? '')); ?></textarea>
        </label>
        
        <label>City
            <input name="city" value="<?= e((string)($edit['city'] ?? '')); ?>" required>
        </label>
        
        <label>State
            <input name="state" value="<?= e((string)($edit['state'] ?? '')); ?>" required>
        </label>
        
        <label>Pincode
            <input name="pincode" value="<?= e((string)($edit['pincode'] ?? '')); ?>" required>
        </label>
        
        <label>Payment Method
            <select name="payment_method">
                <option value="COD" <?= ($edit['payment_method'] ?? 'COD') === 'COD' ? 'selected' : ''; ?>>Cash on Delivery (COD)</option>
                <option value="Online" <?= ($edit['payment_method'] ?? 'COD') === 'Online' ? 'selected' : ''; ?>>Online Payment</option>
            </select>
        </label>
        
        <label>Shipping Charge
            <input type="number" step="0.01" name="shipping_charge" value="<?= e((string)($edit['shipping_charge'] ?? '0.00')); ?>">
        </label>
        
        <label>COD Charge
            <input type="number" step="0.01" name="cod_charge" value="<?= e((string)($edit['cod_charge'] ?? '0.00')); ?>">
        </label>
        
        <div class="wide" style="border-top: 1px solid var(--border); padding-top: 15px; margin-top: 10px;">
            <h3>Courier & Tracking Details</h3>
        </div>
        
        <label>Courier Name
            <input name="courier_name" value="<?= e((string)($edit['courier_name'] ?? '')); ?>" placeholder="e.g. DHL, BlueDart">
        </label>
        
        <label>Tracking ID (Number)
            <input name="tracking_number" value="<?= e((string)($edit['tracking_number'] ?? '')); ?>" placeholder="e.g. 123456789">
        </label>
        
        <label class="wide">Tracking Link (URL)
            <input name="tracking_url" type="url" value="<?= e((string)($edit['tracking_url'] ?? '')); ?>" placeholder="e.g. https://dhl.com/track?id=123456">
        </label>
        
        <label>Dispatch Date
            <input type="date" name="dispatch_date" value="<?= e((string)($edit['dispatch_date'] ?? '')); ?>">
        </label>
        
        <label>Expected Delivery
            <input type="date" name="expected_delivery" value="<?= e((string)($edit['expected_delivery'] ?? '')); ?>">
        </label>
        
        <label class="wide">Admin Notes
            <textarea name="notes" rows="2"><?= e((string)($edit['notes'] ?? '')); ?></textarea>
        </label>
        
        <div class="wide" style="border-top: 1px solid var(--border); padding-top: 15px; margin-top: 10px;">
            <h3>Products List</h3>
            <div id="order-items-wrap">
                <?php 
                $items = $editItems ?: [['product_name' => '', 'quantity' => 1, 'price' => '']]; 
                foreach($items as $i => $item): 
                ?>
                    <div class="order-item-row" style="display: flex; gap: 10px; margin-bottom: 10px;">
                        <input name="items[<?= $i; ?>][product_name]" placeholder="Product name" value="<?= e((string)($item['product_name'] ?? '')); ?>" style="flex: 2;" required>
                        <input type="number" name="items[<?= $i; ?>][quantity]" value="<?= e((string)($item['quantity'] ?? 1)); ?>" style="width: 80px;" min="1" required>
                        <input type="number" step="0.01" name="items[<?= $i; ?>][price]" placeholder="Price" value="<?= e((string)($item['price'] ?? '')); ?>" style="width: 120px;" required>
                        <?php if ($i > 0 || count($items) > 1): ?>
                            <button type="button" class="admin-btn" onclick="this.parentElement.remove()" style="background: red; color: white; min-height: auto; border: 0;">Remove</button>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <button class="admin-btn" type="button" id="add-item-btn" style="margin-top: 5px;">Add Product Row</button>
        </div>
        
        <div class="wide" style="border-top: 1px solid var(--border); padding-top: 20px; text-align: right;">
            <button class="admin-btn primary" style="min-height: 48px; width: 200px;">Save Order</button>
        </div>
    </form>
</section>

<section class="panel">
    <div class="panel-head">
        <h2>Orders List</h2>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer details</th>
                    <th>Status</th>
                    <th>Courier & Tracking</th>
                    <th>Amount</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($rows as $row): ?>
                    <tr>
                        <td><strong><?= e($row['order_id']); ?></strong></td>
                        <td>
                            <strong><?= e($row['customer_name']); ?></strong><br>
                            Phone: <?= e($row['phone']); ?><br>
                            <small style="color: var(--muted);"><?= e($row['city']); ?>, <?= e($row['state']); ?></small>
                        </td>
                        <td>
                            <span class="pill" style="padding: 4px 8px; border-radius: 4px; font-size: 0.85rem; font-weight: 600; background: #eee;"><?= e($row['status']); ?></span>
                        </td>
                        <td>
                            <?php if ($row['courier_name']): ?>
                                <strong><?= e($row['courier_name']); ?></strong><br>
                                ID: <?= e($row['tracking_number']); ?><br>
                                <?php if ($row['tracking_url']): ?>
                                    <a href="<?= e($row['tracking_url']); ?>" target="_blank" rel="noopener" style="font-size: 0.8rem; text-decoration: underline; color: var(--accent);">Track Package</a>
                                <?php endif; ?>
                            <?php else: ?>
                                <span style="color: var(--muted); font-size: 0.85rem;">Not dispatched yet</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong>Total: <?= money((float)$row['total_amount']); ?></strong><br>
                            <small style="color: var(--muted);">Shipping: <?= money((float)$row['shipping_charge']); ?></small>
                        </td>
                        <td style="font-size: 0.85rem;"><?= e($row['created_at']); ?></td>
                        <td>
                            <a href="?edit=<?= (int)$row['id']; ?>" class="admin-btn" style="padding: 4px 8px; min-height: auto; font-size: 0.8rem; margin-right: 5px;">Edit</a>
                            <a class="danger-link" href="?delete=<?= (int)$row['id']; ?>&csrf_token=<?= e(csrf_token()); ?>" onclick="return confirm('Are you sure you want to delete this order?')" style="padding: 4px 8px; border: 1px solid red; border-radius: 4px; color: red; font-size: 0.8rem; display: inline-block;">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const wrap = document.getElementById('order-items-wrap');
    const addBtn = document.getElementById('add-item-btn');
    let index = <?= count($items); ?>;

    addBtn.addEventListener('click', () => {
        const row = document.createElement('div');
        row.className = 'order-item-row';
        row.style.display = 'flex';
        row.style.gap = '10px';
        row.style.marginBottom = '10px';
        row.innerHTML = `
            <input name="items[${index}][product_name]" placeholder="Product name" style="flex: 2;" required>
            <input type="number" name="items[${index}][quantity]" value="1" style="width: 80px;" min="1" required>
            <input type="number" step="0.01" name="items[${index}][price]" placeholder="Price" style="width: 120px;" required>
            <button type="button" class="admin-btn" onclick="this.parentElement.remove()" style="background: red; color: white; min-height: auto; border: 0;">Remove</button>
        `;
        wrap.appendChild(row);
        index++;
    });
});
</script>

<?php require __DIR__ . '/../footer.php'; ?>
