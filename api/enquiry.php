<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

// Support both JSON raw input and standard POST
$input = json_decode(file_get_contents('php://input'), true);
if ($input === null) {
    $input = $_POST;
}

$name = trim((string)($input['name'] ?? ''));
$phone = trim((string)($input['phone'] ?? ''));
$address = trim((string)($input['address'] ?? ''));
$city = trim((string)($input['city'] ?? ''));
$state = trim((string)($input['state'] ?? ''));
$pincode = trim((string)($input['pincode'] ?? ''));
$payment_method = trim((string)($input['payment_method'] ?? 'COD'));
$cart = $input['cart'] ?? [];

if ($name === '' || $phone === '' || $address === '' || $city === '' || $state === '' || $pincode === '') {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'All delivery fields are required.']);
    exit;
}

if (empty($cart)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Your cart is empty.']);
    exit;
}

// Calculate totals
$subtotal = 0.0;
foreach ($cart as $item) {
    $subtotal += (float)$item['price'] * (int)$item['quantity'];
}

$shipping_tn = (float) getSetting('shipping_tn', '0');
$shipping_other = (float) getSetting('shipping_other', '0');
$cod_val = (float) getSetting('cod_charge', '0');

$shipping_charge = (strcasecmp($state, 'Tamil Nadu') === 0) ? $shipping_tn : $shipping_other;
$cod_charge = (strcasecmp($payment_method, 'COD') === 0) ? $cod_val : 0.0;
$grand_total = $subtotal + $shipping_charge + $cod_charge;

$pdo = db();
if (!$pdo instanceof PDO) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit;
}

try {
    $pdo->beginTransaction();
    
    // Save Enquiry
    $stmt = $pdo->prepare(
        'INSERT INTO enquiries (customer_name, phone, address, city, state, pincode, payment_method, shipping_charge, cod_charge, total_amount, status)
         VALUES (:name, :phone, :address, :city, :state, :pincode, :payment_method, :shipping, :cod, :total, "New")'
    );
    $stmt->execute([
        ':name' => $name,
        ':phone' => $phone,
        ':address' => $address,
        ':city' => $city,
        ':state' => $state,
        ':pincode' => $pincode,
        ':payment_method' => $payment_method,
        ':shipping' => $shipping_charge,
        ':cod' => $cod_charge,
        ':total' => $grand_total
    ]);
    
    $enquiryId = (int)$pdo->lastInsertId();
    
    // Save items
    $itemStmt = $pdo->prepare(
        'INSERT INTO enquiry_items (enquiry_id, product_name, quantity, price)
         VALUES (:enquiry_id, :name, :qty, :price)'
    );
    foreach ($cart as $item) {
        $itemStmt->execute([
            ':enquiry_id' => $enquiryId,
            ':name' => $item['name'],
            ':qty' => (int)$item['quantity'],
            ':price' => (float)$item['price']
        ]);
    }
    
    $pdo->commit();
    
    // Generate WhatsApp formatted message
    $whatsapp_num = getSetting('whatsapp_number');
    $whatsapp_clean = preg_replace('/[^0-9]/', '', $whatsapp_num);
    
    $currency_symbol = CURRENCY;
    
    $message = "Hello ZetaStyle, I would like to place an order (Enquiry #{$enquiryId}):\n\n";
    $message .= "Items Ordered:\n";
    foreach ($cart as $item) {
        $item_total = (float)$item['price'] * (int)$item['quantity'];
        $message .= "- " . $item['name'] . " (x" . $item['quantity'] . ") - " . $currency_symbol . number_format($item_total, 2) . "\n";
    }
    $message .= "\n";
    $message .= "Subtotal: " . $currency_symbol . number_format($subtotal, 2) . "\n";
    $message .= "Shipping: " . $currency_symbol . number_format($shipping_charge, 2) . "\n";
    if ($cod_charge > 0) {
        $message .= "COD Charge: " . $currency_symbol . number_format($cod_charge, 2) . "\n";
    }
    $message .= "Grand Total: " . $currency_symbol . number_format($grand_total, 2) . "\n\n";
    $message .= "Delivery Address:\n";
    $message .= "Name: {$name}\n";
    $message .= "Phone: {$phone}\n";
    $message .= "Address: {$address}, {$city}, {$state} - {$pincode}\n";
    $message .= "Payment: {$payment_method}\n\n";
    $message .= "Please confirm my order.";
    
    $whatsapp_url = "https://wa.me/{$whatsapp_clean}?text=" . urlencode($message);
    
    echo json_encode([
        'success' => true,
        'whatsapp_url' => $whatsapp_url,
        'message' => 'Enquiry registered successfully!'
    ]);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to save enquiry: ' . $e->getMessage()]);
}
