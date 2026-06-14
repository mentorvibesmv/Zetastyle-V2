<?php
require_once __DIR__ . '/includes/functions.php';
$meta = page_meta('Premium Custom Printed Clothing', getSetting('seo_description', 'Shop premium custom printed clothing, oversized tees, hoodies, kidswear, and accessories from ZetaStyle.'));
require_once __DIR__ . '/includes/header.php';

$cats = categories();
$subs = sub_categories();

// Define homepage sections configuration
$sections = [
    'categories' => ['enabled' => getSetting('section_categories_enabled', '1') === '1', 'order' => (int)getSetting('section_categories_order', '1')],
    'promo_slider' => ['enabled' => getSetting('section_promo_slider_enabled', '1') === '1', 'order' => (int)getSetting('section_promo_slider_order', '2')],
    'men' => ['enabled' => getSetting('section_men_enabled', '1') === '1', 'order' => (int)getSetting('section_men_order', '3')],
    'women' => ['enabled' => getSetting('section_women_enabled', '1') === '1', 'order' => (int)getSetting('section_women_order', '4')],
    'kids' => ['enabled' => getSetting('section_kids_enabled', '1') === '1', 'order' => (int)getSetting('section_kids_order', '5')],
    'trending' => ['enabled' => getSetting('section_trending_enabled', '1') === '1', 'order' => (int)getSetting('section_trending_order', '6')],
    'new_arrivals' => ['enabled' => getSetting('section_new_arrivals_enabled', '1') === '1', 'order' => (int)getSetting('section_new_arrivals_order', '7')],
    'best_sellers' => ['enabled' => getSetting('section_best_sellers_enabled', '1') === '1', 'order' => (int)getSetting('section_best_sellers_order', '8')],
    'contact_band' => ['enabled' => getSetting('section_contact_band_enabled', '1') === '1', 'order' => (int)getSetting('section_contact_band_order', '9')],
];

// Sort sections by display order setting
uasort($sections, function($a, $b) {
    return $a['order'] <=> $b['order'];
});
?>

<?php
// Render sections in dynamic order
foreach ($sections as $sectionKey => $sectionInfo) {
    if (!$sectionInfo['enabled']) {
        continue;
    }
    
    switch ($sectionKey) {
        case 'categories':
            ?>
            <section class="section">
                <div class="container">
                    <div class="section-heading">
                        <p class="eyebrow">Curated entry points</p>
                        <h2>Shop By Category</h2>
                    </div>
                    <div class="category-grid">
                        <?php foreach ($cats as $category): ?>
                            <a class="image-card reveal" href="category.php?category=<?= e($category['slug']); ?>">
                                <img loading="lazy" src="<?= e($category['image']); ?>" alt="<?= e($category['name']); ?> custom printed clothing">
                                <span><?= e($category['name']); ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
            <?php
            break;
            
        case 'promo_slider':
            ?>
            <section class="promo-slider section-tight" data-slider>
                <div class="slider-track" data-slider-track>
                    <?php foreach (banners() as $banner): ?>
                        <a class="promo-slide" href="<?= e($banner['href']); ?>">
                            <img loading="lazy" src="<?= e($banner['image']); ?>" alt="<?= e($banner['title']); ?>">
                            <span>
                                <small><?= e($banner['subtitle']); ?></small>
                                <strong><?= e($banner['title']); ?></strong>
                            </span>
                        </a>
                    <?php endforeach; ?>
                </div>
                <button class="slider-btn prev" type="button" data-slider-prev aria-label="Previous promotion">Prev</button>
                <button class="slider-btn next" type="button" data-slider-next aria-label="Next promotion">Next</button>
                <div class="slider-dots" data-slider-dots></div>
            </section>
            <?php
            break;
            
        case 'men':
            ?>
            <section class="section">
                <div class="container">
                    <div class="section-heading split">
                        <div>
                            <p class="eyebrow">Made for Men</p>
                            <h2>Men Collection</h2>
                        </div>
                        <a href="category.php?category=men">View all</a>
                    </div>
                    <div class="collection-grid">
                        <?php foreach (array_slice(array_values(array_filter($subs, fn($sub) => $sub['category'] === 'men')), 0, 6) as $sub): ?>
                            <article class="collection-card reveal">
                                <img loading="lazy" src="<?= e($sub['image']); ?>" alt="<?= e($sub['name']); ?>">
                                <div>
                                    <h3><?= e($sub['name']); ?></h3>
                                    <a class="btn btn-light" href="category.php?category=men">Shop Now</a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
            <?php
            break;
            
        case 'women':
            ?>
            <section class="section">
                <div class="container">
                    <div class="section-heading split">
                        <div>
                            <p class="eyebrow">Made for Women</p>
                            <h2>Women Collection</h2>
                        </div>
                        <a href="category.php?category=women">View all</a>
                    </div>
                    <div class="collection-grid">
                        <?php foreach (array_slice(array_values(array_filter($subs, fn($sub) => $sub['category'] === 'women')), 0, 6) as $sub): ?>
                            <article class="collection-card reveal">
                                <img loading="lazy" src="<?= e($sub['image']); ?>" alt="<?= e($sub['name']); ?>">
                                <div>
                                    <h3><?= e($sub['name']); ?></h3>
                                    <a class="btn btn-light" href="category.php?category=women">Shop Now</a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
            <?php
            break;
            
        case 'kids':
            ?>
            <section class="section">
                <div class="container">
                    <div class="section-heading split">
                        <div>
                            <p class="eyebrow">Made for Kids</p>
                            <h2>Kids Collection</h2>
                        </div>
                        <a href="category.php?category=kids">View all</a>
                    </div>
                    <div class="collection-grid">
                        <?php foreach (array_slice(array_values(array_filter($subs, fn($sub) => $sub['category'] === 'kids')), 0, 6) as $sub): ?>
                            <article class="collection-card reveal">
                                <img loading="lazy" src="<?= e($sub['image']); ?>" alt="<?= e($sub['name']); ?>">
                                <div>
                                    <h3><?= e($sub['name']); ?></h3>
                                    <a class="btn btn-light" href="category.php?category=kids">Shop Now</a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
            <?php
            break;
            
        case 'trending':
            ?>
            <section class="section">
                <div class="container">
                    <div class="section-heading">
                        <p class="eyebrow">Most watched this week</p>
                        <h2>Trending Products</h2>
                    </div>
                    <div class="product-slider">
                        <?php 
                        $trendingProducts = filter_products(null, 'trending');
                        foreach ($trendingProducts as $product) {
                            render_product_card($product);
                        } 
                        ?>
                    </div>
                </div>
            </section>
            <?php
            break;
            
        case 'new_arrivals':
            ?>
            <section class="section muted">
                <div class="container">
                    <div class="section-heading">
                        <p class="eyebrow">Fresh from the studio</p>
                        <h2>New Arrivals</h2>
                    </div>
                    <div class="product-grid">
                        <?php 
                        $newProducts = array_slice(filter_products(null, 'new'), 0, 6);
                        foreach ($newProducts as $product) {
                            render_product_card($product);
                        } 
                        ?>
                    </div>
                </div>
            </section>
            <?php
            break;
            
        case 'best_sellers':
            ?>
            <section class="section">
                <div class="container">
                    <div class="section-heading">
                        <p class="eyebrow">Customer favorites</p>
                        <h2>Best Selling Products</h2>
                    </div>
                    <div class="product-slider">
                        <?php 
                        $bestProducts = filter_products(null, 'best');
                        foreach ($bestProducts as $product) {
                            render_product_card($product);
                        } 
                        ?>
                    </div>
                </div>
            </section>
            <?php
            break;
            
        case 'contact_band':
            ?>
            <section class="contact-band">
                <div class="container contact-band-inner">
                    <div>
                        <p class="eyebrow">Custom work</p>
                        <h2>Bring your print idea to fabric.</h2>
                    </div>
                    <a class="btn btn-dark" href="contact.php">Request a Quote</a>
                </div>
            </section>
            <?php
            break;
    }
}
?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
