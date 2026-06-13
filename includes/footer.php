    </main>
    <footer class="site-footer">
        <div class="container footer-grid">
            <section>
                <a class="brand footer-brand" href="index.php" style="display: flex; align-items: center; margin-bottom: 16px;">
                    <?php if ($logo = getSetting('logo')): ?>
                        <img src="<?= e($logo); ?>" alt="<?= e(getSetting('website_name', 'ZetaStyle')); ?>" style="max-height: 40px; width: auto; object-fit: contain;">
                    <?php else: ?>
                        <span><?= e(substr(getSetting('website_name', 'ZetaStyle'), 0, 4)); ?></span><?= e(substr(getSetting('website_name', 'ZetaStyle'), 4)); ?>
                    <?php endif; ?>
                </a>
                <p><?= e(getSetting('footer_description', 'Premium custom printed clothing designed for polished teams, creators, families, and everyday wardrobes.')); ?></p>
                <div class="social-row">
                    <?php foreach (get_social_links() as $link): ?>
                        <a href="<?= e($link['href']); ?>" target="_blank" rel="noopener"><?= e($link['label']); ?></a>
                    <?php endforeach; ?>
                </div>
            </section>
            <section>
                <h2>Quick Links</h2>
                <a href="shop.php">Shop</a>
                <a href="blog.php">Journal</a>
                <a href="about.php">About</a>
                <a href="contact.php">Contact</a>
            </section>
            <section>
                <h2>Customer Support</h2>
                <a href="track-order.php">Track Order</a>
                <a href="category.php?category=men">Men Collection</a>
                <a href="category.php?category=women">Women Collection</a>
                <a href="category.php?category=kids">Kids Collection</a>
            </section>
            <section>
                <h2>Newsletter</h2>
                <p><?= e(getSetting('newsletter_text', 'Receive fabric drops, print-care notes, and private collection previews.')); ?></p>
                <form class="newsletter-form">
                    <label class="sr-only" for="newsletter-email">Email address</label>
                    <input id="newsletter-email" type="email" placeholder="Email address" required>
                    <button class="btn btn-gold" type="submit">Join</button>
                </form>
            </section>
        </div>
        <div class="footer-bottom">
            <p><?= e(getSetting('copyright_text', '© ' . date('Y') . ' ' . getSetting('website_name', 'ZetaStyle') . '. All rights reserved.')); ?></p>
        </div>
    </footer>
    
    <!-- Floating WhatsApp Button -->
    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', getSetting('whatsapp_number')); ?>" class="whatsapp-float" target="_blank" rel="noopener" aria-label="Contact us on WhatsApp">
        <svg viewBox="0 0 24 24" width="30" height="30" fill="currentColor">
            <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.514 2.266 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.502-5.724-1.455L0 24zm6.59-4.846c1.6.95 3.488 1.449 5.407 1.451 5.437 0 9.858-4.403 9.86-9.809.001-2.62-1.01-5.084-2.853-6.93C17.218 1.95 14.764 1.93 12.01 1.93c-5.441 0-9.859 4.406-9.863 9.813-.001 1.921.506 3.8 1.468 5.419L2.6 21.03l3.966-1.039zm11.288-6.106c-.307-.154-1.82-.9-2.1-.1-.28.1-.482.498-.59.622-.109.124-.217.185-.524.03-.307-.154-1.299-.48-2.475-1.531-.915-.815-1.533-1.822-1.713-2.13-.18-.308-.02-.475.134-.628.14-.137.307-.358.462-.538.154-.18.205-.308.307-.514.102-.206.051-.386-.026-.54-.077-.154-.69-1.666-.945-2.28-.248-.599-.5-.519-.69-.529-.18-.009-.385-.01-.59-.01-.206 0-.54.077-.822.386-.282.308-1.078 1.054-1.078 2.572 0 1.517 1.102 2.985 1.256 3.19.154.206 2.17 3.313 5.258 4.646.734.317 1.309.507 1.758.649.737.234 1.41.201 1.94.122.59-.088 1.82-.746 2.077-1.47.256-.724.256-1.344.18-1.472-.077-.129-.283-.206-.59-.36z"/>
        </svg>
    </a>

    <div class="toast" data-toast role="status" aria-live="polite"></div>
    <script src="<?= asset('js/app.js'); ?>" defer></script>
    <script src="<?= asset('js/menu.js'); ?>" defer></script>
    <script src="<?= asset('js/slider.js'); ?>" defer></script>
    <script src="<?= asset('js/cart.js'); ?>" defer></script>
</body>
</html>
