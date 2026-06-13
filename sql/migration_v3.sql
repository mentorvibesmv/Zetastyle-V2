-- Migration file for ZetaStyle V3 - Master Refactor

USE zetastyle;

-- 1. Create enquiries table
CREATE TABLE IF NOT EXISTS enquiries (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(140) NOT NULL,
    phone VARCHAR(40) NOT NULL,
    address TEXT NOT NULL,
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100) NOT NULL,
    pincode VARCHAR(20) NOT NULL,
    payment_method VARCHAR(20) NOT NULL DEFAULT 'COD',
    shipping_charge DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    cod_charge DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    status ENUM('New', 'Contacted', 'Converted', 'Cancelled') NOT NULL DEFAULT 'New',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Create enquiry_items table
CREATE TABLE IF NOT EXISTS enquiry_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    enquiry_id INT UNSIGNED NOT NULL,
    product_name VARCHAR(180) NOT NULL,
    quantity INT UNSIGNED NOT NULL DEFAULT 1,
    price DECIMAL(10,2) NOT NULL,
    CONSTRAINT fk_enquiry_items_enquiry FOREIGN KEY (enquiry_id) REFERENCES enquiries(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Modify orders table columns (add missing columns)
ALTER TABLE orders
ADD COLUMN IF NOT EXISTS city VARCHAR(100) NOT NULL DEFAULT '',
ADD COLUMN IF NOT EXISTS state VARCHAR(100) NOT NULL DEFAULT '',
ADD COLUMN IF NOT EXISTS pincode VARCHAR(20) NOT NULL DEFAULT '',
ADD COLUMN IF NOT EXISTS payment_method VARCHAR(20) NOT NULL DEFAULT 'COD',
ADD COLUMN IF NOT EXISTS shipping_charge DECIMAL(10,2) NOT NULL DEFAULT 0.00,
ADD COLUMN IF NOT EXISTS cod_charge DECIMAL(10,2) NOT NULL DEFAULT 0.00,
ADD COLUMN IF NOT EXISTS tracking_url VARCHAR(255) NOT NULL DEFAULT '',
ADD COLUMN IF NOT EXISTS dispatch_date DATE NULL;

-- 4. Temporarily alter status column to VARCHAR to allow any values
ALTER TABLE orders MODIFY COLUMN status VARCHAR(50) NOT NULL DEFAULT 'Confirmed';

-- 5. Map old orders status to the exact new casing and labels
UPDATE orders SET status = 'Confirmed' WHERE status = 'pending' OR status = 'confirmed';
UPDATE orders SET status = 'Packing' WHERE status = 'printing' OR status = 'packed';
UPDATE orders SET status = 'Dispatched' WHERE status = 'shipped';
UPDATE orders SET status = 'Delivered' WHERE status = 'delivered';
UPDATE orders SET status = 'Cancelled' WHERE status = 'cancelled';

-- 6. Finally, convert status column to the restricted ENUM
ALTER TABLE orders MODIFY COLUMN status ENUM('Confirmed', 'Packing', 'Dispatched', 'Delivered', 'Cancelled') NOT NULL DEFAULT 'Confirmed';

-- 7. Insert new settings options if they do not exist
INSERT INTO settings (setting_key, setting_value) VALUES
('brand_tagline', 'Custom printed clothing for elevated everyday wear'),
('secondary_phone', '+1 555 0198'),
('contact_email', 'care@zetastyle.test'),
('support_email', 'support@zetastyle.test'),
('city', 'New York'),
('state', 'NY'),
('pincode', '10001'),
('google_map_url', 'https://maps.google.com'),
('youtube_url', 'https://youtube.com'),
('linkedin_url', 'https://linkedin.com'),
('twitter_url', 'https://twitter.com'),
('shipping_tn', '50.00'),
('shipping_other', '100.00'),
('cod_charge', '40.00'),
('seo_keywords', 'custom print, clothing, t-shirts, hoodies'),
('default_og_image', ''),
('footer_description', 'Premium custom printed clothing designed for polished teams, creators, families, and everyday wardrobes.'),
('copyright_text', '© 2026 ZetaStyle. All rights reserved.'),
('newsletter_text', 'Receive fabric drops, print-care notes, and private collection previews.'),
('announcement_enable', '1'),
('announcement_text', 'Free premium packaging on custom print orders over $99'),
('announcement_bg_color', '#111111'),
('announcement_text_color', '#ffffff')
ON DUPLICATE KEY UPDATE setting_value=setting_value; -- do not overwrite existing ones
