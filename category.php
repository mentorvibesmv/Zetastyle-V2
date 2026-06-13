<?php
require_once __DIR__ . '/includes/functions.php';
$categorySlug = $_GET['category'] ?? 'men';
$category = null;
foreach (categories() as $item) {
    if ($item['slug'] === $categorySlug) {
        $category = $item;
        break;
    }
}
$category = $category ?? categories()[0];
$meta = page_meta($category['name'] . ' Collection', 'Shop premium custom printed ' . strtolower($category['name']) . ' clothing at ZetaStyle.');
require_once __DIR__ . '/includes/header.php';
?>
<section class="page-hero category-hero">
    <img src="<?= e($category['image']); ?>" alt="<?= e($category['name']); ?> collection">
    <div class="container">
        <p class="eyebrow">Collection</p>
        <h1><?= e($category['name']); ?></h1>
        <p>Polished custom print pieces selected for comfort, clarity, and everyday wear.</p>
    </div>
</section>
<section class="section">
    <div class="container">
        <div class="product-grid">
            <?php foreach (filter_products($category['slug']) as $product) { render_product_card($product); } ?>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
