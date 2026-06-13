<?php
require_once __DIR__ . '/includes/functions.php';
$meta = page_meta('Contact Us', 'Contact ZetaStyle for custom printed clothing orders, quotes, and support.');
require_once __DIR__ . '/includes/header.php';
?>
<section class="page-hero compact">
    <div class="container">
        <p class="eyebrow">Contact</p>
        <h1>Start a custom order.</h1>
        <p>Share your garment type, print idea, quantity, and timeline. Our studio will respond with next steps.</p>
    </div>
</section>
<section class="section">
    <div class="container contact-grid">
        <form class="contact-form reveal" action="api/contact.php" method="post" data-contact-form>
            <div class="form-row">
                <label for="name">Name</label>
                <input id="name" name="name" type="text" autocomplete="name" required minlength="2">
            </div>
            <div class="form-row">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" autocomplete="email" required>
            </div>
            <div class="form-row">
                <label for="phone">Phone</label>
                <input id="phone" name="phone" type="tel" autocomplete="tel" required pattern="[0-9+\-\s()]{7,20}">
            </div>
            <div class="form-row">
                <label for="message">Message</label>
                <textarea id="message" name="message" rows="6" required minlength="10"></textarea>
            </div>
            <button class="btn btn-dark" type="submit">Send Message</button>
            <p class="form-status" data-form-status></p>
        </form>
        <aside class="contact-panel reveal">
            <h2>Studio Details</h2>
            <p>Email: <?= e(getSetting('contact_email', 'care@zetastyle.test')); ?></p>
            <?php if ($support = getSetting('support_email')): ?>
                <p>Support Email: <?= e($support); ?></p>
            <?php endif; ?>
            <p>Phone: <?= e(getSetting('contact_phone', '+1 555 0199')); ?></p>
            <?php if ($secondary = getSetting('secondary_phone')): ?>
                <p>Alt Phone: <?= e($secondary); ?></p>
            <?php endif; ?>
            <?php if ($whatsapp = getSetting('whatsapp_number')): ?>
                <p>WhatsApp: <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $whatsapp); ?>" target="_blank" rel="noopener" style="color: var(--accent); font-weight: 600;">Message Us</a></p>
            <?php endif; ?>
            <p>Address: <?= e(getSetting('address')); ?>, <?= e(getSetting('city')); ?>, <?= e(getSetting('state')); ?> - <?= e(getSetting('pincode')); ?></p>
            <p>Hours: Monday-Friday, 9:00 AM-6:00 PM</p>
            <?php if ($mapUrl = getSetting('google_map_url')): ?>
                <p style="margin-top: 15px;"><a href="<?= e($mapUrl); ?>" target="_blank" rel="noopener" style="color: var(--accent); font-weight: 600; text-decoration: underline;">View on Google Maps</a></p>
            <?php endif; ?>
            <?php if ($insta = getSetting('instagram_url')): ?>
                <p>Instagram: <a href="<?= e($insta); ?>" target="_blank" rel="noopener" style="color: var(--accent); font-weight: 600;">@zetastyle</a></p>
            <?php endif; ?>
        </aside>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
