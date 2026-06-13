CREATE DATABASE IF NOT EXISTS zetastyle CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE zetastyle;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS activity_logs;
DROP TABLE IF EXISTS settings;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS contact_messages;
DROP TABLE IF EXISTS blogs;
DROP TABLE IF EXISTS banners;
DROP TABLE IF EXISTS product_images;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS sub_categories;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS admins;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE admins (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(180) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    status TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    slug VARCHAR(140) NOT NULL UNIQUE,
    image_url VARCHAR(500) NOT NULL,
    display_order INT UNSIGNED NOT NULL DEFAULT 0,
    status TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE sub_categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id INT UNSIGNED NOT NULL,
    name VARCHAR(120) NOT NULL,
    slug VARCHAR(140) NOT NULL UNIQUE,
    image_url VARCHAR(500) NOT NULL,
    status TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_sub_categories_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE products (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id INT UNSIGNED NOT NULL,
    sub_category_id INT UNSIGNED NULL,
    name VARCHAR(180) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    sku VARCHAR(90) NOT NULL UNIQUE,
    description TEXT NOT NULL,
    current_price DECIMAL(10,2) NOT NULL,
    old_price DECIMAL(10,2) NOT NULL,
    discount_badge VARCHAR(40) NOT NULL,
    product_tag ENUM('trending', 'new', 'best') NOT NULL DEFAULT 'new',
    image_url VARCHAR(500) NOT NULL,
    available_sizes VARCHAR(255) NOT NULL DEFAULT 'S,M,L,XL',
    available_colors VARCHAR(255) NOT NULL DEFAULT 'Black,White',
    stock INT NOT NULL DEFAULT 0,
    is_trending TINYINT(1) NOT NULL DEFAULT 0,
    is_best_seller TINYINT(1) NOT NULL DEFAULT 0,
    is_new_arrival TINYINT(1) NOT NULL DEFAULT 0,
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    CONSTRAINT fk_products_sub_category FOREIGN KEY (sub_category_id) REFERENCES sub_categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE product_images (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    image_url VARCHAR(500) NOT NULL,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_product_images_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE banners (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(180) NOT NULL,
    subtitle VARCHAR(255) NOT NULL,
    image_url VARCHAR(500) NOT NULL,
    mobile_image_url VARCHAR(500) NOT NULL DEFAULT '',
    link_url VARCHAR(255) NOT NULL,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE blogs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(220) NOT NULL,
    slug VARCHAR(240) NOT NULL UNIQUE,
    excerpt VARCHAR(300) NOT NULL,
    content LONGTEXT NOT NULL,
    seo_title VARCHAR(220) NOT NULL DEFAULT '',
    seo_description VARCHAR(300) NOT NULL DEFAULT '',
    image_url VARCHAR(500) NOT NULL,
    status ENUM('published', 'draft') NOT NULL DEFAULT 'published',
    published_at DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE contact_messages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(180) NOT NULL,
    phone VARCHAR(40) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE orders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id VARCHAR(40) NOT NULL UNIQUE,
    customer_name VARCHAR(140) NOT NULL,
    phone VARCHAR(40) NOT NULL,
    address TEXT NOT NULL,
    notes TEXT NULL,
    status ENUM('pending','confirmed','printing','packed','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending',
    courier_name VARCHAR(120) NOT NULL DEFAULT '',
    tracking_number VARCHAR(120) NOT NULL DEFAULT '',
    expected_delivery DATE NULL,
    total_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE order_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NULL,
    product_name VARCHAR(180) NOT NULL,
    quantity INT UNSIGNED NOT NULL DEFAULT 1,
    price DECIMAL(10,2) NOT NULL,
    CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_order_items_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(120) NOT NULL UNIQUE,
    setting_value TEXT NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE activity_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    admin_id INT UNSIGNED NULL,
    action VARCHAR(120) NOT NULL,
    entity_type VARCHAR(120) NOT NULL,
    entity_id INT UNSIGNED NULL,
    ip_address VARCHAR(80) NOT NULL DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_activity_admin FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO admins (name, email, password_hash, status) VALUES
('ZetaStyle Admin', 'admin@zetastyle.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCwM5fZL1MWTpiI/aCy', 1);

INSERT INTO categories (id, name, slug, image_url, display_order, status) VALUES
(1, 'Men', 'men', 'https://images.unsplash.com/photo-1516257984-b1b4d707412e?auto=format&fit=crop&w=900&q=85', 1, 1),
(2, 'Women', 'women', 'https://images.unsplash.com/photo-1529139574466-a303027c1d8b?auto=format&fit=crop&w=900&q=85', 2, 1),
(3, 'Kids', 'kids', 'https://images.unsplash.com/photo-1519238263530-99bdd11df2ea?auto=format&fit=crop&w=900&q=85', 3, 1),
(4, 'Oversized', 'oversized', 'https://images.unsplash.com/photo-1503342217505-b0a15ec3261c?auto=format&fit=crop&w=900&q=85', 4, 1),
(5, 'Accessories', 'accessories', 'https://images.unsplash.com/photo-1523170335258-f5ed11844a49?auto=format&fit=crop&w=900&q=85', 5, 1);

INSERT INTO sub_categories (category_id, name, slug, image_url, status) VALUES
(1, 'Premium Tees', 'men-premium-tees', 'https://images.unsplash.com/photo-1523398002811-999ca8dec234?auto=format&fit=crop&w=900&q=85', 1),
(1, 'Printed Polos', 'men-polos', 'https://images.unsplash.com/photo-1618354691373-d851c5c3a990?auto=format&fit=crop&w=900&q=85', 1),
(1, 'Signature Hoodies', 'men-hoodies', 'https://images.unsplash.com/photo-1556821840-3a63f95609a7?auto=format&fit=crop&w=900&q=85', 1),
(2, 'Crop Tops', 'women-crop-tops', 'https://images.unsplash.com/photo-1496747611176-843222e1e57c?auto=format&fit=crop&w=900&q=85', 1),
(2, 'Printed Totes', 'women-totes', 'https://images.unsplash.com/photo-1590874103328-eac38a683ce7?auto=format&fit=crop&w=900&q=85', 1),
(3, 'Kids Tees', 'kids-tees', 'https://images.unsplash.com/photo-1503919545889-aef636e10ad4?auto=format&fit=crop&w=900&q=85', 1);

INSERT INTO products (category_id, sub_category_id, name, slug, sku, description, current_price, old_price, discount_badge, product_tag, image_url, available_sizes, available_colors, stock, is_trending, is_best_seller, is_new_arrival, is_featured, is_active) VALUES
(1, 1, 'Atelier Black Custom Tee', 'atelier-black-tee', 'ZS-MEN-TEE-001', 'Soft black tee with premium custom print finish.', 39.00, 55.00, '29% OFF', 'trending', 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?auto=format&fit=crop&w=900&q=85', 'S,M,L,XL', 'Black,White', 48, 1, 0, 0, 1, 1),
(2, 4, 'Monogram Cream Hoodie', 'monogram-cream-hoodie', 'ZS-WOM-HOD-001', 'Cream hoodie with refined monogram placement.', 74.00, 98.00, 'NEW', 'new', 'https://images.unsplash.com/photo-1554568218-0f1715e72254?auto=format&fit=crop&w=900&q=85', 'S,M,L,XL', 'Cream,Black', 30, 0, 0, 1, 1, 1),
(4, NULL, 'Oversized Studio Tee', 'oversized-studio-tee', 'ZS-OVR-TEE-001', 'Relaxed oversized tee for expressive custom artwork.', 48.00, 66.00, 'BEST', 'best', 'https://images.unsplash.com/photo-1503341504253-dff4815485f1?auto=format&fit=crop&w=900&q=85', 'M,L,XL,XXL', 'Ash,Black', 55, 0, 1, 0, 1, 1),
(3, 6, 'Mini Creator Tee', 'mini-creator-tee', 'ZS-KID-TEE-001', 'Kids tee designed for birthdays, teams, and gifts.', 28.00, 36.00, '22% OFF', 'new', 'https://images.unsplash.com/photo-1519457431-44ccd64a579b?auto=format&fit=crop&w=900&q=85', '2Y,4Y,6Y,8Y', 'White,Blue', 64, 0, 0, 1, 0, 1),
(5, NULL, 'Signature Printed Tote', 'signature-tote', 'ZS-ACC-TOT-001', 'Durable tote with crisp custom print area.', 31.00, 42.00, 'HOT', 'trending', 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=900&q=85', 'One Size', 'Natural,Black', 75, 1, 0, 0, 0, 1),
(1, 2, 'Club Mark Polo', 'club-polo', 'ZS-MEN-POL-001', 'Smart polo for teams, clubs, and elevated uniforms.', 52.00, 70.00, '26% OFF', 'best', 'https://images.unsplash.com/photo-1620012253295-c15cc3e65df4?auto=format&fit=crop&w=900&q=85', 'S,M,L,XL', 'Navy,White', 28, 0, 1, 0, 0, 1);

INSERT INTO banners (title, subtitle, image_url, mobile_image_url, link_url, sort_order, is_active) VALUES
('Custom Print Studio', 'Upload artwork, pick fabric, and wear your idea beautifully.', 'https://images.unsplash.com/photo-1489987707025-afc232f7ea0f?auto=format&fit=crop&w=1500&q=90', '', 'shop.php', 1, 1),
('Monochrome Essentials', 'Minimal silhouettes finished with premium personalization.', 'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?auto=format&fit=crop&w=1500&q=90', '', 'category.php?category=oversized', 2, 1);

INSERT INTO blogs (title, slug, excerpt, content, seo_title, seo_description, image_url, status, published_at) VALUES
('How to Choose the Perfect Print Placement', 'choose-perfect-print-placement', 'A premium garment starts with proportion, scale, and breathing room.', '<p>Balance artwork scale with garment shape, fabric color, and daily wearability.</p>', 'Print placement guide', 'Learn how to place custom print artwork on clothing.', 'https://images.unsplash.com/photo-1452860606245-08befc0ff44b?auto=format&fit=crop&w=900&q=85', 'published', '2026-06-04'),
('Cotton vs. Fleece for Custom Apparel', 'cotton-vs-fleece', 'A practical guide to matching fabric weight with design intent.', '<p>Cotton keeps graphics crisp while fleece creates a softer lifestyle finish.</p>', 'Cotton vs fleece', 'Choose the right fabric for custom apparel.', 'https://images.unsplash.com/photo-1445205170230-053b83016050?auto=format&fit=crop&w=900&q=85', 'published', '2026-05-24');

INSERT INTO orders (order_id, customer_name, phone, address, notes, status, courier_name, tracking_number, expected_delivery, total_amount) VALUES
('ZS-10482', 'Sample Customer', '+15550199', '42 Studio Lane, New York, NY', 'WhatsApp order for team tees.', 'printing', 'DHL', 'DHL998877', DATE_ADD(CURDATE(), INTERVAL 4 DAY), 156.00);

INSERT INTO order_items (order_id, product_name, quantity, price) VALUES
(1, 'Atelier Black Custom Tee', 4, 39.00);

INSERT INTO contact_messages (name, email, phone, message) VALUES
('Avery Stone', 'avery@example.com', '+15550123', 'I need 30 custom hoodies for a launch event.');

INSERT INTO settings (setting_key, setting_value) VALUES
('website_name', 'ZetaStyle'),
('logo', ''),
('favicon', ''),
('contact_number', '+1 555 0199'),
('whatsapp_number', '+1 555 0199'),
('email', 'care@zetastyle.test'),
('address', '42 Studio Lane, New York, NY'),
('instagram', 'https://instagram.com'),
('facebook', 'https://facebook.com'),
('footer_text', 'Premium custom printed clothing designed for polished teams, creators, families, and everyday wardrobes.'),
('seo_title', 'ZetaStyle'),
('seo_description', 'Premium custom printed clothing store.');
