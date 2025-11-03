<?php
/**
 * Database Configuration
 * Pinche Supplies - Sistema de E-commerce
 * 
 * Este archivo carga la clase Database y establece la conexión
 */

// Cargar clase Database si no está cargada
if (!class_exists('Database')) {
    require_once __DIR__ . '/../classes/Database.php';
}

// Obtener instancia de base de datos
try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
} catch (Exception $e) {
    error_log("Database initialization failed: " . $e->getMessage());
    die("Error al conectar con la base de datos. Por favor, contacte al administrador.");
}
?>
