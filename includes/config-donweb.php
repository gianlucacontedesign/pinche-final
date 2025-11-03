<?php
/**
 * Configuración de la aplicación Pinche Supplies
 * ADAPTADO PARA DONWEB - SERVIDOR pinchesupplies.com.ar
 */

// Configuración de la base de datos
// Credenciales para Donweb Hosting
define('DB_HOST', 'localhost');
define('DB_NAME', 'a0030995_pinche');
define('DB_USER', 'a0030995_pinche');
define('DB_PASS', 'vawuDU97zu');
define('DB_CHARSET', 'utf8mb4');

// Configuración del sitio
define('SITE_NAME', 'Pinche Supplies');
define('SITE_URL', 'https://pinchesupplies.com.ar');
define('ADMIN_URL', SITE_URL . '/admin');

// Rutas del sistema
define('ROOT_PATH', dirname(__DIR__));
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('ASSETS_PATH', ROOT_PATH . '/assets');
define('UPLOADS_PATH', ROOT_PATH . '/uploads');

// URLs públicas
define('ASSETS_URL', SITE_URL . '/assets');
define('UPLOADS_URL', SITE_URL . '/uploads');

// Configuración de sesión
define('SESSION_NAME', 'pinche_supplies_session');
define('SESSION_LIFETIME', 7200); // 2 horas

// Configuración de seguridad
define('PASSWORD_MIN_LENGTH', 8);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_TIMEOUT', 900); // 15 minutos

// Configuración de imágenes
define('MAX_IMAGE_SIZE', 5242880); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
define('IMAGE_QUALITY', 85);
define('THUMBNAIL_WIDTH', 400);
define('THUMBNAIL_HEIGHT', 400);

// Configuración de paginación
define('PRODUCTS_PER_PAGE', 12);
define('ADMIN_ITEMS_PER_PAGE', 20);

// Zona horaria
date_default_timezone_set('America/Argentina/Buenos_Aires');

// Configuración de errores para producción
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

// Iniciar sesión
session_name(SESSION_NAME);
session_start();

// Autoload de clases
spl_autoload_register(function ($class_name) {
    $file = INCLUDES_PATH . '/class.' . strtolower($class_name) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Funciones de utilidad
require_once INCLUDES_PATH . '/functions.php';
