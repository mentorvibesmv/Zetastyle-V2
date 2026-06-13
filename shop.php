<?php
require_once __DIR__ . '/includes/functions.php';
$meta = page_meta('Shop', 'Browse premium custom printed clothing across men, women, kids, oversized apparel, and accessories.');
$selected = $_GET['category'] ?? '';

// Initially load all active products
$pdo = db();
$products = [];
if ($pdo instanceof PDO) {
    $q = "SELECT p.*, c.slug AS category_slug, 
          (SELECT pi.image_url FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.sort_order, pi.id LIMIT 1) AS hover_image
          FROM products p
          INNER JOIN categories c ON c.id = p.category_id
          WHERE p.is_active = 1";
    $params = [];
    if ($selected !== '') {
        $q .= " AND c.slug = :cat";
        $params[':cat'] = $selected;
    }
    $q .= " ORDER BY p.id DESC";
    $stmt = $pdo->prepare($q);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
}

require_once __DIR__ . '/includes/header.php';
?>
<section class="page-hero compact">
    <div class="container">
        <p class="eyebrow">Catalog</p>
        <h1>Shop ZetaStyle</h1>
        <p>Custom printed essentials with premium fabric, refined silhouettes, and made-to-order print quality.</p>
    </div>
</section>

<section class="section">
    <div class="container shop-layout">
        <aside class="filter-panel-sticky">
            <form id="filter-form" onsubmit="return false;">
                <div class="filter-group">
                    <label for="search-input">Search Products</label>
                    <input type="text" id="search-input" name="search" placeholder="Search by name, SKU...">
                </div>

                <div class="filter-group">
                    <label for="category-select">Category</label>
                    <select id="category-select" name="category">
                        <option value="">All Categories</option>
                        <?php foreach (categories() as $cat): ?>
                            <option value="<?= e($cat['slug']); ?>" <?= ($selected === $cat['slug']) ? 'selected' : ''; ?>><?= e($cat['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="subcategory-select">Sub Category</label>
                    <select id="subcategory-select" name="sub_category">
                        <option value="">All Sub Categories</option>
                        <?php foreach (sub_categories() as $sub): ?>
                            <option value="<?= e($sub['slug']); ?>" data-category="<?= e($sub['category']); ?>"><?= e($sub['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label>Price Range</label>
                    <div class="price-slider-wrap">
                        <input type="range" id="price-slider" name="max_price" min="0" max="200" value="200" step="5">
                        <div class="price-val">Max: <span id="price-val-label">$200</span></div>
                    </div>
                </div>

                <div class="filter-group">
                    <label>Availability</label>
                    <label class="check-label"><input type="checkbox" name="in_stock" value="1"> In Stock</label>
                    <label class="check-label"><input type="checkbox" name="out_of_stock" value="1"> Out of Stock</label>
                </div>

                <div class="filter-group">
                    <label>Tags</label>
                    <label class="check-label"><input type="checkbox" name="tags" value="featured"> Featured</label>
                    <label class="check-label"><input type="checkbox" name="tags" value="trending"> Trending</label>
                    <label class="check-label"><input type="checkbox" name="tags" value="best"> Best Seller</label>
                    <label class="check-label"><input type="checkbox" name="tags" value="new"> New Arrival</label>
                </div>

                <div class="filter-group">
                    <label for="sort-select">Sort By</label>
                    <select id="sort-select" name="sort">
                        <option value="newest">Newest</option>
                        <option value="price_low_high">Price: Low to High</option>
                        <option value="price_high_low">Price: High to Low</option>
                        <option value="discount">Discount</option>
                        <option value="best_selling">Best Selling</option>
                    </select>
                </div>

                <div class="filter-actions" style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" class="btn btn-dark" style="flex: 1; padding: 10px; font-size: 0.85rem; min-height: auto;">Apply</button>
                    <button type="button" id="clear-filters-btn" class="btn btn-light" style="flex: 1; padding: 10px; font-size: 0.85rem; min-height: auto;">Clear</button>
                </div>
            </form>
        </aside>

        <div class="shop-products-container">
            <div class="product-grid shop-products-grid" id="products-target">
                <?php 
                if (empty($products)) {
                    echo '<div class="no-products" style="grid-column: 1/-1; text-align: center; padding: 48px; color: var(--muted);">No products found.</div>';
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
                ?>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const filterForm = document.getElementById('filter-form');
    const productsTarget = document.getElementById('products-target');
    const categorySelect = document.getElementById('category-select');
    const subcategorySelect = document.getElementById('subcategory-select');
    const priceSlider = document.getElementById('price-slider');
    const priceValLabel = document.getElementById('price-val-label');
    const clearBtn = document.getElementById('clear-filters-btn');

    // Update price label on slide
    priceSlider.addEventListener('input', () => {
        priceValLabel.textContent = `$${priceSlider.value}`;
    });

    // Subcategory filtering based on Category
    function updateSubcategories() {
        const selectedCat = categorySelect.value;
        const options = subcategorySelect.querySelectorAll('option');
        options.forEach(opt => {
            if (opt.value === '') return;
            const parentCat = opt.dataset.category;
            if (selectedCat === '' || parentCat === selectedCat) {
                opt.style.display = '';
                opt.disabled = false;
            } else {
                opt.style.display = 'none';
                opt.disabled = true;
            }
        });
        // Reset subcategory if it's now hidden
        const selectedOpt = subcategorySelect.options[subcategorySelect.selectedIndex];
        if (selectedOpt && selectedOpt.disabled) {
            subcategorySelect.value = '';
        }
    }
    categorySelect.addEventListener('change', updateSubcategories);
    updateSubcategories();

    // Fetch filtered products
    async function applyFilters() {
        const formData = new FormData(filterForm);
        const params = new URLSearchParams();

        if (formData.get('search')) params.append('search', formData.get('search'));
        if (formData.get('category')) params.append('category', formData.get('category'));
        if (formData.get('sub_category')) params.append('sub_category', formData.get('sub_category'));
        if (formData.get('max_price')) params.append('max_price', formData.get('max_price'));
        
        // Availability
        const inStockChecked = filterForm.elements['in_stock'].checked;
        const outOfStockChecked = filterForm.elements['out_of_stock'].checked;
        if (inStockChecked && !outOfStockChecked) {
            params.append('in_stock', '1');
        } else if (!inStockChecked && outOfStockChecked) {
            params.append('in_stock', '0');
        }

        // Tags
        const tags = [];
        filterForm.querySelectorAll('input[name="tags"]:checked').forEach(cb => {
            tags.push(cb.value);
        });
        if (tags.length > 0) {
            params.append('tags', tags.join(','));
        }

        if (formData.get('sort')) params.append('sort', formData.get('sort'));

        productsTarget.style.opacity = '0.5';
        try {
            const res = await fetch(`api/products.php?${params.toString()}`);
            const data = await res.json();
            productsTarget.innerHTML = data.html;
            
            // Rebind add to cart buttons after DOM updates
            if (window.bindAddButtons) {
                window.bindAddButtons();
            }
        } catch (e) {
            console.error('Failed to load products', e);
        } finally {
            productsTarget.style.opacity = '1';
        }
    }

    filterForm.addEventListener('submit', (e) => {
        e.preventDefault();
        applyFilters();
    });

    // Auto-apply on changes for instant filter UX
    filterForm.querySelectorAll('select, input[type="checkbox"], input[type="range"]').forEach(el => {
        el.addEventListener('change', applyFilters);
    });

    // Clear filters
    clearBtn.addEventListener('click', () => {
        filterForm.reset();
        priceValLabel.textContent = `$${priceSlider.value}`;
        updateSubcategories();
        applyFilters();
    });
});
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
