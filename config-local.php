<?php
/**
 * Configuración Local para Pruebas
 * Este archivo permite probar el sistema localmente sin modificar la configuración de producción
 */

// Configuración de Base de Datos - Local
define('DB_HOST', 'localhost');
define('DB_NAME', 'pinche_supplies');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Configuración del Sistema
define('SITE_URL', 'http://localhost');
define('SITE_NAME', 'Pinche Supplies');
define('ADMIN_EMAIL', 'info@pinchesupplies.com.ar');

// Configuración de Email SMTP - Local (opcional)
define('SMTP_HOST', 'localhost');
define('SMTP_PORT', 587);
define('SMTP_USER', '');
define('SMTP_PASS', '');
define('SMTP_SECURE', 'tls');
define('SMTP_AUTH', false);

// Configuración de Seguridad
define('SECURITY_SALT', 'PS-2025-LOCAL-TESTING-SALT');
define('TOKEN_EXPIRE_HOURS', 24);
define('SESSION_LIFETIME', 3600);
define('PASSWORD_MIN_LENGTH', 8);
define('MAX_LOGIN_ATTEMPTS', 5);

// Configuración de Rutas
define('INCLUDES_PATH', __DIR__ . '/includes');
define('ASSETS_URL', SITE_URL . '/assets');
define('UPLOADS_PATH', __DIR__ . '/uploads');

// Configuración de Logs
define('LOG_ERRORS', true);
define('LOG_PATH', __DIR__ . '/logs');
define('LOG_MAX_SIZE', 10485760);

// Configuración de Email
define('MAX_TOKEN_USES', 3);
define('RESEND_EMAIL_DELAY', 300);

// Zonas Horarias
date_default_timezone_set('America/Argentina/Buenos_Aires');

// Configuración de Desarrollo
define('DEBUG_MODE', true);
define('LOG_QUERIES', true);

if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

/**
 * Función para obtener conexión PDO
 */
function getDBConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ];
        
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        if (DEBUG_MODE) {
            die("Error de conexión: " . $e->getMessage());
        } else {
            logError("Error de conexión a la base de datos", ['error' => $e->getMessage()]);
            die("Error de conexión. Contacte al administrador.");
        }
    }
}

/**
 * Función para log de errores
 */
function logError($message, $context = []) {
    if (!LOG_ERRORS) return;
    
    $logDir = LOG_PATH;
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $logFile = $logDir . '/errores.log';
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] ERROR: $message";
    
    if (!empty($context)) {
        $logEntry .= " | Context: " . json_encode($context);
    }
    
    $logEntry .= "\n";
    
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

/**
 * Función para limpiar datos de entrada
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Función para generar token único
 */
function generateToken($length = 64) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Función para hash de contraseña segura
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Función para verificar contraseña
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
