-- Base de Datos Pinche Supplies
-- Ejecutar este script para crear la estructura de la base de datos

CREATE DATABASE IF NOT EXISTS pinche_supplies CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE pinche_supplies;

-- Tabla de usuarios administradores
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'manager') DEFAULT 'manager',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de categorías
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    parent_id INT NULL,
    image VARCHAR(255),
    display_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_slug (slug),
    INDEX idx_parent (parent_id),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de productos
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(200) UNIQUE NOT NULL,
    description TEXT,
    short_description VARCHAR(500),
    price DECIMAL(10, 2) NOT NULL,
    compare_price DECIMAL(10, 2) NULL,
    cost DECIMAL(10, 2) NULL,
    sku VARCHAR(100) UNIQUE,
    barcode VARCHAR(100),
    stock INT DEFAULT 0,
    min_stock INT DEFAULT 5,
    weight DECIMAL(8, 2),
    is_active TINYINT(1) DEFAULT 1,
    is_featured TINYINT(1) DEFAULT 0,
    is_new TINYINT(1) DEFAULT 0,
    views INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    INDEX idx_category (category_id),
    INDEX idx_slug (slug),
    INDEX idx_sku (sku),
    INDEX idx_active (is_active),
    INDEX idx_featured (is_featured),
    INDEX idx_stock (stock),
    FULLTEXT INDEX idx_search (name, description, short_description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de imágenes de productos
CREATE TABLE product_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    display_order INT DEFAULT 0,
    is_primary TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_product (product_id),
    INDEX idx_primary (is_primary)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de variantes de productos (color, talla, etc.)
CREATE TABLE product_variants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    value VARCHAR(100) NOT NULL,
    price_modifier DECIMAL(10, 2) DEFAULT 0,
    stock INT DEFAULT 0,
    sku_suffix VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_product (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de pedidos
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    customer_name VARCHAR(100) NOT NULL,
    customer_email VARCHAR(100) NOT NULL,
    customer_phone VARCHAR(20),
    shipping_address TEXT NOT NULL,
    billing_address TEXT,
    subtotal DECIMAL(10, 2) NOT NULL,
    shipping_cost DECIMAL(10, 2) DEFAULT 0,
    tax DECIMAL(10, 2) DEFAULT 0,
    total DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    payment_method VARCHAR(50),
    payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_order_number (order_number),
    INDEX idx_status (status),
    INDEX idx_email (customer_email),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de items de pedido
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(200) NOT NULL,
    variant_info VARCHAR(200),
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT,
    INDEX idx_order (order_id),
    INDEX idx_product (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de configuración del sitio
CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type ENUM('text', 'number', 'boolean', 'json') DEFAULT 'text',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar usuario administrador por defecto
-- Usuario: admin
-- Contraseña: admin123 (CAMBIAR INMEDIATAMENTE EN PRODUCCIÓN)
INSERT INTO users (username, email, password, full_name, role) VALUES 
('admin', 'admin@pinchesupplies.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador', 'admin');

-- Insertar configuraciones básicas
INSERT INTO settings (setting_key, setting_value, setting_type) VALUES
('site_name', 'Pinche Supplies', 'text'),
('site_email', 'contacto@pinchesupplies.com', 'text'),
('site_phone', '+54 11 1234-5678', 'text'),
('currency', 'ARS', 'text'),
('tax_rate', '21', 'number'),
('shipping_cost', '1500', 'number'),
('free_shipping_threshold', '15000', 'number');

-- Insertar categorías de ejemplo
INSERT INTO categories (name, slug, description, parent_id, display_order, is_active) VALUES
('Máquinas', 'maquinas', 'Máquinas y equipos profesionales', NULL, 1, 1),
('Tintas', 'tintas', 'Tintas y pigmentos de alta calidad', NULL, 2, 1),
('Agujas', 'agujas', 'Agujas y cartuchos profesionales', NULL, 3, 1),
('Insumos', 'insumos', 'Insumos y descartables', NULL, 4, 1),
('Accesorios', 'accesorios', 'Accesorios y complementos', NULL, 5, 1);

-- Insertar productos de ejemplo
INSERT INTO products (category_id, name, slug, description, short_description, price, compare_price, cost, sku, stock, min_stock, is_active, is_featured, is_new) VALUES
(1, 'Máquina Rotativa Pro', 'maquina-rotativa-pro', 'Máquina rotativa profesional de alta precisión con motor japonés. Ideal para líneas y sombreado. Construcción en aluminio aeronáutico.', 'Máquina rotativa profesional de alta precisión', 45000.00, 55000.00, 28000.00, 'MAQ-ROT-001', 15, 3, 1, 1, 1),
(1, 'Pen Inalámbrica Premium', 'pen-inalambrica-premium', 'Pen inalámbrica con batería de larga duración. Motor brushless sin mantenimiento. Peso balanceado para sesiones largas.', 'Pen inalámbrica con batería de larga duración', 38000.00, 45000.00, 24000.00, 'MAQ-PEN-002', 20, 5, 1, 1, 1),
(2, 'Kit Tintas Negras Premium', 'kit-tintas-negras-premium', 'Set de 5 tonos de negro para líneas y sombreado. Fórmula vegana, testada dermatológicamente. Botellas de 30ml.', 'Set de 5 tonos de negro profesional', 12500.00, 15000.00, 7500.00, 'TINT-KIT-001', 30, 5, 1, 1, 0),
(2, 'Tinta Color Set 12 unidades', 'tinta-color-set-12', 'Paleta completa de 12 colores vibrantes. Alta pigmentación y durabilidad. 30ml cada botella.', 'Paleta completa de 12 colores vibrantes', 28000.00, NULL, 16000.00, 'TINT-COL-002', 25, 3, 1, 0, 0),
(3, 'Cartuchos Premium RL 10 unidades', 'cartuchos-premium-rl-10', 'Pack de 10 cartuchos Round Liner. Membrana de seguridad, aguja estéril. Compatible con mayoría de máquinas.', 'Pack de 10 cartuchos Round Liner profesional', 8500.00, 10000.00, 5000.00, 'AGU-CART-001', 50, 10, 1, 1, 0),
(4, 'Guantes Nitrilo Negros 100 unidades', 'guantes-nitrilo-negros-100', 'Caja de 100 guantes de nitrilo sin polvo. Alta resistencia y sensibilidad. Color negro profesional.', 'Caja de 100 guantes de nitrilo profesional', 3500.00, NULL, 2000.00, 'INS-GUA-001', 100, 20, 1, 0, 0);

-- Insertar imágenes de productos de ejemplo (rutas placeholder)
INSERT INTO product_images (product_id, image_path, display_order, is_primary) VALUES
(1, 'uploads/products/maquina-rotativa-1.jpg', 1, 1),
(1, 'uploads/products/maquina-rotativa-2.jpg', 2, 0),
(2, 'uploads/products/pen-inalambrica-1.jpg', 1, 1),
(3, 'uploads/products/kit-tintas-1.jpg', 1, 1),
(4, 'uploads/products/tinta-color-1.jpg', 1, 1),
(5, 'uploads/products/cartuchos-1.jpg', 1, 1),
(6, 'uploads/products/guantes-1.jpg', 1, 1);

-- Crear vistas para reportes

-- Vista de productos con bajo stock
CREATE OR REPLACE VIEW low_stock_products AS
SELECT 
    p.id,
    p.name,
    p.sku,
    p.stock,
    p.min_stock,
    c.name as category_name,
    p.price
FROM products p
INNER JOIN categories c ON p.category_id = c.id
WHERE p.stock <= p.min_stock AND p.is_active = 1;

-- Vista de productos más vendidos
CREATE OR REPLACE VIEW top_selling_products AS
SELECT 
    p.id,
    p.name,
    p.sku,
    SUM(oi.quantity) as total_sold,
    SUM(oi.subtotal) as total_revenue,
    COUNT(DISTINCT oi.order_id) as order_count
FROM products p
INNER JOIN order_items oi ON p.id = oi.product_id
INNER JOIN orders o ON oi.order_id = o.id
WHERE o.status NOT IN ('cancelled')
GROUP BY p.id, p.name, p.sku
ORDER BY total_sold DESC;

-- Vista de resumen de ventas
CREATE OR REPLACE VIEW sales_summary AS
SELECT 
    DATE(created_at) as sale_date,
    COUNT(*) as order_count,
    SUM(subtotal) as subtotal,
    SUM(shipping_cost) as shipping,
    SUM(tax) as tax,
    SUM(total) as total,
    AVG(total) as avg_order_value
FROM orders
WHERE status NOT IN ('cancelled')
GROUP BY DATE(created_at);
