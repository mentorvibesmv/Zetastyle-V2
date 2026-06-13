<?php
require_once __DIR__ . '/includes/functions.php';
$meta = page_meta('Page Not Found', 'The requested ZetaStyle page could not be found.');
require_once __DIR__ . '/includes/header.php';
?>
<section class="page-hero compact">
    <div class="container">
        <p class="eyebrow">404</p>
        <h1>That page slipped out of the collection.</h1>
        <p>The link may be outdated, but the shop is ready.</p>
        <a class="btn btn-dark" href="shop.php">Return to Shop</a>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
