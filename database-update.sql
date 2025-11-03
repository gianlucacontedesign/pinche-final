-- ACTUALIZACIÓN BASE DE DATOS PARA SISTEMA DE EMAIL
-- Ejecuta este código en phpMyAdmin para activar la verificación por email

ALTER TABLE `users` 
ADD COLUMN `email_verified` TINYINT(1) DEFAULT 0 AFTER `email`,
ADD COLUMN `verification_token` VARCHAR(64) NULL AFTER `email_verified`,
ADD COLUMN `verification_token_expires` DATETIME NULL AFTER `verification_token`,
ADD INDEX (`verification_token`);

-- Si quieres verificar que funcionó, ejecuta:
-- DESCRIBE users;
-- Deberías ver los nuevos campos al final de la tabla
