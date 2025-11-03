-- =================================
-- ACTUALIZACIÓN DE BASE DE DATOS
-- SISTEMA DE VERIFICACIÓN POR EMAIL
-- =================================

-- Verificar si los campos ya existen
-- Si ya existen, no los creará de nuevo

ALTER TABLE users 
ADD COLUMN IF NOT EXISTS email_verified TINYINT(1) DEFAULT 0 COMMENT 'Email verificado: 0=No, 1=Sí' AFTER active;

ALTER TABLE users 
ADD COLUMN IF NOT EXISTS verification_token VARCHAR(64) DEFAULT NULL COMMENT 'Token de verificación único' AFTER email_verified;

ALTER TABLE users 
ADD COLUMN IF NOT EXISTS verification_expires DATETIME DEFAULT NULL COMMENT 'Fecha de expiración del token' AFTER verification_token;

ALTER TABLE users 
ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de creación' AFTER verification_expires;

-- Crear índices para optimizar búsquedas
CREATE INDEX IF NOT EXISTS idx_email_verified ON users(email_verified);
CREATE INDEX IF NOT EXISTS idx_verification_token ON users(verification_token);
CREATE INDEX IF NOT EXISTS idx_verification_expires ON users(verification_expires);

-- =================================
-- INFORMACIÓN DE ACTUALIZACIÓN
-- =================================
-- Este script añade los siguientes campos:
-- 
-- email_verified: Boolean (0/1) - Indica si el email está verificado
-- verification_token: String(64) - Token único para verificación
-- verification_expires: DateTime - Fecha de expiración del token
-- created_at: Timestamp - Fecha de creación del usuario
--
-- INDICES CREADOS:
-- - idx_email_verified: Para buscar usuarios verificados/no verificados
-- - idx_verification_token: Para verificar tokens únicos
-- - idx_verification_expires: Para limpiar tokens expirados

SELECT 'Base de datos actualizada exitosamente' as resultado;