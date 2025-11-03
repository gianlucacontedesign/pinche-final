-- Migración: Tabla de Clientes (Customers)
-- Fecha: 2025-10-29
-- Descripción: Tabla para almacenar usuarios/clientes del sitio web

USE pinche_supplies;

-- Tabla de clientes registrados
CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    phone VARCHAR(20),
    
    -- Dirección de envío predeterminada
    address VARCHAR(255),
    city VARCHAR(100),
    state VARCHAR(100),
    zip_code VARCHAR(20),
    country VARCHAR(100) DEFAULT 'Argentina',
    
    -- Verificación de email (para futuras implementaciones)
    email_verified TINYINT(1) DEFAULT 0,
    verification_token VARCHAR(255),
    verification_token_expires TIMESTAMP NULL,
    
    -- Reset de contraseña
    reset_token VARCHAR(255),
    reset_token_expires TIMESTAMP NULL,
    
    -- Timestamps
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Índices para optimización
    INDEX idx_email (email),
    INDEX idx_verification_token (verification_token),
    INDEX idx_reset_token (reset_token),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Agregar campo customer_id a la tabla orders (nullable para permitir compras como invitado)
ALTER TABLE orders 
ADD COLUMN customer_id INT NULL AFTER id,
ADD CONSTRAINT fk_orders_customer 
    FOREIGN KEY (customer_id) REFERENCES customers(id) 
    ON DELETE SET NULL,
ADD INDEX idx_customer (customer_id);

-- Insertar clientes de prueba (contraseña: "password123")
INSERT INTO customers (email, password, first_name, last_name, phone, address, city, state, zip_code) VALUES
('cliente1@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Juan', 'Pérez', '+54 11 1234-5678', 'Av. Corrientes 1234', 'Buenos Aires', 'CABA', '1043'),
('cliente2@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'María', 'González', '+54 11 8765-4321', 'Av. Santa Fe 5678', 'Buenos Aires', 'CABA', '1425'),
('cliente3@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Carlos', 'Rodríguez', '+54 11 5555-1234', 'Calle Falsa 123', 'Córdoba', 'Córdoba', '5000');

-- Verificar que la tabla se creó correctamente
SELECT 'Tabla customers creada exitosamente' AS status;
SELECT COUNT(*) AS total_customers FROM customers;
