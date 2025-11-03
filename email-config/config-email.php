<?php
/**
 * CONFIGURACIÓN DE EMAILS - PINCHE SUPPLIES
 * Configuración SMTP para Donweb Hosting
 */

// Configuración SMTP para Donweb
define('SMTP_HOST', 'mail.pinchesupplies.com.ar'); // Servidor SMTP Donweb
define('SMTP_PORT', 587); // Puerto seguro
define('SMTP_USERNAME', 'noreply@pinchesupplies.com.ar'); // Email noreply
define('SMTP_PASSWORD', 'tu_password_smtp_aqui'); // Poner tu contraseña real
define('SMTP_FROM_EMAIL', 'noreply@pinchesupplies.com.ar');
define('SMTP_FROM_NAME', 'Pinche Supplies');
define('ADMIN_EMAIL', 'admin@pinchesupplies.com.ar'); // Email del administrador

// Configuración adicional
define('SMTP_ENCRYPTION', 'tls'); // or 'ssl'
define('SMTP_AUTH', true);
define('SMTP_TIMEOUT', 30);

// Email de contacto principal
define('CONTACT_EMAIL', 'info@pinchesupplies.com.ar');
define('SALES_EMAIL', 'ventas@pinchesupplies.com.ar');

// Configuración de templates
define('EMAIL_TEMPLATE_PATH', __DIR__ . '/templates/');

/**
 * Función para obtener configuración SMTP
 */
function getSMTPConfig() {
    return [
        'host' => SMTP_HOST,
        'port' => SMTP_PORT,
        'username' => SMTP_USERNAME,
        'password' => SMTP_PASSWORD,
        'encryption' => SMTP_ENCRYPTION,
        'auth' => SMTP_AUTH,
        'timeout' => SMTP_TIMEOUT,
        'from_email' => SMTP_FROM_EMAIL,
        'from_name' => SMTP_FROM_NAME,
        'admin_email' => ADMIN_EMAIL,
        'contact_email' => CONTACT_EMAIL,
        'sales_email' => SALES_EMAIL
    ];
}
?>
