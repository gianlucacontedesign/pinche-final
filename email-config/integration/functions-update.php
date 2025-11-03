<?php
/**
 * ACTUALIZACIÓN DE FUNCIONES - INTEGRACIÓN DE EMAILS
 * Añadir estas funciones a tu archivo includes/functions.php existente
 */

// Incluir configuración de emails
require_once __DIR__ . '/../email-config/config-email.php';

/**
 * Enviar email de bienvenida al usuario
 */
function sendWelcomeEmail($userEmail, $userName, $tempPassword = null) {
    require_once __DIR__ . '/../email-config/includes/email-sender.php';
    $emailSender = new EmailSender();
    return $emailSender->sendWelcomeEmail($userEmail, $userName, $tempPassword);
}

/**
 * Enviar email de recuperación de contraseña
 */
function sendPasswordResetEmail($userEmail, $userName, $resetToken) {
    require_once __DIR__ . '/../email-config/includes/email-sender.php';
    $emailSender = new EmailSender();
    return $emailSender->sendPasswordResetEmail($userEmail, $userName, $resetToken);
}

/**
 * Enviar notificación de nuevo pedido al admin
 */
function notifyAdminNewOrder($orderData) {
    require_once __DIR__ . '/../email-config/includes/email-sender.php';
    $emailSender = new EmailSender();
    return $emailSender->sendNewOrderNotification($orderData);
}

/**
 * Enviar confirmación de pedido al cliente
 */
function sendOrderConfirmation($customerEmail, $orderData) {
    require_once __DIR__ . '/../email-config/includes/email-sender.php';
    $emailSender = new EmailSender();
    return $emailSender->sendOrderConfirmation($customerEmail, $orderData);
}

/**
 * Probar configuración de email
 */
function testEmailConfiguration() {
    require_once __DIR__ . '/../email-config/includes/email-sender.php';
    $emailSender = new EmailSender();
    return $emailSender->testEmailConfiguration();
}

/**
 * Enviar email genérico
 */
function sendCustomEmail($to, $subject, $body, $isHTML = true) {
    require_once __DIR__ . '/../email-config/includes/email-sender.php';
    $emailSender = new EmailSender();
    return $emailSender->sendEmail($to, $subject, $body, $isHTML);
}

/**
 * Función para obtener información de contacto
 */
function getContactInfo() {
    return [
        'admin_email' => ADMIN_EMAIL,
        'contact_email' => CONTACT_EMAIL,
        'sales_email' => SALES_EMAIL,
        'whatsapp' => '5491123456789' // Cambiar por tu número real
    ];
}

/**
 * Función para validar email
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Función para sanitizar email
 */
function sanitizeEmail($email) {
    return filter_var($email, FILTER_SANITIZE_EMAIL);
}
?>
