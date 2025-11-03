<?php
// Configuración de Base de Datos para Verificación de Email
// Compatible con DonWeb cPanel

$db_host = "localhost";
$db_name = "a0030995_pinche";
$db_user = "a0030995_pinche";
$db_password = "vawuDU97zu";

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Opcional: Crear tabla de tokens si no existe
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS verification_tokens (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            token VARCHAR(64) NOT NULL UNIQUE,
            expires_at DATETIME NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user_id (user_id),
            INDEX idx_token (token),
            INDEX idx_expires (expires_at)
        )
    ");
    
} catch(PDOException $e) {
    error_log("Error de conexión a la base de datos: " . $e->getMessage());
    die("Error de conexión a la base de datos. Por favor, contacta al administrador.");
}
?>