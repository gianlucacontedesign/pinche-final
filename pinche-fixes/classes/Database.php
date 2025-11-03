<?php
/**
 * Database Class - Conexión PDO a MySQL
 * Pinche Supplies - Sistema de E-commerce
 */

class Database {
    private static $instance = null;
    private $pdo;
    
    /**
     * Constructor privado para patrón Singleton
     */
    private function __construct() {
        try {
            $this->pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASSWORD,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
                ]
            );
        } catch (PDOException $e) {
            // Log error y mostrar mensaje genérico
            error_log("Database Connection Error: " . $e->getMessage());
            die("Error de conexión a la base de datos. Por favor, contacte al administrador.");
        }
    }
    
    /**
     * Obtener instancia única de la base de datos (Singleton)
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Obtener conexión PDO
     */
    public function getConnection() {
        return $this->pdo;
    }
    
    /**
     * Prevenir clonación
     */
    private function __clone() {}
    
    /**
     * Prevenir deserialización
     */
    public function __wakeup() {
        throw new Exception("No se puede deserializar un singleton.");
    }
}
?>
