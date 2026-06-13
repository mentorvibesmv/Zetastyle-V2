<?php
require_once __DIR__ . '/functions.php';

$meta = $meta ?? page_meta('', SITE_TAGLINE);
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= e($meta['description']); ?>">
    <meta name="theme-color" content="#111111">
    <meta property="og:title" content="<?= e($meta['title']); ?>">
    <meta property="og:description" content="<?= e($meta['description']); ?>">
    <meta property="og:image" content="<?= e($meta['image']); ?>">
    <meta property="og:type" content="website">
    <title><?= e($meta['title']); ?></title>
    <?php if ($favicon = getSetting('favicon')): ?>
        <link rel="icon" href="<?= e($favicon); ?>" type="image/x-icon">
    <?php endif; ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/style.css'); ?>">
    <link rel="stylesheet" href="<?= asset('css/animations.css'); ?>">
    <link rel="stylesheet" href="<?= asset('css/responsive.css'); ?>">
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "ClothingStore",
        "name": "<?= e(getSetting('website_name', 'ZetaStyle')); ?>",
        "description": "<?= e(getSetting('seo_description', 'Custom printed clothing store for premium tees, hoodies, kidswear, and accessories')); ?>",
        "url": "<?= e(BASE_URL ?: '/'); ?>"
    }
    </script>
</head>
<body>
    <a class="skip-link" href="#main-content">Skip to content</a>
    <?php if (getSetting('announcement_enable', '1') === '1'): ?>
        <div class="announcement-bar" style="background-color: <?= e(getSetting('announcement_bg_color', '#111111')); ?>; color: <?= e(getSetting('announcement_text_color', '#ffffff')); ?>;">
            <?= e(getSetting('announcement_text', 'Free premium packaging on custom print orders over $99')); ?>
        </div>
    <?php endif; ?>
    <header class="site-header" data-header>
        <div class="container header-inner">
            <a class="brand" href="index.php" aria-label="<?= e(getSetting('website_name', 'ZetaStyle')); ?> home" style="display: flex; align-items: center;">
                <?php if ($logo = getSetting('logo')): ?>
                    <img src="<?= e($logo); ?>" alt="<?= e(getSetting('website_name', 'ZetaStyle')); ?>" style="max-height: 48px; width: auto; object-fit: contain;">
                <?php else: ?>
                    <span><?= e(substr(getSetting('website_name', 'ZetaStyle'), 0, 4)); ?></span><?= e(substr(getSetting('website_name', 'ZetaStyle'), 4)); ?>
                <?php endif; ?>
            </a>
            <nav class="desktop-nav" aria-label="Primary navigation">
                <?php foreach ($navItems as $item): ?>
                    <a href="<?= e($item['href']); ?>" class="<?= $currentPage === basename($item['href']) ? 'active' : ''; ?>">
                        <?= e($item['label']); ?>
                    </a>
                <?php endforeach; ?>
            </nav>
            <div class="header-actions">
                <a class="cart-link" href="cart.php" aria-label="Cart">
                    Cart <span data-cart-count>0</span>
                </a>
                <button class="menu-toggle" type="button" aria-label="Open menu" aria-expanded="false" data-menu-toggle>
                    <span></span><span></span><span></span>
                </button>
            </div>
        </div>
        <nav class="mobile-nav" aria-label="Mobile navigation" data-mobile-nav>
            <?php foreach ($navItems as $item): ?>
                <a href="<?= e($item['href']); ?>"><?= e($item['label']); ?></a>
            <?php endforeach; ?>
            <a href="cart.php">Cart</a>
        </nav>
    </header>
    <main id="main-content">
