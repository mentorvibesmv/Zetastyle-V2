<?php
require_once __DIR__ . '/../bootstrap.php';
require_admin();
$pdo = admin_db();
$adminTitle = 'Settings';

$defaults = [
    'website_name' => 'ZetaStyle',
    'brand_tagline' => 'Custom printed clothing for elevated everyday wear',
    'logo' => '',
    'favicon' => '',
    'default_og_image' => '',
    'contact_phone' => '+1 555 0199',
    'secondary_phone' => '+1 555 0198',
    'whatsapp_number' => '+1 555 0199',
    'contact_email' => 'care@zetastyle.test',
    'support_email' => 'support@zetastyle.test',
    'address' => '42 Studio Lane',
    'city' => 'New York',
    'state' => 'NY',
    'pincode' => '10001',
    'google_map_url' => 'https://maps.google.com',
    'instagram_url' => 'https://instagram.com',
    'facebook_url' => 'https://facebook.com',
    'youtube_url' => 'https://youtube.com',
    'linkedin_url' => 'https://linkedin.com',
    'twitter_url' => 'https://twitter.com',
    'shipping_tn' => '50.00',
    'shipping_other' => '100.00',
    'cod_charge' => '40.00',
    'seo_title' => 'ZetaStyle - Premium Custom Clothing',
    'seo_description' => 'Shop premium custom printed clothing from ZetaStyle.',
    'seo_keywords' => 'custom print, clothing, t-shirts, hoodies',
    'footer_description' => 'Premium custom printed clothing designed for polished teams, creators, families, and everyday wardrobes.',
    'copyright_text' => '© 2026 ZetaStyle. All rights reserved.',
    'newsletter_text' => 'Receive fabric drops, print-care notes, and private collection previews.',
    'announcement_enable' => '1',
    'announcement_text' => 'Free premium packaging on custom print orders over $99',
    'announcement_bg_color' => '#111111',
    'announcement_text_color' => '#ffffff',
    
    // Homepage sections defaults
    'section_categories_enabled' => '1',
    'section_categories_order' => '1',
    'section_promo_slider_enabled' => '1',
    'section_promo_slider_order' => '2',
    'section_men_enabled' => '1',
    'section_men_order' => '3',
    'section_women_enabled' => '1',
    'section_women_order' => '4',
    'section_kids_enabled' => '1',
    'section_kids_order' => '5',
    'section_trending_enabled' => '1',
    'section_trending_order' => '6',
    'section_new_arrivals_enabled' => '1',
    'section_new_arrivals_order' => '7',
    'section_best_sellers_enabled' => '1',
    'section_best_sellers_order' => '8',
    'section_contact_band_enabled' => '1',
    'section_contact_band_order' => '9',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    
    // Uploads
    $logo = upload_admin_image('logo', 'settings') ?? ($_POST['logo_current'] ?? '');
    $favicon = upload_admin_image('favicon', 'settings') ?? ($_POST['favicon_current'] ?? '');
    $og_image = upload_admin_image('default_og_image', 'settings') ?? ($_POST['default_og_image_current'] ?? '');
    
    foreach ($defaults as $key => $value) {
        if ($key === 'logo') {
            $val = $logo;
        } elseif ($key === 'favicon') {
            $val = $favicon;
        } elseif ($key === 'default_og_image') {
            $val = $og_image;
        } elseif (str_ends_with($key, '_enabled')) {
            // Checkboxes: if not set in $_POST, store '0'
            $val = isset($_POST[$key]) ? '1' : '0';
        } else {
            $val = $_POST[$key] ?? '';
        }
        
        $stmt = $pdo->prepare('INSERT INTO settings (`setting_key`,`setting_value`) VALUES (:k,:v) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)');
        $stmt->execute([':k' => $key, ':v' => $val]);
    }
    
    log_activity('updated_settings', 'settings');
    redirect_with(admin_url('settings/'), 'Settings saved successfully.');
}

// Load current settings
$settings = $defaults;
foreach ($pdo->query('SELECT setting_key, setting_value FROM settings') as $row) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

require_once __DIR__ . '/../header.php';
?>
<style>
    .tab-btn {
        padding: 12px 18px;
        background: none;
        border: none;
        border-bottom: 2px solid transparent;
        cursor: pointer;
        font-weight: 600;
        color: var(--muted);
        transition: all 0.25s ease;
    }
    .tab-btn.active {
        color: var(--accent);
        border-bottom-color: var(--accent);
    }
    .tab-content {
        display: none;
    }
    .tab-content.active {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }
    .tab-content.active .wide {
        grid-column: 1 / -1;
    }
    .settings-section-title {
        grid-column: 1 / -1;
        border-bottom: 1px solid var(--border);
        padding-bottom: 8px;
        margin-top: 15px;
        font-family: 'Playfair Display', serif;
        font-size: 1.3rem;
    }
    .section-row {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 10px;
        border: 1px solid var(--border);
        border-radius: 8px;
        background: var(--background);
    }
</style>

<section class="panel">
    <div class="panel-head">
        <h2>Website CMS Configuration</h2>
    </div>
    
    <!-- Tab Controls -->
    <div class="admin-tabs" style="display: flex; flex-wrap: wrap; gap: 5px; border-bottom: 1px solid var(--border); margin-bottom: 24px;">
        <button type="button" class="tab-btn active" data-tab="general">General</button>
        <button type="button" class="tab-btn" data-tab="branding">Branding</button>
        <button type="button" class="tab-btn" data-tab="contact">Contact</button>
        <button type="button" class="tab-btn" data-tab="social">Social Media</button>
        <button type="button" class="tab-btn" data-tab="shipping">Shipping</button>
        <button type="button" class="tab-btn" data-tab="seo">SEO</button>
        <button type="button" class="tab-btn" data-tab="footer">Footer</button>
        <button type="button" class="tab-btn" data-tab="announcements">Announcements</button>
        <button type="button" class="tab-btn" data-tab="homepage">Homepage Layout</button>
    </div>
    
    <form class="admin-form product-form" method="post" enctype="multipart/form-data" style="margin-top: 0;">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">
        <input type="hidden" name="logo_current" value="<?= e($settings['logo']); ?>">
        <input type="hidden" name="favicon_current" value="<?= e($settings['favicon']); ?>">
        <input type="hidden" name="default_og_image_current" value="<?= e($settings['default_og_image']); ?>">
        
        <!-- GENERAL TAB -->
        <div id="tab-general" class="tab-content active">
            <label class="wide">Website Name<input name="website_name" value="<?= e($settings['website_name']); ?>" required></label>
            <label class="wide">Brand Tagline<input name="brand_tagline" value="<?= e($settings['brand_tagline']); ?>"></label>
        </div>
        
        <!-- BRANDING TAB -->
        <div id="tab-branding" class="tab-content">
            <label>Logo File
                <input type="file" name="logo" accept="image/*">
                <?php if ($settings['logo']): ?>
                    <img src="<?= e(public_image($settings['logo'])); ?>" alt="Logo Preview" style="max-height: 50px; margin-top: 10px; display: block;">
                <?php endif; ?>
            </label>
            <label>Favicon File
                <input type="file" name="favicon" accept="image/*">
                <?php if ($settings['favicon']): ?>
                    <img src="<?= e(public_image($settings['favicon'])); ?>" alt="Favicon Preview" style="max-height: 32px; margin-top: 10px; display: block;">
                <?php endif; ?>
            </label>
        </div>
        
        <!-- CONTACT TAB -->
        <div id="tab-contact" class="tab-content">
            <label>Primary Phone<input name="contact_phone" value="<?= e($settings['contact_phone']); ?>"></label>
            <label>Secondary Phone<input name="secondary_phone" value="<?= e($settings['secondary_phone']); ?>"></label>
            <label>WhatsApp Number<input name="whatsapp_number" value="<?= e($settings['whatsapp_number']); ?>"></label>
            <label>Contact Email<input name="contact_email" value="<?= e($settings['contact_email']); ?>"></label>
            <label>Support Email<input name="support_email" value="<?= e($settings['support_email']); ?>"></label>
            <label class="wide">Address<textarea name="address" rows="2"><?= e($settings['address']); ?></textarea></label>
            <label>City<input name="city" value="<?= e($settings['city']); ?>"></label>
            <label>State<input name="state" value="<?= e($settings['state']); ?>"></label>
            <label>Pincode<input name="pincode" value="<?= e($settings['pincode']); ?>"></label>
            <label class="wide">Google Map URL<input name="google_map_url" value="<?= e($settings['google_map_url']); ?>"></label>
        </div>
        
        <!-- SOCIAL MEDIA TAB -->
        <div id="tab-social" class="tab-content">
            <label>Instagram URL<input name="instagram_url" value="<?= e($settings['instagram_url']); ?>"></label>
            <label>Facebook URL<input name="facebook_url" value="<?= e($settings['facebook_url']); ?>"></label>
            <label>YouTube URL<input name="youtube_url" value="<?= e($settings['youtube_url']); ?>"></label>
            <label>LinkedIn URL<input name="linkedin_url" value="<?= e($settings['linkedin_url']); ?>"></label>
            <label>Twitter/X URL<input name="twitter_url" value="<?= e($settings['twitter_url']); ?>"></label>
        </div>
        
        <!-- SHIPPING TAB -->
        <div id="tab-shipping" class="tab-content">
            <label>Tamil Nadu Shipping Charge<input type="number" step="0.01" name="shipping_tn" value="<?= e($settings['shipping_tn']); ?>"></label>
            <label>Other State Shipping Charge<input type="number" step="0.01" name="shipping_other" value="<?= e($settings['shipping_other']); ?>"></label>
            <label>COD Charge<input type="number" step="0.01" name="cod_charge" value="<?= e($settings['cod_charge']); ?>"></label>
        </div>
        
        <!-- SEO TAB -->
        <div id="tab-seo" class="tab-content">
            <label class="wide">Homepage Meta Title<input name="seo_title" value="<?= e($settings['seo_title']); ?>"></label>
            <label class="wide">Homepage Meta Description<textarea name="seo_description" rows="2"><?= e($settings['seo_description']); ?></textarea></label>
            <label class="wide">Homepage Keywords<input name="seo_keywords" value="<?= e($settings['seo_keywords']); ?>"></label>
            <label class="wide">Default OG Image
                <input type="file" name="default_og_image" accept="image/*">
                <?php if ($settings['default_og_image']): ?>
                    <img src="<?= e(public_image($settings['default_og_image'])); ?>" alt="OG Preview" style="max-height: 100px; margin-top: 10px; display: block;">
                <?php endif; ?>
            </label>
        </div>
        
        <!-- FOOTER TAB -->
        <div id="tab-footer" class="tab-content">
            <label class="wide">Footer Description<textarea name="footer_description" rows="2"><?= e($settings['footer_description']); ?></textarea></label>
            <label class="wide">Copyright Text<input name="copyright_text" value="<?= e($settings['copyright_text']); ?>"></label>
            <label class="wide">Newsletter Text<input name="newsletter_text" value="<?= e($settings['newsletter_text']); ?>"></label>
        </div>
        
        <!-- ANNOUNCEMENTS TAB -->
        <div id="tab-announcements" class="tab-content">
            <div class="wide" style="display: flex; gap: 20px; align-items: center;">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; width: auto; font-weight: 500;">
                    <input type="checkbox" name="announcement_enable" value="1" <?= $settings['announcement_enable'] === '1' ? 'checked' : ''; ?> style="width: 18px; height: 18px;">
                    Enable Announcement Bar
                </label>
            </div>
            <label class="wide">Announcement Text<input name="announcement_text" value="<?= e($settings['announcement_text']); ?>"></label>
            <label>Background Color<input type="color" name="announcement_bg_color" value="<?= e($settings['announcement_bg_color']); ?>" style="padding: 0 5px; height: 44px;"></label>
            <label>Text Color<input type="color" name="announcement_text_color" value="<?= e($settings['announcement_text_color']); ?>" style="padding: 0 5px; height: 44px;"></label>
        </div>
        
        <!-- HOMEPAGE LAYOUT TAB -->
        <div id="tab-homepage" class="tab-content">
            <h3 class="settings-section-title">Configure Homepage Sections</h3>
            
            <?php
            $sections = [
                'categories' => 'Shop By Category',
                'promo_slider' => 'Promo Banners Slider',
                'men' => 'Men Collection Grid',
                'women' => 'Women Collection Grid',
                'kids' => 'Kids Collection Grid',
                'trending' => 'Trending Products Slider',
                'new_arrivals' => 'New Arrivals Grid',
                'best_sellers' => 'Best Sellers Slider',
                'contact_band' => 'Request Quote Banner',
            ];
            foreach ($sections as $sectionKey => $sectionName):
            ?>
                <div class="section-row wide">
                    <span style="font-weight: 600; min-width: 200px;"><?= $sectionName; ?></span>
                    <label style="display: flex; align-items: center; gap: 8px; width: auto; margin-bottom: 0; cursor: pointer;">
                        <input type="checkbox" name="section_<?= $sectionKey; ?>_enabled" value="1" <?= $settings['section_' . $sectionKey . '_enabled'] === '1' ? 'checked' : ''; ?> style="width: 18px; height: 18px;">
                        Active
                    </label>
                    <label style="width: auto; margin-bottom: 0; display: flex; align-items: center; gap: 8px;">
                        Order: 
                        <input type="number" name="section_<?= $sectionKey; ?>_order" value="<?= (int)$settings['section_' . $sectionKey . '_order']; ?>" style="width: 80px; padding: 6px;" min="1" max="20">
                    </label>
                </div>
            <?php endforeach; ?>
        </div>
        
        <button class="admin-btn primary" style="margin-top: 30px;">Save Configuration</button>
    </form>
</section>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            tabBtns.forEach(b => b.classList.remove('active'));
            tabContents.forEach(c => c.classList.remove('active'));
            
            btn.classList.add('active');
            const targetId = 'tab-' + btn.dataset.tab;
            document.getElementById(targetId).classList.add('active');
        });
    });
});
</script>

<?php require_once __DIR__ . '/../footer.php'; ?>
