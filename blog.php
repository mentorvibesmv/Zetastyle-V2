<?php
require_once __DIR__ . '/includes/functions.php';
$meta = page_meta('Journal', 'Read ZetaStyle notes on custom print placement, garment care, and premium apparel choices.');
require_once __DIR__ . '/includes/header.php';
?>
<section class="section">
    <div class="container blog-grid">
        <?php foreach (blogs() as $post): ?>
            <article class="blog-card reveal">
                <img loading="lazy" src="<?= e($post['image']); ?>" alt="<?= e($post['title']); ?>">
                <div>
                    <time><?= e($post['date']); ?></time>
                    <h2><?= e($post['title']); ?></h2>
                    <p><?= e($post['excerpt']); ?></p>
                    <a href="blog.php?post=<?= e($post['slug']); ?>">Read Article</a>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
