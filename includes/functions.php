<?php
declare(strict_types=1);

require_once __DIR__ . '/database.php';

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function asset(string $path): string
{
    return BASE_URL . 'assets/' . ltrim($path, '/');
}

function money(float $amount): string
{
    return CURRENCY . number_format($amount, 2);
}

function page_title(string $title = ''): string
{
    $siteName = getSetting('website_name', 'ZetaStyle');
    return $title === '' ? $siteName : $title . ' | ' . $siteName;
}

function categories(): array
{
    $pdo = db();
    if ($pdo instanceof PDO) {
        try {
            $rows = $pdo->query('SELECT id, slug, name, image_url AS image FROM categories WHERE status = 1 ORDER BY display_order, id')->fetchAll();
            if ($rows !== []) {
                return $rows;
            }
        } catch (PDOException $exception) {
        }
    }

    return [
        ['id' => 1, 'slug' => 'men', 'name' => 'Men', 'image' => 'https://images.unsplash.com/photo-1516257984-b1b4d707412e?auto=format&fit=crop&w=900&q=85'],
        ['id' => 2, 'slug' => 'women', 'name' => 'Women', 'image' => 'https://images.unsplash.com/photo-1529139574466-a303027c1d8b?auto=format&fit=crop&w=900&q=85'],
        ['id' => 3, 'slug' => 'kids', 'name' => 'Kids', 'image' => 'https://images.unsplash.com/photo-1519238263530-99bdd11df2ea?auto=format&fit=crop&w=900&q=85'],
        ['id' => 4, 'slug' => 'oversized', 'name' => 'Oversized', 'image' => 'https://images.unsplash.com/photo-1503342217505-b0a15ec3261c?auto=format&fit=crop&w=900&q=85'],
        ['id' => 5, 'slug' => 'accessories', 'name' => 'Accessories', 'image' => 'https://images.unsplash.com/photo-1523170335258-f5ed11844a49?auto=format&fit=crop&w=900&q=85'],
    ];
}

function sub_categories(): array
{
    $pdo = db();
    if ($pdo instanceof PDO) {
        try {
            $rows = $pdo->query(
                'SELECT c.slug AS category, sc.slug, sc.name, sc.image_url AS image
                 FROM sub_categories sc
                 INNER JOIN categories c ON c.id = sc.category_id
                 WHERE sc.status = 1 AND c.status = 1
                 ORDER BY sc.id'
            )->fetchAll();
            if ($rows !== []) {
                return $rows;
            }
        } catch (PDOException $exception) {
        }
    }

    return [
        ['category' => 'men', 'slug' => 'men-premium-tees', 'name' => 'Premium Tees', 'image' => 'https://images.unsplash.com/photo-1523398002811-999ca8dec234?auto=format&fit=crop&w=900&q=85'],
        ['category' => 'men', 'slug' => 'men-polos', 'name' => 'Printed Polos', 'image' => 'https://images.unsplash.com/photo-1618354691373-d851c5c3a990?auto=format&fit=crop&w=900&q=85'],
        ['category' => 'men', 'slug' => 'men-hoodies', 'name' => 'Signature Hoodies', 'image' => 'https://images.unsplash.com/photo-1556821840-3a63f95609a7?auto=format&fit=crop&w=900&q=85'],
        ['category' => 'men', 'slug' => 'men-sweatshirts', 'name' => 'Sweatshirts', 'image' => 'https://images.unsplash.com/photo-1578587018452-892bacefd3f2?auto=format&fit=crop&w=900&q=85'],
        ['category' => 'men', 'slug' => 'men-activewear', 'name' => 'Activewear', 'image' => 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?auto=format&fit=crop&w=900&q=85'],
        ['category' => 'men', 'slug' => 'men-caps', 'name' => 'Caps', 'image' => 'https://images.unsplash.com/photo-1529958030586-3aae4ca485ff?auto=format&fit=crop&w=900&q=85'],
        ['category' => 'women', 'slug' => 'women-crop-tops', 'name' => 'Crop Tops', 'image' => 'https://images.unsplash.com/photo-1496747611176-843222e1e57c?auto=format&fit=crop&w=900&q=85'],
        ['category' => 'women', 'slug' => 'women-tees', 'name' => 'Soft Tees', 'image' => 'https://images.unsplash.com/photo-1509631179647-0177331693ae?auto=format&fit=crop&w=900&q=85'],
        ['category' => 'women', 'slug' => 'women-hoodies', 'name' => 'Luxe Hoodies', 'image' => 'https://images.unsplash.com/photo-1520975916090-3105956dac38?auto=format&fit=crop&w=900&q=85'],
        ['category' => 'women', 'slug' => 'women-totes', 'name' => 'Printed Totes', 'image' => 'https://images.unsplash.com/photo-1590874103328-eac38a683ce7?auto=format&fit=crop&w=900&q=85'],
        ['category' => 'women', 'slug' => 'women-athleisure', 'name' => 'Athleisure', 'image' => 'https://images.unsplash.com/photo-1506629905607-d405d7d3b0d2?auto=format&fit=crop&w=900&q=85'],
        ['category' => 'women', 'slug' => 'women-co-ords', 'name' => 'Co-ords', 'image' => 'https://images.unsplash.com/photo-1517841905240-472988babdf9?auto=format&fit=crop&w=900&q=85'],
        ['category' => 'kids', 'slug' => 'kids-tees', 'name' => 'Kids Tees', 'image' => 'https://images.unsplash.com/photo-1503919545889-aef636e10ad4?auto=format&fit=crop&w=900&q=85'],
        ['category' => 'kids', 'slug' => 'kids-hoodies', 'name' => 'Mini Hoodies', 'image' => 'https://images.unsplash.com/photo-1515488042361-ee00e0ddd4e4?auto=format&fit=crop&w=900&q=85'],
        ['category' => 'kids', 'slug' => 'kids-sets', 'name' => 'Matching Sets', 'image' => 'https://images.unsplash.com/photo-1503454537195-1dcabb73ffb9?auto=format&fit=crop&w=900&q=85'],
        ['category' => 'kids', 'slug' => 'kids-birthday', 'name' => 'Birthday Prints', 'image' => 'https://images.unsplash.com/photo-1542037104857-ffbb0b9155fb?auto=format&fit=crop&w=900&q=85'],
        ['category' => 'kids', 'slug' => 'kids-school', 'name' => 'School Clubs', 'image' => 'https://images.unsplash.com/photo-1503676382389-4809596d5290?auto=format&fit=crop&w=900&q=85'],
        ['category' => 'kids', 'slug' => 'kids-accessories', 'name' => 'Tiny Accessories', 'image' => 'https://images.unsplash.com/photo-1522771930-78848d9293e8?auto=format&fit=crop&w=900&q=85'],
    ];
}

function products(): array
{
    $pdo = db();
    if ($pdo instanceof PDO) {
        try {
            $rows = $pdo->query(
                'SELECT p.id, p.slug, p.name, c.slug AS category, p.current_price AS price,
                        p.old_price, p.discount_badge AS badge, p.product_tag AS tag,
                        p.image_url AS image
                 FROM products p
                 INNER JOIN categories c ON c.id = p.category_id
                 WHERE p.is_active = 1
                 ORDER BY p.id'
            )->fetchAll();
            if ($rows !== []) {
                return $rows;
            }
        } catch (PDOException $exception) {
        }
    }

    return [
        ['id' => 1, 'slug' => 'atelier-black-tee', 'name' => 'Atelier Black Custom Tee', 'category' => 'men', 'price' => 39, 'old_price' => 55, 'badge' => '29% OFF', 'tag' => 'trending', 'image' => 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?auto=format&fit=crop&w=900&q=85'],
        ['id' => 2, 'slug' => 'monogram-cream-hoodie', 'name' => 'Monogram Cream Hoodie', 'category' => 'women', 'price' => 74, 'old_price' => 98, 'badge' => 'NEW', 'tag' => 'new', 'image' => 'https://images.unsplash.com/photo-1554568218-0f1715e72254?auto=format&fit=crop&w=900&q=85'],
        ['id' => 3, 'slug' => 'oversized-studio-tee', 'name' => 'Oversized Studio Tee', 'category' => 'oversized', 'price' => 48, 'old_price' => 66, 'badge' => 'BEST', 'tag' => 'best', 'image' => 'https://images.unsplash.com/photo-1503341504253-dff4815485f1?auto=format&fit=crop&w=900&q=85'],
        ['id' => 4, 'slug' => 'mini-creator-tee', 'name' => 'Mini Creator Tee', 'category' => 'kids', 'price' => 28, 'old_price' => 36, 'badge' => '22% OFF', 'tag' => 'new', 'image' => 'https://images.unsplash.com/photo-1519457431-44ccd64a579b?auto=format&fit=crop&w=900&q=85'],
        ['id' => 5, 'slug' => 'signature-tote', 'name' => 'Signature Printed Tote', 'category' => 'accessories', 'price' => 31, 'old_price' => 42, 'badge' => 'HOT', 'tag' => 'trending', 'image' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=900&q=85'],
        ['id' => 6, 'slug' => 'club-polo', 'name' => 'Club Mark Polo', 'category' => 'men', 'price' => 52, 'old_price' => 70, 'badge' => '26% OFF', 'tag' => 'best', 'image' => 'https://images.unsplash.com/photo-1620012253295-c15cc3e65df4?auto=format&fit=crop&w=900&q=85'],
        ['id' => 7, 'slug' => 'woman-line-art-tee', 'name' => 'Line Art Relaxed Tee', 'category' => 'women', 'price' => 44, 'old_price' => 58, 'badge' => 'NEW', 'tag' => 'new', 'image' => 'https://images.unsplash.com/photo-1539008835657-9e8e9680c956?auto=format&fit=crop&w=900&q=85'],
        ['id' => 8, 'slug' => 'kids-varsity-hoodie', 'name' => 'Kids Varsity Hoodie', 'category' => 'kids', 'price' => 49, 'old_price' => 63, 'badge' => 'BEST', 'tag' => 'best', 'image' => 'https://images.unsplash.com/photo-1522771930-78848d9293e8?auto=format&fit=crop&w=900&q=85'],
        ['id' => 9, 'slug' => 'oversized-ash-sweatshirt', 'name' => 'Oversized Ash Sweatshirt', 'category' => 'oversized', 'price' => 68, 'old_price' => 86, 'badge' => '21% OFF', 'tag' => 'trending', 'image' => 'https://images.unsplash.com/photo-1618354691438-25bc04584c23?auto=format&fit=crop&w=900&q=85'],
        ['id' => 10, 'slug' => 'embroidered-cap', 'name' => 'Embroidered Minimal Cap', 'category' => 'accessories', 'price' => 34, 'old_price' => 44, 'badge' => 'NEW', 'tag' => 'new', 'image' => 'https://images.unsplash.com/photo-1588850561407-ed78c282e89b?auto=format&fit=crop&w=900&q=85'],
        ['id' => 11, 'slug' => 'premium-white-tee', 'name' => 'Premium White Print Tee', 'category' => 'men', 'price' => 42, 'old_price' => 56, 'badge' => '25% OFF', 'tag' => 'best', 'image' => 'https://images.unsplash.com/photo-1562157873-818bc0726f68?auto=format&fit=crop&w=900&q=85'],
        ['id' => 12, 'slug' => 'luxury-crop-top', 'name' => 'Luxury Crop Top', 'category' => 'women', 'price' => 38, 'old_price' => 50, 'badge' => 'HOT', 'tag' => 'trending', 'image' => 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?auto=format&fit=crop&w=900&q=85'],
    ];
}

function banners(): array
{
    $pdo = db();
    if ($pdo instanceof PDO) {
        try {
            $rows = $pdo->query(
                'SELECT title, subtitle, link_url AS href, image_url AS image
                 FROM banners
                 WHERE is_active = 1
                 ORDER BY sort_order, id'
            )->fetchAll();
            if ($rows !== []) {
                return $rows;
            }
        } catch (PDOException $exception) {
        }
    }

    return [
        ['title' => 'Custom Print Studio', 'subtitle' => 'Upload artwork, pick fabric, and wear your idea beautifully.', 'href' => 'shop.php', 'image' => 'https://images.unsplash.com/photo-1489987707025-afc232f7ea0f?auto=format&fit=crop&w=1500&q=90'],
        ['title' => 'Monochrome Essentials', 'subtitle' => 'Minimal silhouettes finished with premium personalization.', 'href' => 'category.php?category=oversized', 'image' => 'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?auto=format&fit=crop&w=1500&q=90'],
        ['title' => 'Family Print Edit', 'subtitle' => 'Coordinated pieces for kids, teams, celebrations, and everyday memories.', 'href' => 'category.php?category=kids', 'image' => 'https://images.unsplash.com/photo-1522771930-78848d9293e8?auto=format&fit=crop&w=1500&q=90'],
    ];
}

function blogs(): array
{
    $pdo = db();
    if ($pdo instanceof PDO) {
        try {
            $rows = $pdo->query(
                'SELECT slug, title, excerpt, DATE_FORMAT(published_at, "%M %e, %Y") AS date, image_url AS image
                 FROM blogs
                 WHERE status = "published"
                 ORDER BY published_at DESC'
            )->fetchAll();
            if ($rows !== []) {
                return $rows;
            }
        } catch (PDOException $exception) {
        }
    }

    return [
        ['slug' => 'choose-perfect-print-placement', 'title' => 'How to Choose the Perfect Print Placement', 'excerpt' => 'A premium garment starts with proportion, scale, and breathing room.', 'date' => 'June 4, 2026', 'image' => 'https://images.unsplash.com/photo-1452860606245-08befc0ff44b?auto=format&fit=crop&w=900&q=85'],
        ['slug' => 'cotton-vs-fleece', 'title' => 'Cotton vs. Fleece for Custom Apparel', 'excerpt' => 'A practical guide to matching fabric weight with design intent.', 'date' => 'May 24, 2026', 'image' => 'https://images.unsplash.com/photo-1445205170230-053b83016050?auto=format&fit=crop&w=900&q=85'],
        ['slug' => 'care-for-printed-clothing', 'title' => 'Care Tips for Long-Lasting Printed Clothing', 'excerpt' => 'Simple habits that keep colors crisp and silhouettes polished.', 'date' => 'May 12, 2026', 'image' => 'https://images.unsplash.com/photo-1558769132-cb1aea458c5e?auto=format&fit=crop&w=900&q=85'],
    ];
}

function get_product_by_slug(string $slug): ?array
{
    foreach (products() as $product) {
        if ($product['slug'] === $slug) {
            return $product;
        }
    }

    return null;
}

function filter_products(?string $category = null, ?string $tag = null): array
{
    return array_values(array_filter(products(), static function (array $product) use ($category, $tag): bool {
        if ($category !== null && $product['category'] !== $category) {
            return false;
        }

        if ($tag !== null && $product['tag'] !== $tag) {
            return false;
        }

        return true;
    }));
}

function render_product_card(array $product): void
{
    $hoverImage = $product['hover_image'] ?? $product['image'];
    ?>
    <article class="product-card reveal" data-product-card>
        <a class="product-media" href="product.php?slug=<?= e($product['slug']); ?>" aria-label="View <?= e($product['name']); ?>">
            <span class="badge"><?= e($product['badge']); ?></span>
            <img class="main-img" loading="lazy" src="<?= e($product['image']); ?>" alt="<?= e($product['name']); ?>">
            <img class="hover-img" loading="lazy" src="<?= e($hoverImage); ?>" alt="<?= e($product['name']); ?>">
        </a>
        <div class="product-body">
            <h3><?= e($product['name']); ?></h3>
            <div class="price-row">
                <strong><?= money((float) $product['price']); ?></strong>
                <span><?= money((float) $product['old_price']); ?></span>
            </div>
            <div class="product-actions">
                <a class="quick-view" href="product.php?slug=<?= e($product['slug']); ?>">Quick View</a>
                <button class="btn btn-dark add-to-cart"
                    data-id="<?= (int) $product['id']; ?>"
                    data-name="<?= e($product['name']); ?>"
                    data-price="<?= e((string) $product['price']); ?>"
                    data-image="<?= e($product['image']); ?>">Add to Cart</button>
            </div>
            <div style="margin-top: 10px;">
                <a class="btn btn-gold buy-whatsapp" style="width: 100%; text-align: center; justify-content: center; font-size: 0.8rem; padding: 10px 12px; display: inline-flex;"
                   href="https://wa.me/<?= preg_replace('/[^0-9]/', '', getSetting('whatsapp_number')); ?>?text=<?= urlencode('Hello ZetaStyle, I am interested in ' . $product['name'] . ' (' . money((float)$product['price']) . '). Please let me know its availability.'); ?>"
                   target="_blank" rel="noopener">Buy via WhatsApp</a>
            </div>
        </div>
    </article>
    <?php
}

function page_meta(string $title, string $description): array
{
    $defaultOgImage = getSetting('default_og_image');
    if ($defaultOgImage === '') {
        $defaultOgImage = 'https://images.unsplash.com/photo-1445205170230-053b83016050?auto=format&fit=crop&w=1200&q=90';
    }
    
    return [
        'title' => page_title($title),
        'description' => $description !== '' ? $description : getSetting('seo_description', 'Premium custom printed clothing store.'),
        'image' => $defaultOgImage,
    ];
}

function getSetting(string $key, string $default = ''): string
{
    static $settings = null;
    if ($settings === null) {
        $settings = [];
        $pdo = db();
        if ($pdo instanceof PDO) {
            try {
                $rows = $pdo->query('SELECT setting_key, setting_value FROM settings')->fetchAll();
                foreach ($rows as $row) {
                    $settings[$row['setting_key']] = $row['setting_value'];
                }
            } catch (PDOException $exception) {
                // Ignore and fall back to empty settings
            }
        }
    }
    return $settings[$key] ?? $default;
}

function get_social_links(): array
{
    $links = [];
    $platforms = [
        'Instagram' => 'instagram_url',
        'Facebook' => 'facebook_url',
        'YouTube' => 'youtube_url',
        'LinkedIn' => 'linkedin_url',
        'Twitter' => 'twitter_url',
    ];
    foreach ($platforms as $label => $setting_key) {
        $url = getSetting($setting_key);
        if ($url !== '') {
            $links[] = ['label' => $label, 'href' => $url];
        }
    }
    return $links;
}

if (!defined('SITE_NAME')) {
    define('SITE_NAME', getSetting('website_name', 'ZetaStyle'));
}
if (!defined('SITE_TAGLINE')) {
    define('SITE_TAGLINE', getSetting('brand_tagline', 'Custom printed clothing for elevated everyday wear'));
}


