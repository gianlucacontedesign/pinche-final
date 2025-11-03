-- =============================================
-- DATABASE COMPLETO - PINCHE SUPPLIES E-COMMERCE
-- Sistema di e-commerce completo con amministrazione
-- Creato: 03 Nov 2025 - 21:54
-- 
-- Questo file crea tutte le tabelle necessarie per:
-- - Gestione prodotti e categorie
-- - Sistema ordini e carrello
-- - Gestione clienti e indirizzi
-- - Sistema di amministrazione
-- - Gestione sessioni e sicurezza
-- - Sistema di logging e audit
-- - Configurazioni sistema
-- =============================================

SET FOREIGN_KEY_CHECKS = 0;

-- =============================================
-- TABELLA: settings
-- Configurazioni generali del sistema
-- =============================================
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `setting_key` VARCHAR(100) NOT NULL UNIQUE COMMENT 'Chiave della configurazione',
    `setting_value` TEXT COMMENT 'Valore della configurazione',
    `setting_type` VARCHAR(50) DEFAULT 'string' COMMENT 'Tipo di dato (string, integer, decimal, boolean)',
    `description` VARCHAR(255) DEFAULT NULL COMMENT 'Descrizione della configurazione',
    `is_public` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Visibile pubblicamente',
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Ultima modifica',
    PRIMARY KEY (`id`),
    UNIQUE KEY `setting_key` (`setting_key`),
    KEY `idx_public` (`is_public`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Configurazioni generali sistema';

-- =============================================
-- TABELLA: categories
-- Categorie dei prodotti con supporto gerarchico
-- =============================================
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL COMMENT 'Nome della categoria',
    `slug` VARCHAR(255) NOT NULL UNIQUE COMMENT 'URL-friendly della categoria',
    `description` TEXT COMMENT 'Descrizione della categoria',
    `image` VARCHAR(500) DEFAULT NULL COMMENT 'Immagine della categoria',
    `parent_id` INT(11) DEFAULT NULL COMMENT 'ID categoria padre (gerarchia)',
    `sort_order` INT(11) DEFAULT 0 COMMENT 'Ordine di visualizzazione',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Categoria attiva/inattiva',
    `meta_title` VARCHAR(255) DEFAULT NULL COMMENT 'SEO: Titolo',
    `meta_description` VARCHAR(500) DEFAULT NULL COMMENT 'SEO: Descrizione',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data creazione',
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Ultima modifica',
    PRIMARY KEY (`id`),
    UNIQUE KEY `slug` (`slug`),
    KEY `idx_parent` (`parent_id`),
    KEY `idx_active` (`is_active`),
    KEY `idx_sort` (`sort_order`),
    CONSTRAINT `fk_categories_parent` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Categorie prodotti con supporto gerarchico';

-- =============================================
-- TABELLA: products
-- Prodotti principali del catalogo
-- =============================================
DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL COMMENT 'Nome del prodotto',
    `slug` VARCHAR(255) NOT NULL UNIQUE COMMENT 'URL-friendly del prodotto',
    `sku` VARCHAR(100) DEFAULT NULL UNIQUE COMMENT 'Codice SKU del prodotto',
    `description` TEXT COMMENT 'Descrizione completa del prodotto',
    `short_description` VARCHAR(500) DEFAULT NULL COMMENT 'Descrizione breve',
    `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Prezzo di vendita',
    `original_price` DECIMAL(10,2) DEFAULT NULL COMMENT 'Prezzo originale (sconti)',
    `cost_price` DECIMAL(10,2) DEFAULT NULL COMMENT 'Prezzo di costo',
    `discount_percentage` INT(3) DEFAULT 0 COMMENT 'Percentuale sconto (0-100)',
    `stock_quantity` INT(11) NOT NULL DEFAULT 0 COMMENT 'Quantità in stock',
    `min_stock` INT(11) DEFAULT 5 COMMENT 'Stock minimo per allerta',
    `weight` DECIMAL(8,2) DEFAULT 0.00 COMMENT 'Peso in chilogrammi',
    `dimensions_length` DECIMAL(8,2) DEFAULT NULL COMMENT 'Lunghezza (cm)',
    `dimensions_width` DECIMAL(8,2) DEFAULT NULL COMMENT 'Larghezza (cm)',
    `dimensions_height` DECIMAL(8,2) DEFAULT NULL COMMENT 'Altezza (cm)',
    `category_id` INT(11) NOT NULL COMMENT 'ID della categoria',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Prodotto attivo/inattivo',
    `is_featured` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Prodotto in evidenza',
    `is_new` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Prodotto nuovo',
    `is_digital` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Prodotto digitale',
    `requires_shipping` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Richiede spedizione',
    `sales_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'Quantità venduta',
    `views_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'Numero visualizzazioni',
    `rating_average` DECIMAL(3,2) DEFAULT 0.00 COMMENT 'Valutazione media (0-5)',
    `rating_count` INT(11) DEFAULT 0 COMMENT 'Numero valutazioni',
    `meta_title` VARCHAR(255) DEFAULT NULL COMMENT 'SEO: Titolo',
    `meta_description` VARCHAR(500) DEFAULT NULL COMMENT 'SEO: Descrizione',
    `meta_keywords` VARCHAR(500) DEFAULT NULL COMMENT 'SEO: Parole chiave',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data creazione',
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Ultima modifica',
    `is_deleted` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Eliminato (soft delete)',
    `deleted_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Data eliminazione',
    PRIMARY KEY (`id`),
    UNIQUE KEY `slug` (`slug`),
    UNIQUE KEY `sku` (`sku`),
    KEY `idx_category` (`category_id`),
    KEY `idx_active` (`is_active`),
    KEY `idx_featured` (`is_featured`),
    KEY `idx_price` (`price`),
    KEY `idx_stock` (`stock_quantity`),
    KEY `idx_sales` (`sales_count`),
    KEY `idx_deleted` (`is_deleted`),
    KEY `idx_category_active` (`category_id`, `is_active`),
    KEY `idx_featured_active` (`is_featured`, `is_active`),
    KEY `idx_price_stock` (`price`, `stock_quantity`),
    CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Prodotti principali del catalogo';

-- =============================================
-- TABELLA: product_images
-- Immagini dei prodotti con supporto multiple
-- =============================================
DROP TABLE IF EXISTS `product_images`;
CREATE TABLE `product_images` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `product_id` INT(11) NOT NULL COMMENT 'ID del prodotto',
    `image_path` VARCHAR(500) NOT NULL COMMENT 'Percorso dell\'immagine',
    `alt_text` VARCHAR(255) DEFAULT NULL COMMENT 'Testo alternativo',
    `caption` VARCHAR(255) DEFAULT NULL COMMENT 'Didascalia',
    `is_primary` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Immagine principale',
    `sort_order` INT(11) NOT NULL DEFAULT 0 COMMENT 'Ordine di visualizzazione',
    `file_size` INT(11) DEFAULT NULL COMMENT 'Dimensione file in bytes',
    `mime_type` VARCHAR(100) DEFAULT NULL COMMENT 'Tipo MIME del file',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data creazione',
    PRIMARY KEY (`id`),
    KEY `idx_product` (`product_id`),
    KEY `idx_primary` (`is_primary`),
    KEY `idx_sort` (`sort_order`),
    CONSTRAINT `fk_product_images_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Immagini multiple dei prodotti';

-- =============================================
-- TABELLA: product_variants
-- Varianti dei prodotti (taglie, colori, etc.)
-- =============================================
DROP TABLE IF EXISTS `product_variants`;
CREATE TABLE `product_variants` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `product_id` INT(11) NOT NULL COMMENT 'ID del prodotto',
    `variant_name` VARCHAR(100) NOT NULL COMMENT 'Nome della variante (Taglia, Colore, etc.)',
    `variant_value` VARCHAR(100) NOT NULL COMMENT 'Valore della variante (XL, Rosso, etc.)',
    `sku_suffix` VARCHAR(50) DEFAULT NULL COMMENT 'Suffisso SKU per questa variante',
    `price_modifier` DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Modificatore prezzo (+ o -)',
    `stock_modifier` INT(11) DEFAULT 0 COMMENT 'Modificatore stock',
    `weight_modifier` DECIMAL(8,2) DEFAULT 0.00 COMMENT 'Modificatore peso',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Variante attiva/inattiva',
    `sort_order` INT(11) NOT NULL DEFAULT 0 COMMENT 'Ordine di visualizzazione',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data creazione',
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Ultima modifica',
    PRIMARY KEY (`id`),
    KEY `idx_product` (`product_id`),
    KEY `idx_active` (`is_active`),
    KEY `idx_sort` (`sort_order`),
    CONSTRAINT `fk_product_variants_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Varianti dei prodotti';

-- =============================================
-- TABELLA: customers
-- Clienti registrati nel sistema
-- =============================================
DROP TABLE IF EXISTS `customers`;
CREATE TABLE `customers` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `email` VARCHAR(255) NOT NULL UNIQUE COMMENT 'Email del cliente (unica)',
    `password_hash` VARCHAR(255) NOT NULL COMMENT 'Hash della password',
    `first_name` VARCHAR(100) NOT NULL COMMENT 'Nome',
    `last_name` VARCHAR(100) NOT NULL COMMENT 'Cognome',
    `phone` VARCHAR(50) DEFAULT NULL COMMENT 'Telefono',
    `date_of_birth` DATE DEFAULT NULL COMMENT 'Data di nascita',
    `gender` ENUM('male', 'female', 'other', 'prefer_not_to_say') DEFAULT NULL COMMENT 'Genere',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Account attivo/inattivo',
    `is_verified` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Email verificata/non verificata',
    `verification_token` VARCHAR(255) DEFAULT NULL COMMENT 'Token di verifica email',
    `verification_expires` TIMESTAMP NULL DEFAULT NULL COMMENT 'Scadenza token verifica',
    `reset_token` VARCHAR(255) DEFAULT NULL COMMENT 'Token reset password',
    `reset_expires` TIMESTAMP NULL DEFAULT NULL COMMENT 'Scadenza token reset',
    `last_login` TIMESTAMP NULL DEFAULT NULL COMMENT 'Ultimo accesso',
    `login_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'Numero di accessi',
    `total_orders` INT(11) NOT NULL DEFAULT 0 COMMENT 'Numero ordini totali',
    `total_spent` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Totale speso',
    `average_order_value` DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Valore medio ordine',
    `notes` TEXT COMMENT 'Note interne',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data creazione',
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Ultima modifica',
    PRIMARY KEY (`id`),
    UNIQUE KEY `email` (`email`),
    KEY `idx_active` (`is_active`),
    KEY `idx_verified` (`is_verified`),
    KEY `idx_last_login` (`last_login`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Clienti registrati';

-- =============================================
-- TABELLA: customer_addresses
-- Indirizzi dei clienti
-- =============================================
DROP TABLE IF EXISTS `customer_addresses`;
CREATE TABLE `customer_addresses` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `customer_id` INT(11) NOT NULL COMMENT 'ID del cliente',
    `type` ENUM('shipping', 'billing') NOT NULL DEFAULT 'shipping' COMMENT 'Tipo indirizzo',
    `label` VARCHAR(100) DEFAULT NULL COMMENT 'Etichetta (Casa, Ufficio, etc.)',
    `first_name` VARCHAR(100) NOT NULL COMMENT 'Nome',
    `last_name` VARCHAR(100) NOT NULL COMMENT 'Cognome',
    `company` VARCHAR(100) DEFAULT NULL COMMENT 'Azienda',
    `address_line_1` VARCHAR(255) NOT NULL COMMENT 'Indirizzo riga 1',
    `address_line_2` VARCHAR(255) DEFAULT NULL COMMENT 'Indirizzo riga 2',
    `city` VARCHAR(100) NOT NULL COMMENT 'Città',
    `state_province` VARCHAR(100) DEFAULT NULL COMMENT 'Stato/Provincia',
    `postal_code` VARCHAR(20) NOT NULL COMMENT 'Codice postale',
    `country` VARCHAR(100) NOT NULL DEFAULT 'Italia' COMMENT 'Paese',
    `phone` VARCHAR(50) DEFAULT NULL COMMENT 'Telefono',
    `is_default` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Indirizzo predefinito',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data creazione',
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Ultima modifica',
    PRIMARY KEY (`id`),
    KEY `idx_customer` (`customer_id`),
    KEY `idx_type` (`type`),
    KEY `idx_default` (`is_default`),
    CONSTRAINT `fk_customer_addresses_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Indirizzi dei clienti';

-- =============================================
-- TABELLA: orders
-- Ordini di acquisto
-- =============================================
DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `order_number` VARCHAR(50) NOT NULL UNIQUE COMMENT 'Numero ordine univoco',
    `customer_id` INT(11) DEFAULT NULL COMMENT 'ID cliente (NULL per ospiti)',
    `customer_email` VARCHAR(255) NOT NULL COMMENT 'Email del cliente',
    `customer_name` VARCHAR(255) NOT NULL COMMENT 'Nome completo cliente',
    `customer_phone` VARCHAR(50) DEFAULT NULL COMMENT 'Telefono cliente',
    `subtotal` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Subtotale senza tasse e spedizione',
    `tax_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Importo tasse',
    `shipping_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Costo spedizione',
    `discount_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Importo sconti',
    `total_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Totale finale',
    `tax_rate` DECIMAL(5,2) DEFAULT 22.00 COMMENT 'Aliquota IVA applicata',
    `currency` VARCHAR(3) DEFAULT 'EUR' COMMENT 'Valuta',
    `exchange_rate` DECIMAL(10,6) DEFAULT 1.000000 COMMENT 'Tasso di cambio',
    `shipping_method` VARCHAR(100) DEFAULT NULL COMMENT 'Metodo di spedizione',
    `shipping_tracking` VARCHAR(255) DEFAULT NULL COMMENT 'Tracking spedizione',
    `payment_method` VARCHAR(100) DEFAULT NULL COMMENT 'Metodo di pagamento',
    `payment_transaction_id` VARCHAR(255) DEFAULT NULL COMMENT 'ID transazione pagamento',
    `payment_status` VARCHAR(50) NOT NULL DEFAULT 'pending' COMMENT 'Stato pagamento',
    `order_status` VARCHAR(50) NOT NULL DEFAULT 'pending' COMMENT 'Stato ordine',
    `fulfillment_status` VARCHAR(50) DEFAULT 'unfulfilled' COMMENT 'Stato evasione',
    `shipping_address` TEXT COMMENT 'Indirizzo spedizione completo',
    `billing_address` TEXT COMMENT 'Indirizzo fatturazione completo',
    `notes` TEXT COMMENT 'Note aggiuntive',
    `admin_notes` TEXT COMMENT 'Note amministrative interne',
    `coupon_code` VARCHAR(50) DEFAULT NULL COMMENT 'Codice coupon utilizzato',
    `referrer` VARCHAR(255) DEFAULT NULL COMMENT 'Referrer dell\'ordine',
    `ip_address` VARCHAR(45) DEFAULT NULL COMMENT 'IP dell\'ordine',
    `user_agent` TEXT DEFAULT NULL COMMENT 'User agent del browser',
    `processed_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Data elaborazione',
    `shipped_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Data spedizione',
    `delivered_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Data consegna',
    `cancelled_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Data annullamento',
    `cancelled_reason` VARCHAR(255) DEFAULT NULL COMMENT 'Motivo annullamento',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data creazione',
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Ultima modifica',
    PRIMARY KEY (`id`),
    UNIQUE KEY `order_number` (`order_number`),
    KEY `idx_customer` (`customer_id`),
    KEY `idx_email` (`customer_email`),
    KEY `idx_status` (`order_status`),
    KEY `idx_payment` (`payment_status`),
    KEY `idx_fulfillment` (`fulfillment_status`),
    KEY `idx_created` (`created_at`),
    KEY `idx_cancelled` (`cancelled_at`),
    CONSTRAINT `fk_orders_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Ordini di acquisto';

-- =============================================
-- TABELLA: order_items
-- Elementi degli ordini
-- =============================================
DROP TABLE IF EXISTS `order_items`;
CREATE TABLE `order_items` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `order_id` INT(11) NOT NULL COMMENT 'ID dell\'ordine',
    `product_id` INT(11) NOT NULL COMMENT 'ID del prodotto',
    `variant_id` INT(11) DEFAULT NULL COMMENT 'ID della variante',
    `product_name` VARCHAR(255) NOT NULL COMMENT 'Nome prodotto al momento dell\'ordine',
    `product_sku` VARCHAR(100) DEFAULT NULL COMMENT 'SKU prodotto al momento dell\'ordine',
    `variant_name` VARCHAR(100) DEFAULT NULL COMMENT 'Nome variante',
    `variant_value` VARCHAR(100) DEFAULT NULL COMMENT 'Valore variante',
    `quantity` INT(11) NOT NULL COMMENT 'Quantità ordinata',
    `unit_price` DECIMAL(10,2) NOT NULL COMMENT 'Prezzo unitario al momento dell\'ordine',
    `total_price` DECIMAL(10,2) NOT NULL COMMENT 'Prezzo totale (unit_price * quantity)',
    `discount_amount` DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Sconto applicato',
    `tax_rate` DECIMAL(5,2) DEFAULT 22.00 COMMENT 'Aliquota IVA',
    `tax_amount` DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Importo IVA',
    `notes` VARCHAR(500) DEFAULT NULL COMMENT 'Note sull\'elemento',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data creazione',
    PRIMARY KEY (`id`),
    KEY `idx_order` (`order_id`),
    KEY `idx_product` (`product_id`),
    KEY `idx_variant` (`variant_id`),
    CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_order_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
    CONSTRAINT `fk_order_items_variant` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Elementi degli ordini';

-- =============================================
-- TABELLA: admin_users
-- Utenti amministratori del sistema
-- =============================================
DROP TABLE IF EXISTS `admin_users`;
CREATE TABLE `admin_users` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `username` VARCHAR(100) NOT NULL UNIQUE COMMENT 'Nome utente amministratore',
    `email` VARCHAR(255) NOT NULL UNIQUE COMMENT 'Email amministratore',
    `password_hash` VARCHAR(255) NOT NULL COMMENT 'Hash password',
    `first_name` VARCHAR(100) NOT NULL COMMENT 'Nome',
    `last_name` VARCHAR(100) NOT NULL COMMENT 'Cognome',
    `role` ENUM('super_admin', 'admin', 'manager', 'editor') NOT NULL DEFAULT 'admin' COMMENT 'Ruolo amministrativo',
    `permissions` JSON DEFAULT NULL COMMENT 'Permessi specifici in formato JSON',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Account attivo/inattivo',
    `last_login` TIMESTAMP NULL DEFAULT NULL COMMENT 'Ultimo accesso',
    `login_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'Numero accessi',
    `reset_token` VARCHAR(255) DEFAULT NULL COMMENT 'Token reset password',
    `reset_expires` TIMESTAMP NULL DEFAULT NULL COMMENT 'Scadenza token reset',
    `two_factor_secret` VARCHAR(32) DEFAULT NULL COMMENT 'Secret 2FA',
    `two_factor_enabled` TINYINT(1) DEFAULT 0 COMMENT '2FA abilitato',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data creazione',
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Ultima modifica',
    `created_by` INT(11) DEFAULT NULL COMMENT 'ID creatore (amministratore)',
    PRIMARY KEY (`id`),
    UNIQUE KEY `username` (`username`),
    UNIQUE KEY `email` (`email`),
    KEY `idx_active` (`is_active`),
    KEY `idx_role` (`role`),
    KEY `idx_last_login` (`last_login`),
    CONSTRAINT `fk_admin_users_creator` FOREIGN KEY (`created_by`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Utenti amministratori del sistema';

-- =============================================
-- TABELLA: sessions
-- Sessioni utenti (clienti e amministratori)
-- =============================================
DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
    `id` VARCHAR(128) NOT NULL COMMENT 'ID sessione (128 caratteri)',
    `user_id` INT(11) DEFAULT NULL COMMENT 'ID utente (NULL per ospiti)',
    `user_type` ENUM('customer', 'admin') NOT NULL DEFAULT 'customer' COMMENT 'Tipo utente',
    `ip_address` VARCHAR(45) DEFAULT NULL COMMENT 'Indirizzo IP',
    `user_agent` TEXT DEFAULT NULL COMMENT 'User agent del browser',
    `payload` LONGTEXT NOT NULL COMMENT 'Dati della sessione serializzati',
    `last_activity` INT(11) NOT NULL COMMENT 'Ultima attività (timestamp)',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data creazione',
    PRIMARY KEY (`id`),
    KEY `idx_user` (`user_id`, `user_type`),
    KEY `idx_last_activity` (`last_activity`),
    KEY `idx_user_type` (`user_type`),
    CONSTRAINT `fk_sessions_customer` FOREIGN KEY (`user_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_sessions_admin` FOREIGN KEY (`user_id`) REFERENCES `admin_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Sessioni utenti';

-- =============================================
-- TABELLA: system_logs
-- Log del sistema per audit e debug
-- =============================================
DROP TABLE IF EXISTS `system_logs`;
CREATE TABLE `system_logs` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `level` ENUM('debug', 'info', 'notice', 'warning', 'error', 'critical', 'alert', 'emergency') NOT NULL COMMENT 'Livello del log',
    `message` TEXT NOT NULL COMMENT 'Messaggio del log',
    `context` JSON DEFAULT NULL COMMENT 'Contesto aggiuntivo in JSON',
    `user_id` INT(11) DEFAULT NULL COMMENT 'ID utente coinvolto',
    `user_type` ENUM('customer', 'admin', 'system') DEFAULT 'system' COMMENT 'Tipo utente',
    `action` VARCHAR(100) DEFAULT NULL COMMENT 'Azione compiuta',
    `resource_type` VARCHAR(50) DEFAULT NULL COMMENT 'Tipo risorsa coinvolta',
    `resource_id` INT(11) DEFAULT NULL COMMENT 'ID risorsa coinvolta',
    `ip_address` VARCHAR(45) DEFAULT NULL COMMENT 'Indirizzo IP',
    `user_agent` VARCHAR(500) DEFAULT NULL COMMENT 'User agent',
    `request_uri` VARCHAR(500) DEFAULT NULL COMMENT 'URI richiesta',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data creazione',
    PRIMARY KEY (`id`),
    KEY `idx_level` (`level`),
    KEY `idx_user` (`user_id`, `user_type`),
    KEY `idx_action` (`action`),
    KEY `idx_resource` (`resource_type`, `resource_id`),
    KEY `idx_created` (`created_at`),
    CONSTRAINT `fk_logs_customer` FOREIGN KEY (`user_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_logs_admin` FOREIGN KEY (`user_id`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Log del sistema per audit';

-- =============================================
-- TABELLA: cart_items
-- Elementi del carrello (per utenti non registrati)
-- =============================================
DROP TABLE IF EXISTS `cart_items`;
CREATE TABLE `cart_items` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `session_id` VARCHAR(128) NOT NULL COMMENT 'ID sessione',
    `customer_id` INT(11) DEFAULT NULL COMMENT 'ID cliente (se registrato)',
    `product_id` INT(11) NOT NULL COMMENT 'ID del prodotto',
    `variant_id` INT(11) DEFAULT NULL COMMENT 'ID della variante',
    `quantity` INT(11) NOT NULL DEFAULT 1 COMMENT 'Quantità',
    `unit_price` DECIMAL(10,2) NOT NULL COMMENT 'Prezzo unitario al momento dell\'aggiunta',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data aggiunta',
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Ultima modifica',
    PRIMARY KEY (`id`),
    KEY `idx_session` (`session_id`),
    KEY `idx_customer` (`customer_id`),
    KEY `idx_product` (`product_id`),
    KEY `idx_variant` (`variant_id`),
    CONSTRAINT `fk_cart_items_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_cart_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_cart_items_variant` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Elementi del carrello';

-- =============================================
-- TABELLA: product_reviews
-- Recensioni prodotti
-- =============================================
DROP TABLE IF EXISTS `product_reviews`;
CREATE TABLE `product_reviews` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `product_id` INT(11) NOT NULL COMMENT 'ID del prodotto',
    `customer_id` INT(11) DEFAULT NULL COMMENT 'ID cliente (NULL per recensioni anonime)',
    `order_id` INT(11) DEFAULT NULL COMMENT 'ID ordine (verifica acquisto)',
    `rating` INT(1) NOT NULL COMMENT 'Valutazione (1-5)',
    `title` VARCHAR(255) DEFAULT NULL COMMENT 'Titolo della recensione',
    `comment` TEXT COMMENT 'Commento della recensione',
    `pros` TEXT COMMENT 'Pro (opzionale)',
    `cons` TEXT COMMENT 'Contro (opzionale)',
    `is_verified_purchase` TINYINT(1) DEFAULT 0 COMMENT 'Acquisto verificato',
    `is_approved` TINYINT(1) DEFAULT 0 COMMENT 'Approvata dall\'admin',
    `is_featured` TINYINT(1) DEFAULT 0 COMMENT 'In evidenza',
    `helpful_count` INT(11) DEFAULT 0 COMMENT 'Numero "utile"',
    `reported_count` INT(11) DEFAULT 0 COMMENT 'Numero segnalazioni',
    `moderated_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Data moderazione',
    `moderated_by` INT(11) DEFAULT NULL COMMENT 'ID moderatore',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data creazione',
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Ultima modifica',
    PRIMARY KEY (`id`),
    KEY `idx_product` (`product_id`),
    KEY `idx_customer` (`customer_id`),
    KEY `idx_order` (`order_id`),
    KEY `idx_rating` (`rating`),
    KEY `idx_approved` (`is_approved`),
    KEY `idx_featured` (`is_featured`),
    KEY `idx_created` (`created_at`),
    CONSTRAINT `fk_reviews_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_reviews_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_reviews_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_reviews_moderator` FOREIGN KEY (`moderated_by`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Recensioni dei prodotti';

-- =============================================
-- TABELLA: coupons
-- Codici sconto e coupon
-- =============================================
DROP TABLE IF EXISTS `coupons`;
CREATE TABLE `coupons` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `code` VARCHAR(50) NOT NULL UNIQUE COMMENT 'Codice coupon',
    `type` ENUM('percentage', 'fixed', 'free_shipping') NOT NULL COMMENT 'Tipo sconto',
    `value` DECIMAL(10,2) NOT NULL COMMENT 'Valore dello sconto',
    `min_order_amount` DECIMAL(10,2) DEFAULT NULL COMMENT 'Ordine minimo',
    `max_discount_amount` DECIMAL(10,2) DEFAULT NULL COMMENT 'Sconto massimo',
    `usage_limit` INT(11) DEFAULT NULL COMMENT 'Limite utilizzi totali',
    `usage_limit_per_customer` INT(11) DEFAULT 1 COMMENT 'Limite per cliente',
    `used_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'Utilizzi effettuati',
    `valid_from` TIMESTAMP NOT NULL COMMENT 'Data validità inizio',
    `valid_until` TIMESTAMP NOT NULL COMMENT 'Data validità fine',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Attivo/inattivo',
    `applicable_categories` JSON DEFAULT NULL COMMENT 'Categorie applicabili',
    `applicable_products` JSON DEFAULT NULL COMMENT 'Prodotti applicabili',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data creazione',
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Ultima modifica',
    PRIMARY KEY (`id`),
    UNIQUE KEY `code` (`code`),
    KEY `idx_active` (`is_active`),
    KEY `idx_validity` (`valid_from`, `valid_until`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Codici sconto e coupon';

-- =============================================
-- INSERIMENTO DATI CONFIGURAZIONE INIZIALE
-- =============================================

INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_type`, `description`, `is_public`) VALUES
-- Configurazioni General Store
('store_name', 'Pinche Supplies', 'string', 'Nome del negozio', 1),
('store_tagline', 'Materiali Professionali per Tatuaggi', 'string', 'Slogan del negozio', 1),
('store_description', 'Fornitore di materiali professionali per tatuaggi di alta qualità', 'string', 'Descrizione del negozio', 1),
('store_email', 'info@pinchesupplies.com', 'string', 'Email principale del negozio', 0),
('store_phone', '+39 02 1234 5678', 'string', 'Telefono principale del negozio', 1),
('store_address', 'Milano, Italia', 'string', 'Indirizzo del negozio', 1),
('store_business_hours', 'Lun-Ven 9:00-18:00, Sab 9:00-13:00', 'string', 'Orari di apertura', 1),
('store_currency', 'EUR', 'string', 'Valuta principale', 1),
('store_timezone', 'Europe/Rome', 'string', 'Fuso orario del negozio', 0),

-- Configurazioni Prezzi e Tasse
('tax_rate', '22', 'decimal', 'Aliquota IVA (%)', 1),
('tax_included_in_prices', '1', 'boolean', 'IVA inclusa nei prezzi', 1),
('shipping_cost', '9.99', 'decimal', 'Costo spedizione standard', 1),
('free_shipping_threshold', '50.00', 'decimal', 'Spedizione gratuita sopra a', 1),
('express_shipping_cost', '19.99', 'decimal', 'Costo spedizione express', 1),

-- Configurazioni Stock
('low_stock_threshold', '5', 'integer', 'Soglia stock basso', 0),
('critical_stock_threshold', '2', 'integer', 'Soglia stock critico', 0),
('out_of_stock_behavior', 'hide', 'string', 'Comportamento prodotti esauriti (hide/show/disable)', 1),
('stock_management', '1', 'boolean', 'Gestione stock abilitata', 0),

-- Configurazioni Ordini
('order_number_prefix', 'PS', 'string', 'Prefisso numero ordine', 0),
('order_number_padding', '6', 'integer', 'Zeri di riempimento numero ordine', 0),
('order_auto_process', '0', 'boolean', 'Elaborazione automatica ordini', 0),
('order_confirmation_email', '1', 'boolean', 'Email conferma ordine', 1),
('order_statuses', 'pending,processing,shipped,delivered,cancelled,refunded', 'string', 'Stati ordine disponibili', 0),

-- Configurazioni Pagamenti
('payment_methods', 'credit_card,paypal,bank_transfer,cash_on_delivery', 'string', 'Metodi di pagamento accettati', 1),
('default_payment_method', 'credit_card', 'string', 'Metodo di pagamento predefinito', 1),
('payment_test_mode', '1', 'boolean', 'Modalità test pagamenti', 0),
('stripe_publishable_key', '', 'string', 'Chiave pubblica Stripe', 0),
('stripe_secret_key', '', 'string', 'Chiave segreta Stripe', 0),

-- Configurazioni SEO
('seo_home_title', 'Pinche Supplies - Materiali Professionali per Tatuaggi', 'string', 'Titolo SEO homepage', 1),
('seo_home_description', 'Scopri la nostra selezione di materiali professionali per tatuaggi di alta qualità', 'string', 'Descrizione SEO homepage', 1),
('seo_home_keywords', 'tatuaggi, materiali, aghi, inchiostri, macchine', 'string', 'Keywords SEO homepage', 1),
('seo_robots', 'index, follow', 'string', 'Direttive robots SEO', 1),
('seo_google_analytics', '', 'string', 'ID Google Analytics', 0),
('seo_google_search_console', '', 'string', 'Verifica Google Search Console', 0),

-- Configurazioni Email
('email_smtp_host', 'smtp.gmail.com', 'string', 'Server SMTP', 0),
('email_smtp_port', '587', 'integer', 'Porta SMTP', 0),
('email_smtp_username', '', 'string', 'Username SMTP', 0),
('email_smtp_password', '', 'string', 'Password SMTP', 0),
('email_from_name', 'Pinche Supplies', 'string', 'Nome mittente email', 1),
('email_from_address', 'noreply@pinchesupplies.com', 'string', 'Indirizzo mittente email', 0),
('email_order_notifications', '1', 'boolean', 'Notifiche email ordini', 0),
('email_low_stock_alerts', '1', 'boolean', 'Alert email stock basso', 0),

-- Configurazioni Sicurezza
('session_lifetime', '120', 'integer', 'Durata sessione in minuti', 0),
('max_login_attempts', '5', 'integer', 'Max tentativi login falliti', 0),
('lockout_duration', '15', 'integer', 'Durata blocco in minuti', 0),
('password_min_length', '8', 'integer', 'Lunghezza minima password', 0),
('require_email_verification', '1', 'boolean', 'Verifica email obbligatoria', 1),
('two_factor_auth', '0', 'boolean', 'Autenticazione a due fattori', 0),

-- Configurazioni Performance
('cache_enabled', '1', 'boolean', 'Cache abilitata', 0),
('cache_duration', '3600', 'integer', 'Durata cache in secondi', 0),
('image_optimization', '1', 'boolean', 'Ottimizzazione immagini', 0),
('lazy_loading', '1', 'boolean', 'Caricamento lazy immagini', 1),
('cdn_enabled', '0', 'boolean', 'CDN abilitato', 0),
('cdn_url', '', 'string', 'URL CDN', 0),

-- Configurazioni Backup
('auto_backup', '1', 'boolean', 'Backup automatico', 0),
('backup_frequency', 'daily', 'string', 'Frequenza backup (daily/weekly)', 0),
('backup_retention', '30', 'integer', 'Giorni conservazione backup', 0),
('backup_email_notifications', '1', 'boolean', 'Notifiche email backup', 0),

-- Configurazioni Analytics
('track_conversions', '1', 'boolean', 'Tracciamento conversioni', 0),
('track_abandoned_carts', '1', 'boolean', 'Tracciamento carrelli abbandonati', 0),
('abandoned_cart_timeout', '24', 'integer', 'Timeout carrello abbandonato (ore)', 0);

-- =============================================
-- INSERIMENTO CATEGORIE INIZIALI
-- =============================================

INSERT INTO `categories` (`name`, `slug`, `description`, `is_active`, `sort_order`) VALUES
('Inchiostri', 'inchiostri', 'Inchiostri per tatuaggi di alta qualità', 1, 1),
('Aguje e Cartucce', 'aguglie-cartucce', 'Aguje e cartucce per tutte le tecniche', 1, 2),
('Macchine per Tatuaggi', 'macchine', 'Macchine rotative e a bobina professionali', 1, 3),
('Alimentatori', 'alimentatori', 'Alimentatori e power supplies', 1, 4),
('Guanti e Protezione', 'guanti-protezione', 'Guanti monouso e materiali di protezione', 1, 5),
('Igiene e Pulizia', 'igiene-pulizia', 'Prodotti per la pulizia e disinfezione', 1, 6),
('Accessori', 'accessori', 'Accessori e strumenti aggiuntivi', 1, 7),
('Stencil e Transfer', 'stencil-transfer', 'Prodotti per il transfer del design', 1, 8),
('Cura e Guarigione', 'cura-guarigione', 'Prodotti per la cura post-tatuaggio', 1, 9),
('Libri e Guide', 'libri-guide', 'Libri e guide professionali', 1, 10);

-- =============================================
-- INSERIMENTO PRODOTTI DI ESEMPIO
-- =============================================

INSERT INTO `products` (`name`, `slug`, `sku`, `description`, `price`, `stock_quantity`, `category_id`, `is_active`, `is_featured`, `is_new`) VALUES
('Inchiostro Nero Eternal Ink 30ml', 'inchiostro-nero-eternal-ink-30ml', 'ETN-BLK-30', 'Inchiostro nero di alta densità per contorni e shading professionali. Formula premium certificata.', 28.50, 25, 1, 1, 1, 1),
('Set Aguglie Round Liner RL 3 - 10 pezzi', 'set-aguglie-round-liner-rl3', 'RL3-SET-10', 'Set di 10 aguglie Round Liner RL 3 per linee precise e dettagli. Sterilizzate EO.', 15.90, 40, 2, 1, 1, 0),
('Macchina Rotativa Bishop Wand V6', 'macchina-rotativa-bishop-wand-v6', 'BIS-V6-WAND', 'Macchina rotativa Bishop Wand V6 con motore brushless. Design ergonomico professionale.', 449.00, 5, 3, 1, 1, 1),
('Alimentatore Digital Pro 3A', 'alimentatore-digital-pro-3a', 'DIG-PRO-3A', 'Alimentatore digitale professionale 3A con display LCD e controllo precisione.', 189.90, 12, 4, 1, 0, 0),
('Guanti Nitrilo Nitrilex - 100 pezzi', 'guanti-nitrilo-nitrilex-100', 'NIT-100-BOX', 'Guanti in nitrile senza polvere, resistenti. Taglia M. Box da 100 pezzi.', 8.50, 60, 5, 1, 0, 0),
('Soluzione Salina 500ml', 'soluzione-salina-500ml', 'SAL-500ML', 'Soluzione salina sterile per pulizia e igiene. Flacone da 500ml.', 12.90, 30, 6, 1, 0, 0),
('Supporto Macchina Acciaio Inox', 'supporto-macchina-acciaio-inox', 'SUP-MAQ-INO', 'Supporto universale per macchine in acciaio inossidabile. Regolabile e sterilizzabile.', 35.00, 15, 7, 1, 0, 0),
('Stencil Transfer Gel 100ml', 'stencil-transfer-gel-100ml', 'STG-100ML', 'Gel trasparente per transfer stencil. Formula professionale ad alta aderenza.', 16.50, 20, 8, 1, 0, 1),
('Crema Guarigione Tattoo Care 50ml', 'crema-guarigione-tattoo-care-50ml', 'THC-50ML', 'Crema специально formulata per la guarigione dei tatuaggi. Ingredienti naturali.', 18.90, 25, 9, 1, 0, 0),
('Guida Tatuaggio Professionale', 'guida-tatuaggio-professionale', 'GUI-PROF', 'Guida completa per tatuatori professionali. Tecniche, igiene, business.', 45.00, 8, 10, 1, 0, 0);

-- =============================================
-- INSERIMENTO IMMAGINI DI ESEMPIO
-- =============================================

INSERT INTO `product_images` (`product_id`, `image_path`, `alt_text`, `is_primary`, `sort_order`) VALUES
(1, 'products/inks/eternal-black-30ml.jpg', 'Inchiostro Nero Eternal Ink 30ml', 1, 1),
(2, 'products/needles/rl3-set-10pcs.jpg', 'Set Aguglie Round Liner RL 3', 1, 1),
(3, 'products/machines/bishop-v6-wand.jpg', 'Macchina Rotativa Bishop Wand V6', 1, 1),
(4, 'products/power/digital-pro-3a.jpg', 'Alimentatore Digital Pro 3A', 1, 1),
(5, 'products/safety/nitrilex-gloves-100.jpg', 'Guanti Nitrilo Nitrilex - 100 pezzi', 1, 1),
(6, 'products/hygiene/saline-solution-500ml.jpg', 'Soluzione Salina 500ml', 1, 1),
(7, 'products/accessories/steel-machine-holder.jpg', 'Supporto Macchina Acciaio Inox', 1, 1),
(8, 'products/stencil/stencil-gel-100ml.jpg', 'Stencil Transfer Gel 100ml', 1, 1),
(9, 'products/healing/tattoo-care-cream-50ml.jpg', 'Crema Guarigione Tattoo Care 50ml', 1, 1),
(10, 'products/books/professional-tattoo-guide.jpg', 'Guida Tatuaggio Professionale', 1, 1);

-- =============================================
-- INSERIMENTO UTENTE AMMINISTRATORE DEFAULT
-- =============================================

INSERT INTO `admin_users` (`username`, `email`, `password_hash`, `first_name`, `last_name`, `role`) VALUES
('admin', 'admin@pinchesupplies.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Amministratore', 'Sistema', 'super_admin');

-- =============================================
-- INDICI AGGIUNTIVI PER OTTIMIZZAZIONE
-- =============================================

-- Indici composti per query frequenti
ALTER TABLE `products` ADD INDEX `idx_category_active_featured` (`category_id`, `is_active`, `is_featured`);
ALTER TABLE `products` ADD INDEX `idx_search` (`name`, `slug`, `sku`);
ALTER TABLE `products` ADD INDEX `idx_stock_status` (`stock_quantity`, `is_active`, `is_deleted`);

ALTER TABLE `orders` ADD INDEX `idx_customer_status` (`customer_id`, `order_status`);
ALTER TABLE `orders` ADD INDEX `idx_date_status` (`created_at`, `order_status`);
ALTER TABLE `orders` ADD INDEX `idx_payment_status` (`payment_status`, `order_status`);

ALTER TABLE `customers` ADD INDEX `idx_name` (`first_name`, `last_name`);
ALTER TABLE `customers` ADD INDEX `idx_orders_spent` (`total_orders`, `total_spent`);

ALTER TABLE `system_logs` ADD INDEX `idx_level_created` (`level`, `created_at`);
ALTER TABLE `system_logs` ADD INDEX `idx_user_action` (`user_type`, `action`, `created_at`);

-- =============================================
-- TRIGGERS PER AUDIT E AUTOMAZIONE
-- =============================================

DELIMITER $$

-- Trigger per aggiornare conteggio vendite prodotto
CREATE TRIGGER `tr_update_product_sales_count` 
AFTER INSERT ON `order_items` 
FOR EACH ROW
BEGIN
    UPDATE products 
    SET sales_count = sales_count + NEW.quantity,
        updated_at = NOW()
    WHERE id = NEW.product_id;
END$$

-- Trigger per aggiornare statistiche cliente
CREATE TRIGGER `tr_update_customer_order_stats` 
AFTER INSERT ON `orders` 
FOR EACH ROW
BEGIN
    IF NEW.customer_id IS NOT NULL THEN
        UPDATE customers 
        SET total_orders = total_orders + 1,
            total_spent = total_spent + NEW.total_amount,
            average_order_value = total_spent / total_orders,
            last_login = NOW(),
            updated_at = NOW()
        WHERE id = NEW.customer_id;
    END IF;
END$$

-- Trigger per validare indirizzo predefinito
CREATE TRIGGER `tr_set_default_address` 
BEFORE INSERT ON `customer_addresses` 
FOR EACH ROW
BEGIN
    IF NEW.is_default = 1 THEN
        UPDATE customer_addresses 
        SET is_default = 0 
        WHERE customer_id = NEW.customer_id AND type = NEW.type AND id != NEW.id;
    END IF;
END$$

-- Trigger per aggiornare rating prodotto
CREATE TRIGGER `tr_update_product_rating` 
AFTER INSERT ON `product_reviews` 
FOR EACH ROW
BEGIN
    IF NEW.is_approved = 1 THEN
        UPDATE products p
        SET 
            rating_count = (SELECT COUNT(*) FROM product_reviews WHERE product_id = NEW.product_id AND is_approved = 1),
            rating_average = (SELECT AVG(rating) FROM product_reviews WHERE product_id = NEW.product_id AND is_approved = 1),
            updated_at = NOW()
        WHERE p.id = NEW.product_id;
    END IF;
END$$

-- Trigger per eliminazione soft dei prodotti
CREATE TRIGGER `tr_soft_delete_product` 
BEFORE DELETE ON `products` 
FOR EACH ROW
BEGIN
    UPDATE products 
    SET is_deleted = 1, 
        deleted_at = NOW(),
        updated_at = NOW()
    WHERE id = OLD.id;
END$$

DELIMITER ;

-- =============================================
-- VISTE UTILI PER REPORTING
-- =============================================

-- Vista prodotti con informazioni complete
CREATE VIEW `vw_products_complete` AS
SELECT 
    p.*,
    c.name as category_name,
    c.slug as category_slug,
    pi.image_path as primary_image,
    pi.alt_text as primary_image_alt,
    CASE 
        WHEN p.stock_quantity <= 0 THEN 'out_of_stock'
        WHEN p.stock_quantity <= (SELECT CAST(setting_value AS UNSIGNED) FROM settings WHERE setting_key = 'critical_stock_threshold') THEN 'critical_stock'
        WHEN p.stock_quantity <= (SELECT CAST(setting_value AS UNSIGNED) FROM settings WHERE setting_key = 'low_stock_threshold') THEN 'low_stock'
        ELSE 'in_stock'
    END as stock_status,
    COALESCE(ROUND((SELECT AVG(rating) FROM product_reviews WHERE product_id = p.id AND is_approved = 1), 2), 0) as average_rating,
    COALESCE((SELECT COUNT(*) FROM product_reviews WHERE product_id = p.id AND is_approved = 1), 0) as review_count
FROM products p
LEFT JOIN categories c ON p.category_id = c.id
LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
WHERE p.is_deleted = 0;

-- Vista ordini con dettagli cliente
CREATE VIEW `vw_orders_complete` AS
SELECT 
    o.*,
    CONCAT(c.first_name, ' ', c.last_name) as customer_full_name,
    c.email as customer_email,
    COUNT(oi.id) as items_count,
    SUM(oi.quantity) as total_items
FROM orders o
LEFT JOIN customers c ON o.customer_id = c.id
LEFT JOIN order_items oi ON o.id = oi.order_id
GROUP BY o.id;

-- Vista statistiche dashboard
CREATE VIEW `vw_dashboard_stats` AS
SELECT 
    (SELECT COUNT(*) FROM products WHERE is_active = 1 AND is_deleted = 0) as total_products,
    (SELECT COUNT(*) FROM categories WHERE is_active = 1) as total_categories,
    (SELECT COUNT(*) FROM customers WHERE is_active = 1) as total_customers,
    (SELECT COUNT(*) FROM orders WHERE DATE(created_at) = CURDATE()) as orders_today,
    (SELECT COUNT(*) FROM orders WHERE WEEK(created_at) = WEEK(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())) as orders_week,
    (SELECT COUNT(*) FROM orders WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())) as orders_month,
    (SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE order_status != 'cancelled' AND DATE(created_at) = CURDATE()) as revenue_today,
    (SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE order_status != 'cancelled' AND WEEK(created_at) = WEEK(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())) as revenue_week,
    (SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE order_status != 'cancelled' AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())) as revenue_month,
    (SELECT COUNT(*) FROM products WHERE stock_quantity <= (SELECT CAST(setting_value AS UNSIGNED) FROM settings WHERE setting_key = 'low_stock_threshold') AND is_active = 1 AND is_deleted = 0) as low_stock_products,
    (SELECT COUNT(*) FROM product_reviews WHERE is_approved = 0) as pending_reviews;

-- =============================================
-- PROCEDURE MEMORIZZATE
-- =============================================

DELIMITER $$

-- Procedura per aggiornare stock prodotto
CREATE PROCEDURE `UpdateProductStock`(
    IN p_product_id INT,
    IN p_quantity_change INT,
    IN p_operation ENUM('add', 'subtract', 'set')
)
BEGIN
    DECLARE current_stock INT;
    
    SELECT stock_quantity INTO current_stock FROM products WHERE id = p_product_id;
    
    IF p_operation = 'add' THEN
        UPDATE products SET stock_quantity = stock_quantity + p_quantity_change WHERE id = p_product_id;
    ELSEIF p_operation = 'subtract' THEN
        UPDATE products SET stock_quantity = GREATEST(0, stock_quantity - p_quantity_change) WHERE id = p_product_id;
    ELSEIF p_operation = 'set' THEN
        UPDATE products SET stock_quantity = GREATEST(0, p_quantity_change) WHERE id = p_product_id;
    END IF;
    
    -- Log dell'operazione
    INSERT INTO system_logs (`level`, `message`, `action`, `resource_type`, `resource_id`)
    VALUES ('info', CONCAT('Stock updated for product ID ', p_product_id, ': ', p_operation, ' ', p_quantity_change), 'update_stock', 'product', p_product_id);
END$$

-- Procedura per prodotti con stock basso
CREATE PROCEDURE `GetLowStockProducts`()
BEGIN
    SELECT 
        p.*,
        c.name as category_name,
        s.setting_value as low_stock_threshold
    FROM products p
    INNER JOIN categories c ON p.category_id = c.id
    CROSS JOIN settings s 
    WHERE p.is_active = 1 
    AND p.is_deleted = 0
    AND p.stock_quantity <= s.setting_value 
    AND s.setting_key = 'low_stock_threshold'
    ORDER BY p.stock_quantity ASC;
END$$

-- Procedura per statistiche vendite
CREATE PROCEDURE `GetSalesReport`(
    IN p_date_from DATE,
    IN p_date_to DATE
)
BEGIN
    SELECT 
        DATE(o.created_at) as order_date,
        COUNT(o.id) as total_orders,
        COALESCE(SUM(o.total_amount), 0) as total_revenue,
        COALESCE(AVG(o.total_amount), 0) as average_order_value,
        SUM(oi.quantity) as total_items_sold
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE o.created_at >= p_date_from 
    AND o.created_at <= p_date_to + INTERVAL 1 DAY
    AND o.order_status != 'cancelled'
    GROUP BY DATE(o.created_at)
    ORDER BY order_date DESC;
END$$

DELIMITER ;

-- =============================================
-- VERIFICA TABELLE CREATE
-- =============================================

SELECT 'Tabella settings creata' as status;
SELECT 'Tabella categories creata' as status;
SELECT 'Tabella products creata' as status;
SELECT 'Tabella product_images creata' as status;
SELECT 'Tabella product_variants creata' as status;
SELECT 'Tabella customers creata' as status;
SELECT 'Tabella customer_addresses creata' as status;
SELECT 'Tabella orders creata' as status;
SELECT 'Tabella order_items creata' as status;
SELECT 'Tabella admin_users creata' as status;
SELECT 'Tabella sessions creata' as status;
SELECT 'Tabella system_logs creata' as status;
SELECT 'Tabella cart_items creata' as status;
SELECT 'Tabella product_reviews creata' as status;
SELECT 'Tabella coupons creata' as status;
SELECT 'Configurazioni iniziali inserite' as status;
SELECT 'Categorie di esempio inserite' as status;
SELECT 'Prodotti di esempio inseriti' as status;
SELECT 'Immagini di esempio inserite' as status;
SELECT 'Utente amministratore creato' as status;
SELECT 'Indici aggiuntivi creati' as status;
SELECT 'Triggers creati' as status;
SELECT 'Viste create' as status;
SELECT 'Procedure memorizzate create' as status;

SET FOREIGN_KEY_CHECKS = 1;

-- =============================================
-- RIEPILOGO FINALE
-- =============================================

SELECT 
    'Database completo creato con successo!' as message,
    NOW() as created_at,
    'Pinche Supplies E-commerce System' as system,
    'v1.0' as version;

-- =============================================
-- FINE FILE SQL
-- =============================================