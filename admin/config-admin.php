<?php
/**
 * Archivo de configuración del panel de administración
 * Ajusta estos valores según tu configuración
 */

// ========================================
// CONFIGURACIÓN DE BASE DE DATOS
// ========================================
define('DB_HOST', 'localhost');
define('DB_NAME', 'a0030995_pinche');
define('DB_USER', 'a0030995_pinche');
define('DB_PASS', 'vawuDU97zu');
define('DB_CHARSET', 'utf8mb4');

// ========================================
// CONFIGURACIÓN DE ADMINISTRADOR
// ========================================
// Email y contraseña para acceso al panel (CAMBIAR EN PRODUCCIÓN)
define('ADMIN_EMAIL', 'admin@pinchesupplies.com.ar');
define('ADMIN_PASSWORD', 'admin123'); // IMPORTANTE: Cambiar por una contraseña segura

// Hash de contraseña para mayor seguridad (opcional)
// Para generar: password_hash('tu_password', PASSWORD_DEFAULT)
define('ADMIN_PASSWORD_HASH', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

// ========================================
// CONFIGURACIÓN DEL SITIO
// ========================================
define('SITE_NAME', 'Pinche Supplies');
define('SITE_URL', 'https://tudominio.com'); // CAMBIAR por tu dominio
define('ADMIN_PANEL_NAME', 'Panel de Administración - Verificaciones');

// ========================================
// CONFIGURACIÓN DE EMAIL
// ========================================
define('EMAIL_FROM_NAME', 'Pinche Supplies');
define('EMAIL_FROM_ADDRESS', 'no-reply@tudominio.com'); // CAMBIAR por tu email
define('EMAIL_ADMIN_ADDRESS', 'admin@tudominio.com'); // CAMBIAR por tu email de admin

// ========================================
// CONFIGURACIÓN DE SEGURIDAD
// ========================================
// Tiempo de sesión en minutos
define('SESSION_TIMEOUT', 60);

// Configuración de tokens CSRF
define('CSRF_TOKEN_NAME', 'admin_csrf_token');

// ========================================
// CONFIGURACIÓN DE PAGINACIÓN
// ========================================
define('USERS_PER_PAGE', 20);

// ========================================
// CONFIGURACIÓN DE VERIFICACIÓN
// ========================================
// Tiempo de expiración del token de verificación (en horas)
define('TOKEN_EXPIRY_HOURS', 24);

// Configuración del auto-refresh del dashboard (en segundos)
define('AUTO_REFRESH_SECONDS', 30);

// ========================================
// CONFIGURACIÓN DE LOGS
// ========================================
// Directorio para logs del panel admin
define('ADMIN_LOG_DIR', __DIR__ . '/logs/');

// Crear directorio de logs si no existe
if (!file_exists(ADMIN_LOG_DIR)) {
    mkdir(ADMIN_LOG_DIR, 0755, true);
}

// ========================================
// CONFIGURACIÓN AVANZADA
// ========================================

// Configuración de errores (CAMBIAR EN PRODUCCIÓN)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Configuración de zona horaria
date_default_timezone_set('America/Argentina/Buenos_Aires');

// Función para log de actividad de admin
function logAdminActivity($action, $details = '', $user_id = null) {
    $log_file = ADMIN_LOG_DIR . 'admin_activity.log';
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    
    $log_entry = "[$timestamp] IP: $ip | Action: $action | Details: $details | UserID: $user_id | Agent: $user_agent" . PHP_EOL;
    
    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
}

// Función para validar configuración
function validateConfig() {
    $errors = [];
    
    // Verificar credenciales de base de datos
    if (DB_NAME === 'tu_base_datos' || DB_USER === 'tu_usuario' || DB_PASS === 'tu_password') {
        $errors[] = "Las credenciales de la base de datos no han sido configuradas correctamente.";
    }
    
    // Verificar email de admin
    if (ADMIN_EMAIL === 'admin@pinchesupplies.com.ar') {
        $errors[] = "El email de administrador no ha sido configurado.";
    }
    
    // Verificar contraseña por defecto
    if (ADMIN_PASSWORD === 'admin123') {
        $errors[] = "ADVERTENCIA: Estás usando la contraseña por defecto. Cambia ADMIN_PASSWORD por seguridad.";
    }
    
    // Verificar URL del sitio
    if (SITE_URL === 'https://tudominio.com') {
        $errors[] = "La URL del sitio no ha sido configurada correctamente.";
    }
    
    return $errors;
}

// Validar configuración al cargar
$config_errors = validateConfig();
if (!empty($config_errors) && $_SERVER['SERVER_NAME'] !== 'localhost') {
    // En producción, podrías redirigir a una página de error o mostrar warning
    // Por ahora, solo logueamos
    error_log("Configuración del panel admin requiere atención: " . implode('; ', $config_errors));
}
?>