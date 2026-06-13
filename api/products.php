<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

$pdo = db();
if (!$pdo instanceof PDO) {
    echo json_encode(['html' => '', 'count' => 0]);
    exit;
}

$search = trim((string)($_GET['search'] ?? ''));
$category = trim((string)($_GET['category'] ?? ''));
$sub_category = trim((string)($_GET['sub_category'] ?? ''));
$min_price = isset($_GET['min_price']) ? (float)$_GET['min_price'] : 0.0;
$max_price = isset($_GET['max_price']) ? (float)$_GET['max_price'] : 9999.0;
$in_stock = isset($_GET['in_stock']) ? (int)$_GET['in_stock'] : -1; // -1: all, 1: in stock, 0: out of stock

// Tags can come as a comma-separated list or array
$tagsInput = $_GET['tags'] ?? '';
$tags = is_array($tagsInput) ? $tagsInput : ($tagsInput !== '' ? explode(',', $tagsInput) : []);

$sort = trim((string)($_GET['sort'] ?? 'newest'));

$query = "SELECT p.*, c.slug AS category_slug, 
          (SELECT pi.image_url FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.sort_order, pi.id LIMIT 1) AS hover_image
          FROM products p
          INNER JOIN categories c ON c.id = p.category_id
          LEFT JOIN sub_categories sc ON sc.id = p.sub_category_id
          WHERE p.is_active = 1";
$params = [];

if ($search !== '') {
    $query .= " AND (p.name LIKE :search OR p.description LIKE :search OR p.sku LIKE :search)";
    $params[':search'] = "%{$search}%";
}

if ($category !== '') {
    $query .= " AND c.slug = :category";
    $params[':category'] = $category;
}

if ($sub_category !== '') {
    $query .= " AND sc.slug = :sub_category";
    $params[':sub_category'] = $sub_category;
}

if ($min_price > 0) {
    $query .= " AND p.current_price >= :min_price";
    $params[':min_price'] = $min_price;
}

if ($max_price < 9999) {
    $query .= " AND p.current_price <= :max_price";
    $params[':max_price'] = $max_price;
}

if ($in_stock === 1) {
    $query .= " AND p.stock > 0";
} elseif ($in_stock === 0) {
    $query .= " AND p.stock <= 0";
}

if (in_array('featured', $tags, true)) {
    $query .= " AND p.is_featured = 1";
}
if (in_array('trending', $tags, true)) {
    $query .= " AND p.is_trending = 1";
}
if (in_array('best', $tags, true)) {
    $query .= " AND p.is_best_seller = 1";
}
if (in_array('new', $tags, true)) {
    $query .= " AND p.is_new_arrival = 1";
}

// Sorting
switch ($sort) {
    case 'price_low_high':
        $query .= " ORDER BY p.current_price ASC";
        break;
    case 'price_high_low':
        $query .= " ORDER BY p.current_price DESC";
        break;
    case 'discount':
        $query .= " ORDER BY (p.old_price - p.current_price) DESC";
        break;
    case 'best_selling':
        $query .= " ORDER BY p.is_best_seller DESC, p.id DESC";
        break;
    case 'newest':
    default:
        $query .= " ORDER BY p.id DESC";
        break;
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll();

ob_start();
if (empty($products)) {
    echo '<div class="no-products" style="grid-column: 1/-1; text-align: center; padding: 48px; color: var(--muted);">No products match your filters. Try clearing filters.</div>';
} else {
    foreach ($products as $product) {
        $cardProduct = [
            'id' => $product['id'],
            'slug' => $product['slug'],
            'name' => $product['name'],
            'price' => $product['current_price'],
            'old_price' => $product['old_price'],
            'badge' => $product['discount_badge'],
            'image' => $product['image_url'],
            'hover_image' => $product['hover_image'] ?? $product['image_url'],
        ];
        render_product_card($cardProduct);
    }
}
$html = ob_get_clean();

echo json_encode([
    'html' => $html,
    'count' => count($products)
]);
