<?php
require_once __DIR__ . '/includes/functions.php';
$slug = $_GET['slug'] ?? '';
$product = get_product_by_slug($slug);
if (!$product) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}
$meta = page_meta($product['name'], 'View ' . $product['name'] . ' from ZetaStyle custom printed clothing.');
require_once __DIR__ . '/includes/header.php';
?>
<section class="section product-detail">
    <div class="container product-detail-grid">
        <div class="detail-media reveal">
            <span class="badge"><?= e($product['badge']); ?></span>
            <img src="<?= e($product['image']); ?>" alt="<?= e($product['name']); ?>">
        </div>
        <div class="detail-copy reveal">
            <p class="eyebrow">Made to order</p>
            <h1><?= e($product['name']); ?></h1>
            <div class="price-row large">
                <strong><?= money((float) $product['price']); ?></strong>
                <span><?= money((float) $product['old_price']); ?></span>
            </div>
            <p>Cut from soft premium fabric and finished with crisp custom printing. Ideal for brand drops, events, personal artwork, and refined everyday styling.</p>
            <ul class="check-list">
                <li>Premium cotton-rich fabric</li>
                <li>High-resolution print finish</li>
                <li>Made in small batches for quality control</li>
                <li>Dispatch estimate: 3-5 business days</li>
            </ul>
            <button class="btn btn-dark add-to-cart"
                data-id="<?= (int) $product['id']; ?>"
                data-name="<?= e($product['name']); ?>"
                data-price="<?= e((string) $product['price']); ?>"
                data-image="<?= e($product['image']); ?>">Add to Cart</button>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
