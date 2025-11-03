-- Base de datos para Sistema de Carrito de Compras
-- Pinche Supplies - Creado: 03 Nov 2025 - 21:44
-- 
-- Instrucciones:
-- 1. Ejecutar este script en tu base de datos MySQL
-- 2. Verificar que todas las tablas se crearon correctamente
-- 3. Insertar datos de prueba si es necesario

SET FOREIGN_KEY_CHECKS = 0;

-- =============================================
-- TABLA: products
-- Productos principales del catálogo
-- =============================================
DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL COMMENT 'Nombre del producto',
    `slug` VARCHAR(255) NOT NULL UNIQUE COMMENT 'URL amigable del producto',
    `sku` VARCHAR(100) DEFAULT NULL UNIQUE COMMENT 'Código SKU del producto',
    `description` TEXT COMMENT 'Descripción completa del producto',
    `short_description` VARCHAR(500) DEFAULT NULL COMMENT 'Descripción corta',
    `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Precio de venta',
    `original_price` DECIMAL(10,2) DEFAULT NULL COMMENT 'Precio original (para ofertas)',
    `discount_percentage` INT(3) DEFAULT 0 COMMENT 'Porcentaje de descuento (0-100)',
    `stock` INT(11) NOT NULL DEFAULT 0 COMMENT 'Stock disponible',
    `min_stock` INT(11) DEFAULT 5 COMMENT 'Stock mínimo para alertas',
    `weight` DECIMAL(8,2) DEFAULT 0.00 COMMENT 'Peso en kilogramos',
    `category_id` INT(11) NOT NULL COMMENT 'ID de la categoría',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Producto activo/inactivo',
    `is_featured` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Producto destacado',
    `is_new` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Producto nuevo',
    `sales_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'Cantidad vendida',
    `meta_title` VARCHAR(255) DEFAULT NULL COMMENT 'SEO: Título',
    `meta_description` VARCHAR(500) DEFAULT NULL COMMENT 'SEO: Descripción',
    `meta_keywords` VARCHAR(500) DEFAULT NULL COMMENT 'SEO: Palabras clave',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de creación',
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Última actualización',
    PRIMARY KEY (`id`),
    UNIQUE KEY `slug` (`slug`),
    UNIQUE KEY `sku` (`sku`),
    KEY `idx_category` (`category_id`),
    KEY `idx_active` (`is_active`),
    KEY `idx_featured` (`is_featured`),
    KEY `idx_price` (`price`),
    KEY `idx_stock` (`stock`),
    KEY `idx_sales` (`sales_count`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabla de productos principales';

-- =============================================
-- TABLA: categories
-- Categorías de productos
-- =============================================
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL COMMENT 'Nombre de la categoría',
    `slug` VARCHAR(255) NOT NULL UNIQUE COMMENT 'URL amigable de la categoría',
    `description` TEXT COMMENT 'Descripción de la categoría',
    `image` VARCHAR(500) DEFAULT NULL COMMENT 'Imagen de la categoría',
    `parent_id` INT(11) DEFAULT NULL COMMENT 'ID de categoría padre (para categorías anidadas)',
    `sort_order` INT(11) DEFAULT 0 COMMENT 'Orden de visualización',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Categoría activa/inactiva',
    `meta_title` VARCHAR(255) DEFAULT NULL COMMENT 'SEO: Título',
    `meta_description` VARCHAR(500) DEFAULT NULL COMMENT 'SEO: Descripción',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de creación',
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Última actualización',
    PRIMARY KEY (`id`),
    UNIQUE KEY `slug` (`slug`),
    KEY `idx_parent` (`parent_id`),
    KEY `idx_active` (`is_active`),
    KEY `idx_sort` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabla de categorías de productos';

-- =============================================
-- TABLA: product_images
-- Imágenes de productos
-- =============================================
DROP TABLE IF EXISTS `product_images`;
CREATE TABLE `product_images` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `product_id` INT(11) NOT NULL COMMENT 'ID del producto',
    `image_path` VARCHAR(500) NOT NULL COMMENT 'Ruta de la imagen',
    `alt_text` VARCHAR(255) DEFAULT NULL COMMENT 'Texto alternativo',
    `is_primary` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Imagen principal',
    `sort_order` INT(11) NOT NULL DEFAULT 0 COMMENT 'Orden de visualización',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de creación',
    PRIMARY KEY (`id`),
    KEY `idx_product` (`product_id`),
    KEY `idx_primary` (`is_primary`),
    KEY `idx_sort` (`sort_order`),
    CONSTRAINT `fk_product_images_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabla de imágenes de productos';

-- =============================================
-- TABLA: product_variants
-- Variantes de productos (tallas, colores, etc.)
-- =============================================
DROP TABLE IF EXISTS `product_variants`;
CREATE TABLE `product_variants` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `product_id` INT(11) NOT NULL COMMENT 'ID del producto',
    `name` VARCHAR(100) NOT NULL COMMENT 'Nombre de la variante (Talla, Color, etc.)',
    `value` VARCHAR(100) NOT NULL COMMENT 'Valor de la variante (XL, Rojo, etc.)',
    `sku_suffix` VARCHAR(50) DEFAULT NULL COMMENT 'Sufijo del SKU para esta variante',
    `price_modifier` DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Modificador de precio (+ o -)',
    `stock_modifier` INT(11) DEFAULT 0 COMMENT 'Modificador de stock',
    `weight_modifier` DECIMAL(8,2) DEFAULT 0.00 COMMENT 'Modificador de peso',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Variante activa/inactiva',
    `sort_order` INT(11) NOT NULL DEFAULT 0 COMMENT 'Orden de visualización',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de creación',
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Última actualización',
    PRIMARY KEY (`id`),
    KEY `idx_product` (`product_id`),
    KEY `idx_active` (`is_active`),
    KEY `idx_sort` (`sort_order`),
    CONSTRAINT `fk_product_variants_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabla de variantes de productos';

-- =============================================
-- TABLA: settings
-- Configuraciones del sistema
-- =============================================
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `setting_key` VARCHAR(100) NOT NULL UNIQUE COMMENT 'Clave de la configuración',
    `setting_value` TEXT COMMENT 'Valor de la configuración',
    `setting_type` VARCHAR(50) DEFAULT 'string' COMMENT 'Tipo de dato (string, integer, decimal, boolean)',
    `description` VARCHAR(255) DEFAULT NULL COMMENT 'Descripción de la configuración',
    `is_public` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Si es visible públicamente',
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Última actualización',
    PRIMARY KEY (`id`),
    UNIQUE KEY `setting_key` (`setting_key`),
    KEY `idx_public` (`is_public`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabla de configuraciones del sistema';

-- =============================================
-- TABLA: orders
-- Órdenes de compra
-- =============================================
DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `order_number` VARCHAR(50) NOT NULL UNIQUE COMMENT 'Número de orden único',
    `customer_id` INT(11) DEFAULT NULL COMMENT 'ID del cliente (NULL para pedidos de invitados)',
    `customer_email` VARCHAR(255) NOT NULL COMMENT 'Email del cliente',
    `customer_name` VARCHAR(255) NOT NULL COMMENT 'Nombre completo del cliente',
    `customer_phone` VARCHAR(50) DEFAULT NULL COMMENT 'Teléfono del cliente',
    `subtotal` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Subtotal sin impuestos ni envío',
    `tax_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Monto de impuestos',
    `shipping_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Costo de envío',
    `discount_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Monto de descuentos',
    `total_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Total final',
    `tax_rate` DECIMAL(5,2) DEFAULT 21.00 COMMENT 'Tasa de impuestos aplicada',
    `shipping_method` VARCHAR(100) DEFAULT NULL COMMENT 'Método de envío seleccionado',
    `payment_method` VARCHAR(100) DEFAULT NULL COMMENT 'Método de pago seleccionado',
    `payment_status` VARCHAR(50) NOT NULL DEFAULT 'pending' COMMENT 'Estado del pago',
    `order_status` VARCHAR(50) NOT NULL DEFAULT 'pending' COMMENT 'Estado de la orden',
    `shipping_address` TEXT COMMENT 'Dirección de envío completa',
    `billing_address` TEXT COMMENT 'Dirección de facturación completa',
    `notes` TEXT COMMENT 'Notas adicionales',
    `processed_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Fecha de procesamiento',
    `shipped_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Fecha de envío',
    `delivered_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Fecha de entrega',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de creación',
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Última actualización',
    PRIMARY KEY (`id`),
    UNIQUE KEY `order_number` (`order_number`),
    KEY `idx_customer` (`customer_id`),
    KEY `idx_email` (`customer_email`),
    KEY `idx_status` (`order_status`),
    KEY `idx_payment` (`payment_status`),
    KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabla de órdenes de compra';

-- =============================================
-- TABLA: order_items
-- Items de las órdenes
-- =============================================
DROP TABLE IF EXISTS `order_items`;
CREATE TABLE `order_items` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `order_id` INT(11) NOT NULL COMMENT 'ID de la orden',
    `product_id` INT(11) NOT NULL COMMENT 'ID del producto',
    `variant_id` INT(11) DEFAULT NULL COMMENT 'ID de la variante (si aplica)',
    `product_name` VARCHAR(255) NOT NULL COMMENT 'Nombre del producto al momento de la compra',
    `product_sku` VARCHAR(100) DEFAULT NULL COMMENT 'SKU del producto al momento de la compra',
    `variant_name` VARCHAR(100) DEFAULT NULL COMMENT 'Nombre de la variante',
    `variant_value` VARCHAR(100) DEFAULT NULL COMMENT 'Valor de la variante',
    `quantity` INT(11) NOT NULL COMMENT 'Cantidad ordenada',
    `unit_price` DECIMAL(10,2) NOT NULL COMMENT 'Precio unitario al momento de la compra',
    `total_price` DECIMAL(10,2) NOT NULL COMMENT 'Precio total (unit_price * quantity)',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de creación',
    PRIMARY KEY (`id`),
    KEY `idx_order` (`order_id`),
    KEY `idx_product` (`product_id`),
    KEY `idx_variant` (`variant_id`),
    CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_order_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabla de items de las órdenes';

-- =============================================
-- TABLA: customers
-- Clientes registrados
-- =============================================
DROP TABLE IF EXISTS `customers`;
CREATE TABLE `customers` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `email` VARCHAR(255) NOT NULL UNIQUE COMMENT 'Email del cliente (único)',
    `password_hash` VARCHAR(255) NOT NULL COMMENT 'Hash de la contraseña',
    `first_name` VARCHAR(100) NOT NULL COMMENT 'Nombre',
    `last_name` VARCHAR(100) NOT NULL COMMENT 'Apellido',
    `phone` VARCHAR(50) DEFAULT NULL COMMENT 'Teléfono',
    `date_of_birth` DATE DEFAULT NULL COMMENT 'Fecha de nacimiento',
    `gender` ENUM('male', 'female', 'other', 'prefer_not_to_say') DEFAULT NULL COMMENT 'Género',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Cuenta activa/inactiva',
    `is_verified` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Email verificado/no verificado',
    `verification_token` VARCHAR(255) DEFAULT NULL COMMENT 'Token de verificación',
    `reset_token` VARCHAR(255) DEFAULT NULL COMMENT 'Token de recuperación de contraseña',
    `reset_expires` TIMESTAMP NULL DEFAULT NULL COMMENT 'Expiración del token de reset',
    `last_login` TIMESTAMP NULL DEFAULT NULL COMMENT 'Último inicio de sesión',
    `login_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'Cantidad de inicios de sesión',
    `total_orders` INT(11) NOT NULL DEFAULT 0 COMMENT 'Total de órdenes realizadas',
    `total_spent` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Total gastado',
    `notes` TEXT COMMENT 'Notas internas',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de creación',
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Última actualización',
    PRIMARY KEY (`id`),
    UNIQUE KEY `email` (`email`),
    KEY `idx_active` (`is_active`),
    KEY `idx_verified` (`is_verified`),
    KEY `idx_last_login` (`last_login`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabla de clientes registrados';

-- =============================================
-- TABLA: customer_addresses
-- Direcciones de los clientes
-- =============================================
DROP TABLE IF EXISTS `customer_addresses`;
CREATE TABLE `customer_addresses` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `customer_id` INT(11) NOT NULL COMMENT 'ID del cliente',
    `type` ENUM('shipping', 'billing') NOT NULL DEFAULT 'shipping' COMMENT 'Tipo de dirección',
    `first_name` VARCHAR(100) NOT NULL COMMENT 'Nombre',
    `last_name` VARCHAR(100) NOT NULL COMMENT 'Apellido',
    `company` VARCHAR(100) DEFAULT NULL COMMENT 'Empresa',
    `address_line_1` VARCHAR(255) NOT NULL COMMENT 'Dirección línea 1',
    `address_line_2` VARCHAR(255) DEFAULT NULL COMMENT 'Dirección línea 2',
    `city` VARCHAR(100) NOT NULL COMMENT 'Ciudad',
    `state_province` VARCHAR(100) DEFAULT NULL COMMENT 'Estado/Provincia',
    `postal_code` VARCHAR(20) NOT NULL COMMENT 'Código postal',
    `country` VARCHAR(100) NOT NULL DEFAULT 'Argentina' COMMENT 'País',
    `phone` VARCHAR(50) DEFAULT NULL COMMENT 'Teléfono',
    `is_default` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Dirección por defecto',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de creación',
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Última actualización',
    PRIMARY KEY (`id`),
    KEY `idx_customer` (`customer_id`),
    KEY `idx_type` (`type`),
    KEY `idx_default` (`is_default`),
    CONSTRAINT `fk_customer_addresses_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabla de direcciones de clientes';

-- =============================================
-- INSERTAR DATOS DE CONFIGURACIÓN INICIAL
-- =============================================

INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_type`, `description`, `is_public`) VALUES
('tax_rate', '21', 'decimal', 'Tasa de impuestos IVA (porcentaje)', 1),
('shipping_cost', '500', 'decimal', 'Costo de envío estándar', 1),
('free_shipping_threshold', '15000', 'decimal', 'Monto mínimo para envío gratis', 1),
('low_stock_threshold', '5', 'integer', 'Cantidad mínima para alerta de stock bajo', 0),
('critical_stock_threshold', '2', 'integer', 'Cantidad mínima para alerta de stock crítico', 0),
('currency_symbol', '$', 'string', 'Símbolo de moneda', 1),
('currency_position', 'before', 'string', 'Posición del símbolo (before/after)', 1),
('decimal_places', '2', 'integer', 'Número de decimales para precios', 1),
('decimal_separator', ',', 'string', 'Separador decimal', 1),
('thousands_separator', '.', 'string', 'Separador de miles', 1),
('store_name', 'Pinche Supplies', 'string', 'Nombre de la tienda', 1),
('store_email', 'info@pinchesupplies.com.ar', 'string', 'Email principal de la tienda', 0),
('store_phone', '+54 11 1234-5678', 'string', 'Teléfono principal de la tienda', 1),
('store_address', 'Buenos Aires, Argentina', 'string', 'Dirección de la tienda', 1),
('business_hours', 'Lunes a Viernes 9:00 - 18:00', 'string', 'Horarios de atención', 1);

-- =============================================
-- INSERTAR CATEGORÍAS DE EJEMPLO
-- =============================================

INSERT INTO `categories` (`name`, `slug`, `description`, `is_active`, `sort_order`) VALUES
('Tintas', 'tintas', 'Tintas para tatuajes de alta calidad', 1, 1),
('Agujas', 'agujas', 'Agujas y cartuchos para tatuajes', 1, 2),
('Máquinas', 'maquinas', 'Máquinas de tatuar rotativas y bobina', 1, 3),
('Fuentes de Alimentación', 'fuentes', 'Fuentes de alimentación para máquinas', 1, 4),
('Guantes', 'guantes', 'Guantes desechables y de protección', 1, 5),
('Productos de Higiene', 'higiene', 'Productos de limpieza e higiene', 1, 6),
('Accesorios', 'accesorios', 'Accesorios y herramientas adicionales', 1, 7);

-- =============================================
-- INSERTAR PRODUCTOS DE EJEMPLO
-- =============================================

INSERT INTO `products` (`name`, `slug`, `sku`, `description`, `price`, `stock`, `category_id`, `is_active`, `is_featured`, `is_new`) VALUES
('Tinta Black Mamba 30ml', 'tinta-black-mamba-30ml', 'TIN-BM-30', 'Tinta negra de alta densidad para contornos y rellenos intensos', 2500.00, 15, 1, 1, 1, 1),
('Set Agujas Round Liner RL 3', 'set-agujas-round-liner-rl3', 'AGU-RL3-SET', 'Set de agujas Round Liner RL 3 - 10 unidades', 1800.00, 25, 2, 1, 1, 0),
('Máquina Rotativa Bishop V6', 'maquina-rotativa-bishop-v6', 'BIS-V6-ROT', 'Máquina rotativa Bishop V6 con motorbrushless', 45000.00, 3, 3, 1, 1, 1),
('Fuente Digital 2A', 'fuente-digital-2a', 'FUE-DIG-2A', 'Fuente de alimentación digital con pantalla LCD 2A', 12000.00, 8, 4, 1, 0, 0),
('Guantes Nitrilo Caja 100u', 'guantes-nitrilo-caja-100', 'GUI-NIT-100', 'Guantes de nitrilo sin polvo - Caja por 100 unidades', 850.00, 50, 5, 1, 0, 0),
('Alcohol Isopropílico 1L', 'alcohol-isopropilico-1l', 'ALC-ISO-1L', 'Alcohol isopropílico para limpieza y desinfección 1 litro', 650.00, 30, 6, 1, 0, 0),
('Porta Máquina Acero Inox', 'porta-maquina-acero-inox', 'POR-MAQ-AC', 'Porta máquina de acero inoxidable ajustable', 3200.00, 12, 7, 1, 0, 0);

-- =============================================
-- INSERTAR IMÁGENES DE EJEMPLO
-- =============================================

INSERT INTO `product_images` (`product_id`, `image_path`, `alt_text`, `is_primary`, `sort_order`) VALUES
(1, 'productos/tintas/black-mamba-30ml.jpg', 'Tinta Black Mamba 30ml', 1, 1),
(2, 'productos/agujas/rl3-set.jpg', 'Set Agujas Round Liner RL 3', 1, 1),
(3, 'productos/maquinas/bishop-v6.jpg', 'Máquina Rotativa Bishop V6', 1, 1),
(4, 'productos/fuentes/digital-2a.jpg', 'Fuente Digital 2A', 1, 1),
(5, 'productos/guantes/nitrilo-100u.jpg', 'Guantes Nitrilo Caja 100u', 1, 1),
(6, 'productos/higiene/alcohol-isopropilico.jpg', 'Alcohol Isopropílico 1L', 1, 1),
(7, 'productos/accesorios/porta-maquina-acero.jpg', 'Porta Máquina Acero Inox', 1, 1);

-- =============================================
-- ÍNDICES ADICIONALES PARA OPTIMIZACIÓN
-- =============================================

-- Índices compuestos para consultas comunes
ALTER TABLE `products` ADD INDEX `idx_category_active` (`category_id`, `is_active`);
ALTER TABLE `products` ADD INDEX `idx_featured_active` (`is_featured`, `is_active`);
ALTER TABLE `products` ADD INDEX `idx_price_stock` (`price`, `stock`);

-- =============================================
-- TRIGGERS PARA AUDITORÍA
-- =============================================

DELIMITER $$

-- Trigger para actualizar sales_count cuando se crea una orden
CREATE TRIGGER `tr_update_sales_count` 
AFTER INSERT ON `order_items` 
FOR EACH ROW
BEGIN
    UPDATE products 
    SET sales_count = sales_count + NEW.quantity 
    WHERE id = NEW.product_id;
END$$

-- Trigger para actualizar total_orders y total_spent del cliente
CREATE TRIGGER `tr_update_customer_stats` 
AFTER INSERT ON `orders` 
FOR EACH ROW
BEGIN
    UPDATE customers 
    SET total_orders = total_orders + 1,
        total_spent = total_spent + NEW.total_amount,
        last_login = NOW()
    WHERE id = NEW.customer_id;
END$$

DELIMITER ;

-- =============================================
-- VISTAS ÚTILES
-- =============================================

-- Vista para productos con información de categoría
CREATE VIEW `vw_products_with_category` AS
SELECT 
    p.*,
    c.name as category_name,
    c.slug as category_slug,
    (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image,
    (SELECT COUNT(*) FROM product_images WHERE product_id = p.id) as image_count,
    CASE 
        WHEN p.stock <= 0 THEN 'out_of_stock'
        WHEN p.stock <= (SELECT setting_value FROM settings WHERE setting_key = 'critical_stock_threshold') THEN 'critical_stock'
        WHEN p.stock <= (SELECT setting_value FROM settings WHERE setting_key = 'low_stock_threshold') THEN 'low_stock'
        ELSE 'in_stock'
    END as stock_status
FROM products p
INNER JOIN categories c ON p.category_id = c.id
WHERE p.is_active = 1;

-- Vista para estadísticas del carrito
CREATE VIEW `vw_cart_summary` AS
SELECT 
    COUNT(*) as total_items,
    SUM(CASE WHEN pi.is_primary = 1 THEN 1 ELSE 0 END) as products_with_images,
    SUM(p.stock) as total_stock,
    AVG(p.price) as average_price,
    MIN(p.price) as min_price,
    MAX(p.price) as max_price
FROM products p
LEFT JOIN product_images pi ON p.id = pi.product_id
WHERE p.is_active = 1;

-- =============================================
-- PROCEDIMIENTOS ALMACENADOS
-- =============================================

DELIMITER $$

-- Procedimiento para actualizar stock de un producto
CREATE PROCEDURE `UpdateProductStock`(
    IN p_product_id INT,
    IN p_quantity_change INT,
    IN p_operation ENUM('add', 'subtract', 'set')
)
BEGIN
    DECLARE current_stock INT;
    
    SELECT stock INTO current_stock FROM products WHERE id = p_product_id;
    
    IF p_operation = 'add' THEN
        UPDATE products SET stock = stock + p_quantity_change WHERE id = p_product_id;
    ELSEIF p_operation = 'subtract' THEN
        UPDATE products SET stock = GREATEST(0, stock - p_quantity_change) WHERE id = p_product_id;
    ELSEIF p_operation = 'set' THEN
        UPDATE products SET stock = GREATEST(0, p_quantity_change) WHERE id = p_product_id;
    END IF;
END$$

-- Procedimiento para obtener productos con stock bajo
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
    AND p.stock <= s.setting_value 
    AND s.setting_key = 'low_stock_threshold'
    ORDER BY p.stock ASC;
END$$

DELIMITER ;

-- =============================================
-- VERIFICACIÓN FINAL
-- =============================================

SELECT 'Tabla products creada' as status;
SELECT 'Tabla categories creada' as status;
SELECT 'Tabla product_images creada' as status;
SELECT 'Tabla product_variants creada' as status;
SELECT 'Tabla settings creada' as status;
SELECT 'Tabla orders creada' as status;
SELECT 'Tabla order_items creada' as status;
SELECT 'Tabla customers creada' as status;
SELECT 'Tabla customer_addresses creada' as status;
SELECT 'Configuraciones insertadas' as status;
SELECT 'Categorías de ejemplo insertadas' as status;
SELECT 'Productos de ejemplo insertados' as status;
SELECT 'Imágenes de ejemplo insertadas' as status;
SELECT 'Triggers creados' as status;
SELECT 'Vistas creadas' as status;
SELECT 'Procedimientos almacenados creados' as status;

SET FOREIGN_KEY_CHECKS = 1;

-- =============================================
-- FINALIZACIÓN
-- =============================================
SELECT 'Base de datos del carrito creada exitosamente' as message;