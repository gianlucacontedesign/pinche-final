<?php
/**
 * CLASE DE ENVÍO DE EMAILS - PINCHE SUPPLIES
 * Sistema completo de envío de emails con PHPMailer
 */

require_once __DIR__ . '/../config-email.php';

class EmailSender {
    private $mailer;
    private $config;
    
    public function __construct() {
        $this->config = getSMTPConfig();
        $this->initMailer();
    }
    
    /**
     * Inicializar PHPMailer
     */
    private function initMailer() {
        // Para este ejemplo, usamos la función mail() nativa de PHP
        // En producción, considera usar PHPMailer o SwiftMailer
        
        $this->mailer = [
            'host' => $this->config['host'],
            'port' => $this->config['port'],
            'username' => $this->config['username'],
            'password' => $this->config['password'],
            'from_email' => $this->config['from_email'],
            'from_name' => $this->config['from_name'],
            'admin_email' => $this->config['admin_email']
        ];
    }
    
    /**
     * Enviar email básico usando mail() nativo de PHP
     */
    public function sendEmail($to, $subject, $body, $isHTML = true) {
        $headers = [];
        $headers[] = "MIME-Version: 1.0";
        $headers[] = "From: " . $this->mailer['from_name'] . " <" . $this->mailer['from_email'] . ">";
        $headers[] = "Reply-To: " . $this->mailer['admin_email'];
        $headers[] = "Return-Path: " . $this->mailer['from_email'];
        
        if ($isHTML) {
            $headers[] = "Content-Type: text/html; charset=UTF-8";
        } else {
            $headers[] = "Content-Type: text/plain; charset=UTF-8";
        }
        
        $headers_string = implode("\r\n", $headers);
        
        // Enviar email
        $sent = mail($to, $subject, $body, $headers_string);
        
        // Log del envío
        $this->logEmail($to, $subject, $sent);
        
        return $sent;
    }
    
    /**
     * Enviar email de registro de usuario
     */
    public function sendWelcomeEmail($userEmail, $userName, $tempPassword = null) {
        $subject = "¡Bienvenido a Pinche Supplies!";
        $body = $this->getTemplate('welcome', [
            'userName' => $userName,
            'userEmail' => $userEmail,
            'tempPassword' => $tempPassword,
            'siteUrl' => SITE_URL,
            'adminUrl' => ADMIN_URL
        ]);
        
        return $this->sendEmail($userEmail, $subject, $body, true);
    }
    
    /**
     * Enviar email de recuperación de contraseña
     */
    public function sendPasswordResetEmail($userEmail, $userName, $resetToken) {
        $subject = "Recupera tu contraseña - Pinche Supplies";
        $resetUrl = SITE_URL . "/reset-password.php?token=" . $resetToken;
        
        $body = $this->getTemplate('password-reset', [
            'userName' => $userName,
            'userEmail' => $userEmail,
            'resetUrl' => $resetUrl,
            'siteUrl' => SITE_URL
        ]);
        
        return $this->sendEmail($userEmail, $subject, $body, true);
    }
    
    /**
     * Enviar notificación de nuevo pedido al admin
     */
    public function sendNewOrderNotification($orderData) {
        $subject = "Nuevo Pedido #" . $orderData['order_number'];
        $adminEmail = $this->mailer['admin_email'];
        
        $body = $this->getTemplate('new-order', $orderData);
        
        return $this->sendEmail($adminEmail, $subject, $body, true);
    }
    
    /**
     * Enviar confirmación de pedido al cliente
     */
    public function sendOrderConfirmation($customerEmail, $orderData) {
        $subject = "Confirmación de Pedido #" . $orderData['order_number'];
        
        $body = $this->getTemplate('order-confirmation', $orderData);
        
        return $this->sendEmail($customerEmail, $subject, $body, true);
    }
    
    /**
     * Obtener template de email
     */
    private function getTemplate($templateName, $variables = []) {
        $templatePath = __DIR__ . "/../templates/{$templateName}.html";
        
        if (!file_exists($templatePath)) {
            return $this->getDefaultTemplate($templateName, $variables);
        }
        
        $template = file_get_contents($templatePath);
        
        // Reemplazar variables en el template
        foreach ($variables as $key => $value) {
            $template = str_replace('{{' . $key . '}}', $value, $template);
        }
        
        return $template;
    }
    
    /**
     * Template por defecto si no existe el archivo
     */
    private function getDefaultTemplate($templateName, $variables) {
        $content = isset($variables['content']) ? $variables['content'] : 'Contenido del email';
        $userName = isset($variables['userName']) ? $variables['userName'] : 'Usuario';
        
        return "
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f5f5f5; }
                .container { max-width: 600px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; }
                .header { background: #6b46c1; color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { padding: 20px; }
                .footer { background: #f8f9fa; padding: 15px; text-align: center; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Pinche Supplies</h1>
                </div>
                <div class='content'>
                    <h2>Hola {$userName},</h2>
                    {$content}
                </div>
                <div class='footer'>
                    <p>Pinche Supplies - Insumos Profesionales para Tatuajes</p>
                    <p><a href='" . SITE_URL . "'>pinchesupplies.com.ar</a></p>
                </div>
            </div>
        </body>
        </html>";
    }
    
    /**
     * Log de emails enviados
     */
    private function logEmail($to, $subject, $success) {
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'to' => $to,
            'subject' => $subject,
            'success' => $success ? 'YES' : 'NO'
        ];
        
        $logLine = json_encode($logData) . "\n";
        file_put_contents(__DIR__ . '/../logs/email.log', $logLine, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Verificar si el servidor soporta email
     */
    public function testEmailConfiguration() {
        $testEmail = $this->config['admin_email'];
        $subject = "Prueba de configuración de email - Pinche Supplies";
        $body = "<h2>Prueba exitosa</h2><p>Si recibes este email, la configuración de SMTP está funcionando correctamente.</p>";
        
        $result = $this->sendEmail($testEmail, $subject, $body, true);
        
        return [
            'success' => $result,
            'message' => $result ? 'Email de prueba enviado exitosamente' : 'Error al enviar email de prueba',
            'config' => $this->config
        ];
    }
}
?>
