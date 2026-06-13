<?php
require_once __DIR__ . '/includes/functions.php';
$meta = page_meta('About Us', 'Learn about ZetaStyle, a premium custom printed clothing store focused on minimal fashion and refined print quality.');
require_once __DIR__ . '/includes/header.php';
?>
<section class="page-hero split-hero">
    <div class="container split-hero-grid">
        <div>
            <p class="eyebrow">Our studio</p>
            <h1>Custom clothing with restraint, clarity, and premium finish.</h1>
            <p>ZetaStyle was built for people who want personal apparel without noisy design. We pair modern silhouettes, white-space-led layouts, and durable print methods to create pieces that feel elevated.</p>
            <?php if ($insta = getSetting('instagram_url')): ?>
                <p style="margin-top: 15px;">Follow us on <a href="<?= e($insta); ?>" target="_blank" rel="noopener" style="color: var(--accent); font-weight: 600; text-decoration: underline;">Instagram</a> for behind-the-scenes print drops.</p>
            <?php endif; ?>
        </div>
        <img src="https://images.unsplash.com/photo-1441986300917-64674bd600d8?auto=format&fit=crop&w=1000&q=90" alt="Premium apparel studio interior">
    </div>
</section>
<section class="section">
    <div class="container value-grid">
        <article>
            <h2>Premium Base Garments</h2>
            <p>Every print starts on carefully selected cotton-rich tees, hoodies, totes, caps, and kidswear.</p>
        </article>
        <article>
            <h2>Clean Print Execution</h2>
            <p>Artwork is checked for scale, placement, contrast, and final production quality before print.</p>
        </article>
        <article>
            <h2>Made For Real Use</h2>
            <p>Our pieces are designed for repeated wear, event runs, gifting, launches, and everyday wardrobes.</p>
        </article>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
