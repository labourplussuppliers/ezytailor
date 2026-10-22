-- ========================================================
-- EzyTailor Web Application - Production Database Schema
-- Compatible with MySQL 5.7+, 8.0+, MariaDB & SQLite
-- ========================================================

CREATE DATABASE IF NOT EXISTS `ezytailor_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `ezytailor_db`;

-- 1. Users / Auth Table
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `full_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) DEFAULT NULL,
    `role` ENUM('admin', 'tailor', 'cashier') NOT NULL DEFAULT 'admin',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Shop Settings & Preferences
CREATE TABLE IF NOT EXISTS `settings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `shop_name` VARCHAR(150) NOT NULL DEFAULT 'ezyTailor Boutique & Tailors',
    `tagline` VARCHAR(200) DEFAULT 'Smart Tailoring & Bespoke Stitching',
    `owner_name` VARCHAR(100) DEFAULT 'Ustad Muhammad Asif',
    `phone` VARCHAR(30) DEFAULT '+92 300 9876543',
    `whatsapp` VARCHAR(30) DEFAULT '+923009876543',
    `email` VARCHAR(100) DEFAULT 'contact@ezytailor.com',
    `city` VARCHAR(50) DEFAULT 'Lahore',
    `address` TEXT,
    `currency` VARCHAR(10) DEFAULT 'Rs',
    `theme` VARCHAR(30) DEFAULT 'olive',
    `language` VARCHAR(10) DEFAULT 'en',
    `receipt_header` TEXT,
    `receipt_footer` TEXT,
    `terms` TEXT,
    `receipt_paper_size` VARCHAR(10) DEFAULT '80mm',
    `show_barcode` TINYINT(1) DEFAULT 1,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Customers Table
CREATE TABLE IF NOT EXISTS `customers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `customer_code` VARCHAR(30) UNIQUE NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(30) NOT NULL,
    `alt_phone` VARCHAR(30) DEFAULT NULL,
    `city` VARCHAR(50) DEFAULT 'Lahore',
    `gender` ENUM('male', 'female', 'other') DEFAULT 'male',
    `address` TEXT DEFAULT NULL,
    `notes` TEXT DEFAULT NULL,
    `photo` VARCHAR(255) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Measurement Templates
CREATE TABLE IF NOT EXISTS `measurement_templates` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `template_key` VARCHAR(50) UNIQUE NOT NULL,
    `title` VARCHAR(100) NOT NULL,
    `title_ur` VARCHAR(100) DEFAULT NULL,
    `title_hi` VARCHAR(100) DEFAULT NULL,
    `title_ar` VARCHAR(100) DEFAULT NULL,
    `garment_category` VARCHAR(50) NOT NULL,
    `icon` VARCHAR(50) DEFAULT 'bi-person',
    `fields_json` LONGTEXT NOT NULL,
    `styles_json` LONGTEXT DEFAULT NULL,
    `is_default` TINYINT(1) DEFAULT 1,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Customer Saved Measurements
CREATE TABLE IF NOT EXISTS `customer_measurements` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `customer_id` INT NOT NULL,
    `template_id` INT DEFAULT NULL,
    `garment_type` VARCHAR(50) NOT NULL,
    `title` VARCHAR(100) NOT NULL DEFAULT 'Standard Fit',
    `measurements_json` LONGTEXT NOT NULL,
    `styles_json` LONGTEXT DEFAULT NULL,
    `fabric_photo` VARCHAR(255) DEFAULT NULL,
    `audio_note` VARCHAR(255) DEFAULT NULL,
    `notes` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Workers / Karigars Table
CREATE TABLE IF NOT EXISTS `workers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(30) DEFAULT NULL,
    `role` VARCHAR(50) DEFAULT 'Senior Tailor',
    `rate_per_piece` DECIMAL(10,2) DEFAULT 400.00,
    `status` ENUM('active', 'inactive') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Orders Table
CREATE TABLE IF NOT EXISTS `orders` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_no` VARCHAR(40) UNIQUE NOT NULL,
    `customer_id` INT NOT NULL,
    `measurement_id` INT DEFAULT NULL,
    `garment_type` VARCHAR(50) NOT NULL,
    `quantity` INT NOT NULL DEFAULT 1,
    `unit_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `total_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `advance_paid` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `balance_due` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `payment_status` ENUM('unpaid', 'partial', 'paid') DEFAULT 'unpaid',
    `order_status` ENUM('pending', 'cutting', 'stitching', 'ready', 'delivered', 'cancelled') DEFAULT 'pending',
    `booking_date` DATE NOT NULL,
    `delivery_date` DATE NOT NULL,
    `is_urgent` TINYINT(1) DEFAULT 0,
    `fabric_details` TEXT DEFAULT NULL,
    `fabric_photo` VARCHAR(255) DEFAULT NULL,
    `audio_note` VARCHAR(255) DEFAULT NULL,
    `special_instructions` TEXT DEFAULT NULL,
    `assigned_worker_id` INT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Order Items / Multi-item breakdown
CREATE TABLE IF NOT EXISTS `order_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT NOT NULL,
    `item_name` VARCHAR(100) NOT NULL,
    `garment_type` VARCHAR(50) NOT NULL,
    `quantity` INT NOT NULL DEFAULT 1,
    `unit_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `total_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `measurement_snapshot_json` LONGTEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Payments Table
CREATE TABLE IF NOT EXISTS `payments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT DEFAULT NULL,
    `customer_id` INT NOT NULL,
    `amount` DECIMAL(10,2) NOT NULL,
    `payment_method` VARCHAR(50) DEFAULT 'Cash',
    `transaction_ref` VARCHAR(100) DEFAULT NULL,
    `notes` VARCHAR(255) DEFAULT NULL,
    `payment_date` DATE NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Ledger Book (Cash In / Cash Out)
CREATE TABLE IF NOT EXISTS `ledger` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `type` ENUM('income', 'expense') NOT NULL,
    `category` VARCHAR(50) NOT NULL,
    `title` VARCHAR(200) NOT NULL,
    `amount` DECIMAL(10,2) NOT NULL,
    `date` DATE NOT NULL,
    `reference_type` VARCHAR(50) DEFAULT 'general',
    `reference_id` INT DEFAULT NULL,
    `notes` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================
-- SEED DATA
-- ========================================================

-- Default Admin User (Password: admin123)
INSERT INTO `users` (`id`, `username`, `password_hash`, `full_name`, `email`, `role`)
VALUES (1, 'admin', '$2y$10$tM3VbSvhPvZJ8B7r.4yZ0uF9k5EwT3N1l6wFzTjM9.QyW7h9h7zO.', 'Master Tailor Admin', 'admin@ezytailor.com', 'admin')
ON DUPLICATE KEY UPDATE `username`=`username`;

-- Shop Settings
INSERT INTO `settings` (`id`, `shop_name`, `tagline`, `owner_name`, `phone`, `whatsapp`, `email`, `city`, `address`, `currency`, `theme`, `language`, `receipt_header`, `receipt_footer`, `terms`, `receipt_paper_size`, `show_barcode`)
VALUES (
    1,
    'ezyTailor Boutique & Master Tailors',
    'Smart Tailoring POS & Bespoke Stitching',
    'Ustad Muhammad Asif',
    '+92 300 9876543',
    '+923009876543',
    'support@ezytailor.com',
    'Lahore',
    'Shop #14, Ground Floor, Pace Shopping Mall, Gulberg III, Lahore',
    'Rs',
    'olive',
    'en',
    '*** ezyTailor Premium Tailoring Studio ***\nPrecision Fitting & Exceptional Craftsmanship',
    'Thank you for trusting us with your style!\nPlease retain this thermal slip for order collection.',
    '1. Suits must be collected within 30 days of the committed delivery date.\n2. Minor alterations are free if claimed within 7 days of collection.\n3. Advance payment is non-refundable once cutting has begun.\n4. No suit handed over without returning this receipt.',
    '80mm',
    1
)
ON DUPLICATE KEY UPDATE `id`=`id`;

-- Measurement Templates
INSERT INTO `measurement_templates` (`id`, `template_key`, `title`, `title_ur`, `title_hi`, `title_ar`, `garment_category`, `icon`, `fields_json`, `styles_json`, `is_default`)
VALUES
(
    1,
    'male_shalwar_kameez',
    'Male Shalwar Kameez',
    'شلوار قمیض (مردانہ)',
    'सलवार कमीज (पुरुष)',
    'ثوب شلوار قميص رجالي',
    'traditional',
    'bi-person',
    '[
        {"key":"lambai","label":"Kameez Length","label_ur":"لمبائی","label_hi":"कमीज लंबाई","label_ar":"طول القميص","default":"40.5","unit":"in"},
        {"key":"tera","label":"Shoulder / Tira","label_ur":"تیرا","label_hi":"कंधा / तीरा","label_ar":"الكتف","default":"18.0","unit":"in"},
        {"key":"chaati","label":"Chest / Chhati","label_ur":"چھاتی","label_hi":"छाती","label_ar":"الصدر","default":"39.0","unit":"in"},
        {"key":"loosing","label":"Loosing / Fitting","label_ur":"لوزنگ","label_hi":"लूजिंग","label_ar":"التوسيع","default":"4.0","unit":"in"},
        {"key":"kamar","label":"Waist / Kamar","label_ur":"کمر","label_hi":"कमर","label_ar":"الخصر","default":"37.0","unit":"in"},
        {"key":"ghera","label":"Ghera / Daman","label_ur":"گھیرا / دامن","label_hi":"घेरा","label_ar":"الذيل","default":"23.5","unit":"in"},
        {"key":"bazo","label":"Sleeve / Bazu","label_ur":"بازو","label_hi":"बाजू","label_ar":"الكم","default":"23.5","unit":"in"},
        {"key":"collar","label":"Collar / Hala","label_ur":"کالر / گلا","label_hi":"कॉलर","label_ar":"الياقة","default":"15.5","unit":"in"},
        {"key":"cuff","label":"Cuff","label_ur":"کف","label_hi":"कफ","label_ar":"الأسورة","default":"9.5","unit":"in"},
        {"key":"armhole","label":"Armhole / Modha","label_ur":"موڈھا","label_hi":"आर्महोल","label_ar":"حفرة الإبط","default":"9.5","unit":"in"},
        {"key":"shalwar_length","label":"Shalwar Length","label_ur":"شلوار لمبائی","label_hi":"सलवार लंबाई","label_ar":"طول السروال","default":"39.5","unit":"in"},
        {"key":"paicha","label":"Paicha / Mori","label_ur":"پانچہ","label_hi":"पांयचा","label_ar":"عرض القدم","default":"8.5","unit":"in"},
        {"key":"asan","label":"Asan / Rise","label_ur":"آسن","label_hi":"आसन","label_ar":"الحجر","default":"16.5","unit":"in"},
        {"key":"shalwar_ghera","label":"Shalwar Ghera","label_ur":"شلوار گھیرا","label_hi":"सलवार घेरा","label_ar":"عرض الشلوار","default":"21.5","unit":"in"}
    ]',
    '[
        {"key":"collar_type","label":"Collar Style","options":["Full Sherwani Ban (شیروانی بین)","Half Ban (ہاف بین)","Shirt Collar (شرٹ کالر)","Cut Ban (کٹ بین)","Kurta Collar (سادہ کٹ)"]},
        {"key":"cuff_type","label":"Cuff Style","options":["Single Button Cuff (سنگل کف)","Double Button Cuff (ڈبل بٹن)","Cut Cuff (کٹ کف)","Sadha Open Sleeve (سادہ کھلا بازو)"]},
        {"key":"daman_type","label":"Daman Style","options":["Choras / Square (چورس دامن)","Gol / Round (گول دامن)","Short Kurta Cut (شارٹ کرتا)"]},
        {"key":"front_patti","label":"Front Patti","options":["Hidden / Gum Patti (گم پٹی)","Open Button Patti (اوپن پٹی)","Fancy Double Stitch (ڈبل سلائی)"]},
        {"key":"pockets","label":"Pockets","options":["1 Front Pocket + 2 Side Pockets","1 Front Pocket + 1 Side Pocket","Front Pocket Only","No Front Pocket, 2 Side Pockets"]},
        {"key":"shalwar_pocket","label":"Shalwar Pocket","options":["Yes - Single Right Pocket","Yes - Both Side Pockets (Zipper)","No Pocket"]}
    ]',
    1
),
(
    2,
    'female_shalwar_kameez',
    'Female Shalwar Kameez',
    'شلوار قمیض (زنـانہ)',
    'सलवार कमीज (महिला)',
    'طقم نسائي قميص وشلوار',
    'ladies',
    'bi-flower1',
    '[
        {"key":"kameez_length","label":"Kameez Length","label_ur":"قمیض لمبائی","label_hi":"कमीज लंबाई","label_ar":"طول القميص","default":"39.0","unit":"in"},
        {"key":"tera","label":"Shoulder / Tira","label_ur":"تیرا","label_hi":"कंधा","label_ar":"الكتف","default":"14.5","unit":"in"},
        {"key":"chest","label":"Bust / Chest","label_ur":"چھاتی","label_hi":"छाती","label_ar":"الصدر","default":"36.5","unit":"in"},
        {"key":"waist","label":"Waist / Kamar","label_ur":"کمر","label_hi":"कमर","label_ar":"الخصر","default":"32.0","unit":"in"},
        {"key":"hip","label":"Hip","label_ur":"ہپ / کولہے","label_hi":"हिप","label_ar":"الورك","default":"38.5","unit":"in"},
        {"key":"daman","label":"Daman / Bottom Ghera","label_ur":"دامن","label_hi":"दामन","label_ar":"الذيل","default":"22.0","unit":"in"},
        {"key":"sleeves","label":"Sleeve Length","label_ur":"بازو لمبائی","label_hi":"बाजू लंबाई","label_ar":"طول الكم","default":"21.0","unit":"in"},
        {"key":"armhole","label":"Armhole","label_ur":"موڈھا","label_hi":"आर्महोल","label_ar":"الإبط","default":"8.0","unit":"in"},
        {"key":"sleeve_hole","label":"Sleeve Opening","label_ur":"موری بازو","label_hi":"बाजू मोरी","label_ar":"فتحة الكم","default":"5.5","unit":"in"},
        {"key":"neck_front","label":"Front Neck Deep","label_ur":"اگلا گلا","label_hi":"आगे का गला","label_ar":"الرقبة الأمامية","default":"6.5","unit":"in"},
        {"key":"neck_back","label":"Back Neck Deep","label_ur":"پچھلا گلا","label_hi":"पीछे का गला","label_ar":"الرقبة الخلفية","default":"4.0","unit":"in"},
        {"key":"bottom_length","label":"Bottom / Trouser Length","label_ur":"ٹراؤزر / شلوار لمبائی","label_hi":"ट्राउजर लंबाई","label_ar":"طول البنطال","default":"37.5","unit":"in"},
        {"key":"bottom_paicha","label":"Bottom Opening / Paicha","label_ur":"پانچہ","label_hi":"पांयचा","label_ar":"عرض البنطال","default":"7.0","unit":"in"}
    ]',
    '[
        {"key":"bottom_type","label":"Bottom Type","options":["Straight Cigarette Trouser","Classic Shalwar","Flapper / Bell Bottom","Tulip Shalwar","Plazo Pants"]},
        {"key":"neck_design","label":"Neckline Pattern","options":["Round Neck with Slit & Piping","Boat Neck (کشتی گلا)","Ban Collar with V-Neck","Sweetheart Neck","Angrakha Style"]},
        {"key":"sleeve_style","label":"Sleeve Styling","options":["Straight Cut with Lace","Bell Sleeves (بیل سلیوز)","Ruffle Elastic Cuff","Cuff with Potli Buttons"]}
    ]',
    1
),
(
    3,
    'shirt',
    'Men Shirt',
    'مردانہ شرٹ',
    'पुरुष शर्ट',
    'قميص رجالي كلاسيك',
    'western',
    'bi-app-indicator',
    '[
        {"key":"shirt_length","label":"Shirt Length","label_ur":"لمبائی","label_hi":"शर्ट लंबाई","label_ar":"طول القميص","default":"29.5","unit":"in"},
        {"key":"shoulder","label":"Shoulder","label_ur":"تیرا","label_hi":"कंधा","label_ar":"الكتف","default":"18.0","unit":"in"},
        {"key":"chest","label":"Chest","label_ur":"چھاتی","label_hi":"छाती","label_ar":"الصدر","default":"39.5","unit":"in"},
        {"key":"waist","label":"Waist","label_ur":"کمر","label_hi":"कमर","label_ar":"الخصر","default":"36.0","unit":"in"},
        {"key":"sleeves","label":"Sleeve Length","label_ur":"بازو","label_hi":"बाजू","label_ar":"الكم","default":"24.5","unit":"in"},
        {"key":"collar","label":"Collar Neck","label_ur":"کالر","label_hi":"कॉलर","label_ar":"الياقة","default":"15.5","unit":"in"},
        {"key":"cuff","label":"Cuff Size","label_ur":"کف","label_hi":"कफ","label_ar":"الأسورة","default":"9.5","unit":"in"},
        {"key":"armhole","label":"Armhole","label_ur":"موڈھا","label_hi":"आर्महोल","label_ar":"حفرة الذراع","default":"9.5","unit":"in"}
    ]',
    '[
        {"key":"fitting","label":"Fitting Type","options":["Slim Fit","Tailored Fit","Regular Comfort Fit"]},
        {"key":"collar_style","label":"Collar Design","options":["Standard Spread Collar","Cutaway Windsor Collar","Button-Down Collar","Band / Mandarin Collar (بین)"]},
        {"key":"cuff_style","label":"Cuff Type","options":["Single Button Rounded","Double Button Angled","French Cuff (for Cufflinks)"]}
    ]',
    1
),
(
    4,
    'pant',
    'Trousers / Dress Pant',
    'ڈریس پینٹ / ٹراؤزر',
    'पैंट / पतलून',
    'بنطال كلاسيك',
    'western',
    'bi-layout-split',
    '[
        {"key":"pant_length","label":"Pant Length (Outseam)","label_ur":"پینٹ لمبائی","label_hi":"पैंट लंबाई","label_ar":"طول البنطال","default":"40.5","unit":"in"},
        {"key":"waist","label":"Waist","label_ur":"کمر","label_hi":"कमر","label_ar":"محيط الخصر","default":"33.0","unit":"in"},
        {"key":"hip","label":"Hip","label_ur":"کولہے","label_hi":"हिप","label_ar":"الورك","default":"39.0","unit":"in"},
        {"key":"thigh","label":"Thigh / Raand","label_ur":"ران","label_hi":"जांघ","label_ar":"الفخذ","default":"25.0","unit":"in"},
        {"key":"knee","label":"Knee / Goda","label_ur":"گھٹنا","label_hi":"घुटना","label_ar":"الركبة","default":"18.5","unit":"in"},
        {"key":"bottom","label":"Bottom / Mori","label_ur":"باٹم / موری","label_hi":"बॉटम","label_ar":"الفتحة السفلية","default":"15.0","unit":"in"},
        {"key":"rise","label":"Fly / Rise (Front)","label_ur":"فلائی / آسن","label_hi":"फ्लाई","label_ar":"الحجر","default":"10.5","unit":"in"}
    ]',
    '[
        {"key":"pleats","label":"Pleats Pattern","options":["Flat Front (No Pleats)","Single Pleat","Double Pleats"]},
        {"key":"pocket_style","label":"Front Pockets","options":["Cross Slanted Pockets","Straight Side Seam Pockets","L-Shaped Chino Pockets"]},
        {"key":"back_pocket","label":"Back Pocket","options":["Two Welts with Buttons","One Welt on Right Side","Pocket with Flap"]}
    ]',
    1
),
(
    5,
    'waistcoat',
    'Waistcoat / Waskat',
    'ویسٹ کوٹ (واسکت)',
    'वेस्टकोट / बास्कट',
    'صديري / صديرية رجالي',
    'formal',
    'bi-award',
    '[
        {"key":"length","label":"Waistcoat Length","label_ur":"لمبائی","label_hi":"लंबाई","label_ar":"الطول","default":"27.0","unit":"in"},
        {"key":"shoulder","label":"Shoulder","label_ur":"تیرا","label_hi":"कंधा","label_ar":"الكتف","default":"17.0","unit":"in"},
        {"key":"chest","label":"Chest","label_ur":"چھاتی","label_hi":"छाती","label_ar":"الصدر","default":"39.0","unit":"in"},
        {"key":"waist","label":"Waist","label_ur":"کمر","label_hi":"कमर","label_ar":"الخصر","default":"36.0","unit":"in"},
        {"key":"neck","label":"Neck / Hala","label_ur":"گلا / ہالہ","label_hi":"गला","label_ar":"الرقبة","default":"16.0","unit":"in"},
        {"key":"armhole","label":"Armhole","label_ur":"موڈھا","label_hi":"आर्महोल","label_ar":"الإبط","default":"9.0","unit":"in"}
    ]',
    '[
        {"key":"neck_style","label":"Neck Collar","options":["Sherwani Ban Collar (بین والی)","Deep V-Neck (وی گلا)","Mandarin Stand Collar"]},
        {"key":"buttons_count","label":"Buttons Count","options":["5 Buttons Front","6 Buttons Front","Fancy Metal Crest Buttons"]},
        {"key":"bottom_shape","label":"Bottom Cut","options":["Angled Double Point (کو نے والے)","Straight Cut (سیدھا)","Round Cut (گول)"]}
    ]',
    1
),
(
    6,
    'coat_blazer',
    'Suit Coat / Blazer',
    'کوٹ / بلیزر',
    'सूट कोट / ब्लेज़र',
    'جاكيت بدلة / بليزر',
    'formal',
    'bi-briefcase',
    '[
        {"key":"coat_length","label":"Coat Length","label_ur":"لمبائی","label_hi":"लंबाई","label_ar":"طول المعطف","default":"29.0","unit":"in"},
        {"key":"shoulder","label":"Shoulder / Cross Back","label_ur":"تیرا","label_hi":"कंधा","label_ar":"عرض الكتف","default":"18.5","unit":"in"},
        {"key":"chest","label":"Chest","label_ur":"چھاتی","label_hi":"छाती","label_ar":"الصدر","default":"40.0","unit":"in"},
        {"key":"waist","label":"Waist","label_ur":"کمر","label_hi":"कमर","label_ar":"الخصر","default":"35.5","unit":"in"},
        {"key":"hip","label":"Hip / Seat","label_ur":"کولہے","label_hi":"हिप","label_ar":"الورك","default":"40.5","unit":"in"},
        {"key":"sleeves","label":"Sleeves","label_ur":"بازو","label_hi":"बाजू","label_ar":"الكم","default":"24.5","unit":"in"},
        {"key":"cross_back","label":"Half Back","label_ur":"پشت / کراس بیک","label_hi":"क्रॉस बैक","label_ar":"الظهر","default":"16.0","unit":"in"}
    ]',
    '[
        {"key":"lapel_type","label":"Lapel Style","options":["Notch Lapel (نـوچ لیپل)","Peak Lapel (پیک لیپل)","Shawl Collar (شال کالر)"]},
        {"key":"closure","label":"Front Closure","options":["2-Button Single Breasted","1-Button Single Breasted","Double Breasted (6 Buttons)"]},
        {"key":"vents","label":"Back Vents","options":["Double Side Slits (ڈبل کٹ)","Center Single Slit (سنٹر کٹ)","No Vent (بغیر کٹ)"]}
    ]',
    1
),
(
    7,
    'thobe_jubba',
    'Thobe / Jubba (عربک ثوب)',
    'ثوب / جبہ (عربی)',
    'अरबी थोब / जुब्बा',
    'ثوب عربي / جبة كويتية وسعودية',
    'traditional',
    'bi-shield-shaded',
    '[
        {"key":"length","label":"Thobe Length","label_ur":"لمبائی","label_hi":"लंबाई","label_ar":"الطول الكامل","default":"56.0","unit":"in"},
        {"key":"shoulder","label":"Shoulder","label_ur":"تیرا","label_hi":"कंधा","label_ar":"الكتف","default":"18.0","unit":"in"},
        {"key":"chest","label":"Chest","label_ur":"چھاتی","label_hi":"छाती","label_ar":"الصدر","default":"40.0","unit":"in"},
        {"key":"loosing","label":"Loosing / Fitting","label_ur":"لوزنگ","label_hi":"लूजिंग","label_ar":"الوسع","default":"5.0","unit":"in"},
        {"key":"sleeves","label":"Sleeves","label_ur":"بازو","label_hi":"बाजू","label_ar":"الكم","default":"24.0","unit":"in"},
        {"key":"neck","label":"Neck / Hala","label_ur":"گلا","label_hi":"गला","label_ar":"محيط الرقبة","default":"16.0","unit":"in"},
        {"key":"daman","label":"Daman Width","label_ur":"دامن / گھیرا","label_hi":"दामन","label_ar":"عرض الذيل","default":"32.0","unit":"in"}
    ]',
    '[
        {"key":"thobe_style","label":"Cut Style","options":["Saudi Style (سعودی اسٹائل)","Emirati / Omani with Tarboosha (اماراتی)","Kuwaiti Style (کویتی اسٹائل)","Qatari Cut (قطری)"]},
        {"key":"collar_type","label":"Collar Type","options":["High Stand Collar with 2 Buttons","Round Mandarin Ban","Soft Fold Collar"]},
        {"key":"cuff_type","label":"Sleeve Cuff","options":["French Cuff for Cufflinks","Single Button Standard Cuff","Open Loose Sleeve (سادہ کھلا)"]}
    ]',
    1
)
ON DUPLICATE KEY UPDATE `template_key`=`template_key`;

-- Sample Customers
INSERT INTO `customers` (`id`, `customer_code`, `name`, `phone`, `alt_phone`, `city`, `gender`, `address`, `notes`)
VALUES
(1, 'CUST-101', 'Chaudhry Bilal Gujjar', '+923005551234', '+923219998877', 'Lahore', 'male', 'House #45, Street #12, Block C, DHA Phase 5, Lahore', 'Regular VIP client. Prefers stiff collars and double-button cuffs.'),
(2, 'CUST-102', 'Farhan Sheikh', '+923228889900', NULL, 'Lahore', 'male', 'Apartment 302, Model Town, Lahore', 'Always requests extra loosing on kameez chest and loose paicha on shalwar.'),
(3, 'CUST-103', 'Mrs. Ayesha Malik', '+923347776655', NULL, 'Islamabad', 'female', 'Sector F-7/2, Street 18, Islamabad', 'Designer lawn suits with custom fancy laces and delicate piping.'),
(4, 'CUST-104', 'Zain Ul Abideen', '+923152223344', NULL, 'Karachi', 'male', 'Gulshan-e-Iqbal, Block 13-D, Karachi', 'Ordered charcoal wool blend 2-piece blazer for upcoming wedding.'),
(5, 'CUST-105', 'Hamza Tariq', '+923451122334', '+923015556677', 'Faisalabad', 'male', 'D-Ground, Peoples Colony #1, Faisalabad', 'Prefers Egyptian Giza cotton fabric with hidden gum patti.'),
(6, 'CUST-106', 'Dr. Tariq Mehmood', '+923004433221', NULL, 'Lahore', 'male', 'Doctors Colony, CMH Medical Complex, Lahore', 'Formal dress shirts and trousers. Flat front trousers only.'),
(7, 'CUST-107', 'Syed Ali Raza', '+923338877665', NULL, 'Rawalpindi', 'male', 'Saddar Bazar, Rawalpindi', 'White Saudi Jubba with tarboosha and cufflink sleeves.'),
(8, 'CUST-108', 'Mrs. Sania Imran', '+923214455667', NULL, 'Lahore', 'female', 'Bahria Town, Sector C, Lahore', 'A-line Kurtis and straight cigarette trousers.')
ON DUPLICATE KEY UPDATE `id`=`id`;

-- Sample Workers
INSERT INTO `workers` (`id`, `name`, `phone`, `role`, `rate_per_piece`, `status`)
VALUES
(1, 'Master Rashid Ahmed', '+923121112233', 'Master Cutter', 500.00, 'active'),
(2, 'Muhammad Tariq', '+923214445566', 'Senior Shalwar Kameez Stitcher', 600.00, 'active'),
(3, 'Kamran Ali', '+923337778899', 'Coat & Blazer Specialist', 1800.00, 'active'),
(4, 'Babar Hussain', '+923459990011', 'Trouser & Pant Craftsman', 450.00, 'active'),
(5, 'Ghulam Murtaza', '+923023344556', 'Pressing & Finishing Master', 150.00, 'active')
ON DUPLICATE KEY UPDATE `id`=`id`;

-- Sample Saved Customer Measurements
INSERT INTO `customer_measurements` (`id`, `customer_id`, `template_id`, `garment_type`, `title`, `measurements_json`, `styles_json`, `notes`)
VALUES
(
    1, 1, 1, 'male_shalwar_kameez', 'Eid Festive Fit',
    '{"lambai":"41.0","tera":"18.25","chaati":"39.5","loosing":"4.0","kamar":"37.5","ghera":"23.5","bazo":"24.0","collar":"16.0","cuff":"9.75","armhole":"9.75","shalwar_length":"40.0","paicha":"8.5","asan":"16.5","shalwar_ghera":"22.0"}',
    '{"collar_type":"Full Sherwani Ban (شیروانی بین)","cuff_type":"Single Button Cuff (سنگل کف)","daman_type":"Choras / Square (چورس دامن)","front_patti":"Hidden / Gum Patti (گم پٹی)","pockets":"1 Front Pocket + 2 Side Pockets","shalwar_pocket":"Yes - Single Right Pocket"}',
    'Customer wants fine 1.25 inch ban collar with German interlining (بکرم)'
),
(
    2, 2, 1, 'male_shalwar_kameez', 'Casual Comfort Fit',
    '{"lambai":"40.0","tera":"17.5","chaati":"38.5","loosing":"5.0","kamar":"36.5","ghera":"23.0","bazo":"23.0","collar":"15.5","cuff":"9.25","armhole":"9.5","shalwar_length":"39.0","paicha":"9.0","asan":"17.0","shalwar_ghera":"23.0"}',
    '{"collar_type":"Half Ban (ہاف بین)","cuff_type":"Sadha Open Sleeve (سادہ کھلا بازو)","daman_type":"Gol / Round (گول دامن)","front_patti":"Open Button Patti (اوپن پٹی)","pockets":"1 Front Pocket + 2 Side Pockets","shalwar_pocket":"Yes - Both Side Pockets (Zipper)"}',
    'Relaxed fit around waist and arms.'
),
(
    3, 3, 2, 'female_shalwar_kameez', 'Designer Festive Suit',
    '{"kameez_length":"39.5","tera":"14.5","chest":"36.5","waist":"31.5","hip":"38.0","daman":"22.5","sleeves":"21.5","armhole":"8.0","sleeve_hole":"5.5","neck_front":"6.5","neck_back":"4.0","bottom_length":"37.5","bottom_paicha":"6.75"}',
    '{"bottom_type":"Straight Cigarette Trouser","neck_design":"Round Neck with Slit & Piping","sleeve_style":"Straight Cut with Lace"}',
    'Attached sample silk thread lace on sleeves and daman border.'
),
(
    4, 4, 6, 'coat_blazer', 'Italian Slim Blazer',
    '{"coat_length":"29.5","shoulder":"18.75","chest":"40.5","waist":"35.0","hip":"40.5","sleeves":"25.0","cross_back":"16.5"}',
    '{"lapel_type":"Peak Lapel (پیک لیپل)","closure":"2-Button Single Breasted","vents":"Double Side Slits (ڈبل کٹ)"}',
    'Full satin lining with interior chest welt pockets.'
),
(
    5, 6, 3, 'shirt', 'Formal Oxford Shirt',
    '{"shirt_length":"30.0","shoulder":"18.0","chest":"39.5","waist":"35.5","sleeves":"24.5","collar":"15.5","cuff":"9.5","armhole":"9.5"}',
    '{"fitting":"Slim Fit","collar_style":"Standard Spread Collar","cuff_style":"Single Button Rounded"}',
    'Soft collar stay bones inserted.'
),
(
    6, 7, 7, 'thobe_jubba', 'Saudi Pure White Jubba',
    '{"length":"56.5","shoulder":"18.25","chest":"40.5","loosing":"5.0","sleeves":"24.5","neck":"16.0","daman":"33.0"}',
    '{"thobe_style":"Saudi Style (سعودی اسٹائل)","collar_type":"High Stand Collar with 2 Buttons","cuff_type":"French Cuff for Cufflinks"}',
    'Use lightweight Japanese Toyobo fabric.'
)
ON DUPLICATE KEY UPDATE `id`=`id`;

-- Sample Orders
INSERT INTO `orders` (`id`, `order_no`, `customer_id`, `measurement_id`, `garment_type`, `quantity`, `unit_price`, `total_amount`, `advance_paid`, `balance_due`, `payment_status`, `order_status`, `booking_date`, `delivery_date`, `is_urgent`, `fabric_details`, `special_instructions`, `assigned_worker_id`)
VALUES
(
    1, 'ORD-2026-001', 1, 1, 'male_shalwar_kameez', 2, 2200.00, 4400.00, 2000.00, 2400.00,
    'partial', 'stitching', '2026-10-18', '2026-10-25', 1,
    'Boski Silk (Off-white) & Wash-n-Wear (Charcoal Gray)',
    'Urgent wedding order. Double-needle stitch on all shoulder seams. Deliver by 5 PM sharp.',
    2
),
(
    2, 'ORD-2026-002', 2, 2, 'male_shalwar_kameez', 1, 2000.00, 2000.00, 2000.00, 0.00,
    'paid', 'ready', '2026-10-16', '2026-10-23', 0,
    'Al-Karam Soft Cotton (Sky Blue)',
    'Single open sleeves without cuffs, round daman.',
    2
),
(
    3, 'ORD-2026-003', 3, 3, 'female_shalwar_kameez', 1, 3200.00, 3200.00, 1500.00, 1700.00,
    'partial', 'cutting', '2026-10-20', '2026-10-28', 0,
    'Maria B Luxury Lawn 3-Piece',
    'Lace provided in fabric bag to be stitched on bottom and neckline.',
    1
),
(
    4, 'ORD-2026-004', 4, 4, 'coat_blazer', 1, 8500.00, 8500.00, 5000.00, 3500.00,
    'partial', 'stitching', '2026-10-15', '2026-10-26', 1,
    'Pure Wool Blend (Midnight Navy)',
    'Peak lapel with pick stitching and horn buttons.',
    3
),
(
    5, 'ORD-2026-005', 5, 1, 'male_shalwar_kameez', 3, 2200.00, 6600.00, 3000.00, 3600.00,
    'partial', 'pending', '2026-10-21', '2026-10-30', 0,
    'Pasha Fabrics Festive Latha (White & Cream)',
    'Gum patti with contrast anchor thread buttonholes.',
    NULL
),
(
    6, 'ORD-2026-006', 6, 5, 'shirt', 2, 1400.00, 2800.00, 2800.00, 0.00,
    'paid', 'delivered', '2026-10-10', '2026-10-17', 0,
    'Egyptian Giza Cotton (White & Powder Blue)',
    'Spread collar, single button rounded cuffs.',
    4
),
(
    7, 'ORD-2026-007', 7, 6, 'thobe_jubba', 2, 3500.00, 7000.00, 3000.00, 4000.00,
    'partial', 'cutting', '2026-10-19', '2026-10-27', 0,
    'Japanese Toyobo White Polyester-Cotton',
    'Dual French cuff and classic stand collar.',
    2
),
(
    8, 'ORD-2026-008', 8, 3, 'female_shalwar_kameez', 2, 2800.00, 5600.00, 1600.00, 4000.00,
    'partial', 'pending', '2026-10-22', '2026-10-31', 0,
    'Sapphire Printed Winter Linen',
    'Boat neck with pearl buttons on center slit.',
    NULL
),
(
    9, 'ORD-2026-009', 1, 1, 'waistcoat', 1, 3500.00, 3500.00, 3500.00, 0.00,
    'paid', 'ready', '2026-10-17', '2026-10-24', 1,
    'Raw Silk Jamawar (Emerald Green)',
    'Antique brass buttons and ban collar.',
    3
),
(
    10, 'ORD-2026-010', 2, 2, 'pant', 2, 1500.00, 3000.00, 0.00, 3000.00,
    'unpaid', 'pending', '2026-10-22', '2026-11-02', 0,
    'Wool blend chino cotton (Khaki & Navy)',
    'Flat front, cross pockets.',
    NULL
)
ON DUPLICATE KEY UPDATE `id`=`id`;

-- Sample Order Items
INSERT INTO `order_items` (`order_id`, `item_name`, `garment_type`, `quantity`, `unit_price`, `total_price`)
VALUES
(1, 'Gents Shalwar Kameez (Boski Silk)', 'male_shalwar_kameez', 1, 2200.00, 2200.00),
(1, 'Gents Shalwar Kameez (Charcoal Gray)', 'male_shalwar_kameez', 1, 2200.00, 2200.00),
(2, 'Gents Shalwar Kameez (Sky Blue)', 'male_shalwar_kameez', 1, 2000.00, 2000.00),
(3, 'Ladies 3-Piece Designer Suit', 'female_shalwar_kameez', 1, 3200.00, 3200.00),
(4, 'Midnight Navy Wool Blazer', 'coat_blazer', 1, 8500.00, 8500.00),
(5, 'Festive Latha Shalwar Kameez (White)', 'male_shalwar_kameez', 2, 2200.00, 4400.00),
(5, 'Festive Latha Shalwar Kameez (Cream)', 'male_shalwar_kameez', 1, 2200.00, 2200.00),
(6, 'Formal Oxford Shirt (White)', 'shirt', 1, 1400.00, 1400.00),
(6, 'Formal Oxford Shirt (Powder Blue)', 'shirt', 1, 1400.00, 1400.00),
(7, 'Saudi Pure White Jubba', 'thobe_jubba', 2, 3500.00, 7000.00),
(8, 'Sapphire Winter Linen Kurti & Trouser', 'female_shalwar_kameez', 2, 2800.00, 5600.00),
(9, 'Raw Silk Jamawar Waistcoat', 'waistcoat', 1, 3500.00, 3500.00),
(10, 'Dress Chino Trousers (Khaki & Navy)', 'pant', 2, 1500.00, 3000.00)
ON DUPLICATE KEY UPDATE `id`=`id`;

-- Sample Payments
INSERT INTO `payments` (`id`, `order_id`, `customer_id`, `amount`, `payment_method`, `transaction_ref`, `notes`, `payment_date`)
VALUES
(1, 1, 1, 2000.00, 'Cash', NULL, 'Advance booking deposit', '2026-10-18'),
(2, 2, 2, 2000.00, 'JazzCash', 'JC-99281726', 'Full payment at booking time', '2026-10-16'),
(3, 3, 3, 1500.00, 'Bank Transfer', 'MZN-7729112', '50% advance for ladies suit', '2026-10-20'),
(4, 4, 4, 5000.00, 'Cash', NULL, 'Advance for wool blazer', '2026-10-15'),
(5, 5, 5, 3000.00, 'EasyPaisa', 'EP-3382910', 'Advance for 3 suits', '2026-10-21'),
(6, 6, 6, 2800.00, 'Cash', NULL, 'Full payment on delivery', '2026-10-17'),
(7, 7, 7, 3000.00, 'Cash', NULL, 'Advance for 2 Jubbas', '2026-10-19'),
(8, 8, 8, 1600.00, 'Cash', NULL, 'Advance for 2 linen suits', '2026-10-22'),
(9, 9, 1, 3500.00, 'Cash', NULL, 'Full payment for raw silk waistcoat', '2026-10-17')
ON DUPLICATE KEY UPDATE `id`=`id`;

-- Sample Ledger Entries
INSERT INTO `ledger` (`id`, `type`, `category`, `title`, `amount`, `date`, `reference_type`, `reference_id`, `notes`)
VALUES
(1, 'income', 'Shop Order', 'Advance received for Order ORD-2026-001 (Chaudhry Bilal)', 2000.00, '2026-10-18', 'order', 1, 'Cash payment'),
(2, 'income', 'Shop Order', 'Full payment for Order ORD-2026-002 (Farhan Sheikh)', 2000.00, '2026-10-16', 'order', 2, 'JazzCash'),
(3, 'income', 'Shop Order', 'Advance for Order ORD-2026-003 (Mrs. Ayesha Malik)', 1500.00, '2026-10-20', 'order', 3, 'Bank transfer'),
(4, 'income', 'Shop Order', 'Advance for Order ORD-2026-004 (Zain Ul Abideen)', 5000.00, '2026-10-15', 'order', 4, 'Cash payment'),
(5, 'income', 'Shop Order', 'Advance for Order ORD-2026-005 (Hamza Tariq)', 3000.00, '2026-10-21', 'order', 5, 'EasyPaisa'),
(6, 'income', 'Shop Order', 'Delivery balance collected for Order ORD-2026-006 (Dr. Tariq)', 2800.00, '2026-10-17', 'order', 6, 'Cash on delivery'),
(7, 'income', 'Shop Order', 'Advance for Order ORD-2026-007 (Syed Ali Raza)', 3000.00, '2026-10-19', 'order', 7, 'Cash payment'),
(8, 'income', 'Shop Order', 'Advance for Order ORD-2026-008 (Mrs. Sania Imran)', 1600.00, '2026-10-22', 'order', 8, 'Cash payment'),
(9, 'income', 'Shop Order', 'Full payment for Order ORD-2026-009 (Waistcoat)', 3500.00, '2026-10-17', 'order', 9, 'Cash payment'),
(10, 'income', 'Sale', 'Sale of spare German interlining buckram rolls and mother-of-pearl buttons', 1850.00, '2026-10-21', 'general', NULL, 'Retail counter cash sale'),

(11, 'expense', 'Rent', 'Monthly Shop Rental Payment - Pace Shopping Mall Gulberg', 18000.00, '2026-10-01', 'general', NULL, 'Paid by Cheque #49281'),
(12, 'expense', 'Utility', 'LESCO Commercial Electricity Bill for September', 6450.00, '2026-10-08', 'general', NULL, 'Paid via JazzCash'),
(13, 'expense', 'Fabric/Material', 'Wholesale Purchase of Thread Cones, Buckram (بکرم), Zippers & Lining Silk', 4200.00, '2026-10-14', 'general', NULL, 'Purchased from Shahalam Market'),
(14, 'expense', 'Salaries', 'Weekly Advance Piece-Rate Payment to Master Rashid & Stitchers', 5500.00, '2026-10-18', 'worker', 1, 'Cash disbursed to workers'),
(15, 'expense', 'General', 'Daily Karigar Afternoon Tea, Fresh Bread & Refreshments', 650.00, '2026-10-21', 'general', NULL, 'Daily bakery & tea expense'),
(16, 'expense', 'General', 'Sewing Machine Maintenance & High-Speed Press Iron Servicing', 1200.00, '2026-10-12', 'general', NULL, 'Mechanic fee and oil lubrication')
ON DUPLICATE KEY UPDATE `id`=`id`;
