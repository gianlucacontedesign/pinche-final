<?php
// =================================
// SISTEMA DE ENVÍO DE EMAILS
// Actualizado para verificación por email
// archivo: includes/email-sender.php
// =================================

if (!class_exists('EmailSender')) {
    class EmailSender {
        private $smtp_host;
        private $smtp_port;
        private $smtp_user;
        private $smtp_pass;
        private $admin_email;
        private $site_name = "Pinche Supplies";
        
        public function __construct() {
            $this->smtp_host = defined('SMTP_HOST') ? SMTP_HOST : 'mail.pinchesupplies.com.ar';
            $this->smtp_port = defined('SMTP_PORT') ? SMTP_PORT : 587;
            $this->smtp_user = defined('SMTP_USERNAME') ? SMTP_USERNAME : 'noreply@pinchesupplies.com.ar';
            $this->smtp_pass = defined('SMTP_PASSWORD') ? SMTP_PASSWORD : '';
            $this->admin_email = defined('ADMIN_EMAIL') ? ADMIN_EMAIL : 'admin@pinchesupplies.com.ar';
        }
        
        private function sendEmail($to, $subject, $htmlContent, $textContent = null) {
            $headers = [
                'MIME-Version: 1.0',
                'Content-type: text/html; charset=UTF-8',
                'From: ' . $this->site_name . ' <' . $this->smtp_user . '>',
                'Reply-To: ' . $this->admin_email,
                'X-Mailer: PHP/' . phpversion()
            ];
            
            return mail($to, $subject, $htmlContent, implode("\r\n", $headers));
        }
        
        // =================================
        // EMAIL DE VERIFICACIÓN (NUEVO)
        // =================================
        public function sendVerificationEmail($to, $name, $verificationUrl) {
            $subject = "✅ Verifica tu email - " . $this->site_name;
            
            $htmlContent = $this->loadTemplate('email-verification.html', [
                'name' => $name,
                'verification_url' => $verificationUrl,
                'site_name' => $this->site_name,
                'whatsapp' => '5491123456789',
                'admin_email' => $this->admin_email,
                'support_email' => 'info@pinchesupplies.com.ar'
            ]);
            
            return $this->sendEmail($to, $subject, $htmlContent);
        }
        
        // =================================
        // EMAIL DE BIENVENIDA (ACTUALIZADO)
        // =================================
        public function sendWelcomeEmail($to, $name, $verificationUrl) {
            $subject = "🎉 ¡Bienvenido a " . $this->site_name . "!";
            
            $htmlContent = $this->loadTemplate('welcome.html', [
                'name' => $name,
                'verification_url' => $verificationUrl,
                'site_name' => $this->site_name,
                'whatsapp' => '5491123456789',
                'admin_email' => $this->admin_email,
                'support_email' => 'info@pinchesupplies.com.ar'
            ]);
            
            return $this->sendEmail($to, $subject, $htmlContent);
        }
        
        // =================================
        // RESET DE CONTRASEÑA
        // =================================
        public function sendPasswordReset($to, $name, $resetUrl) {
            $subject = "🔐 Restablecer contraseña - " . $this->site_name;
            
            $htmlContent = $this->loadTemplate('password-reset.html', [
                'name' => $name,
                'reset_url' => $resetUrl,
                'site_name' => $this->site_name,
                'whatsapp' => '5491123456789',
                'admin_email' => $this->admin_email,
                'support_email' => 'info@pinchesupplies.com.ar'
            ]);
            
            return $this->sendEmail($to, $subject, $htmlContent);
        }
        
        // =================================
        // NOTIFICACIÓN DE NUEVA ORDEN (ADMIN)
        // =================================
        public function sendNewOrderNotification($admin_email, $orderData) {
            $subject = "🆕 Nueva Orden #" . $orderData['id'] . " - " . $this->site_name;
            
            $htmlContent = $this->loadTemplate('new-order.html', [
                'order' => $orderData,
                'site_name' => $this->site_name,
                'whatsapp' => '5491123456789',
                'admin_email' => $admin_email
            ]);
            
            return $this->sendEmail($admin_email, $subject, $htmlContent);
        }
        
        // =================================
        // CONFIRMACIÓN DE ORDEN (CLIENTE)
        // =================================
        public function sendOrderConfirmation($to, $name, $orderData) {
            $subject = "📦 Orden #" . $orderData['id'] . " confirmada - " . $this->site_name;
            
            $htmlContent = $this->loadTemplate('order-confirmation.html', [
                'name' => $name,
                'order' => $orderData,
                'site_name' => $this->site_name,
                'whatsapp' => '5491123456789',
                'admin_email' => $this->admin_email,
                'support_email' => 'info@pinchesupplies.com.ar'
            ]);
            
            return $this->sendEmail($to, $subject, $htmlContent);
        }
        
        // =================================
        // FUNCIÓN AUXILIAR PARA CARGAR TEMPLATES
        // =================================
        private function loadTemplate($templateName, $variables = []) {
            $templatePath = __DIR__ . '/../templates/' . $templateName;
            
            if (!file_exists($templatePath)) {
                error_log("Template no encontrado: " . $templatePath);
                return $this->getFallbackTemplate($templateName, $variables);
            }
            
            $template = file_get_contents($templatePath);
            
            // Reemplazar variables
            foreach ($variables as $key => $value) {
                $template = str_replace('{{' . $key . '}}', $value, $template);
            }
            
            return $template;
        }
        
        // =================================
        // TEMPLATE DE FALLBACK
        // =================================
        private function getFallbackTemplate($templateName, $variables) {
            $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Email - ' . $this->site_name . '</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; padding: 20px 0; }
        .logo { font-size: 2em; font-weight: bold; color: #667eea; }
        .content { padding: 20px; background: #f9f9f9; }
        .button { display: inline-block; background: #667eea; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; margin: 10px 0; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 0.9em; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">🎯 ' . $this->site_name . '</div>
        </div>
        <div class="content">';
            
            if ($templateName === 'email-verification.html') {
                $html .= '
            <h2>✅ Verifica tu Email</h2>
            <p>Hola <strong>' . $variables['name'] . '</strong>,</p>
            <p>Gracias por registrarte en ' . $this->site_name . '. Para activar tu cuenta, haz clic en el siguiente botón:</p>
            <a href="' . $variables['verification_url'] . '" class="button">✅ Verificar Email</a>
            <p>Si no funciona el botón, copia y pega este enlace en tu navegador:</p>
            <p style="font-size: 0.9em; color: #666;">' . $variables['verification_url'] . '</p>';
            }
            
            $html .= '
        </div>
        <div class="footer">
            <p>© 2024 ' . $this->site_name . '. Todos los derechos reservados.</p>
            <p>WhatsApp: <a href="https://wa.me/' . $variables['whatsapp'] . '">' . $variables['whatsapp'] . '</a></p>
        </div>
    </div>
</body>
</html>';
            
            return $html;
        }
    }
}

// =================================
// FUNCIONES DE CONVENIENCIA
// =================================

function sendVerificationEmail($to, $name, $verificationUrl) {
    $emailSender = new EmailSender();
    return $emailSender->sendVerificationEmail($to, $name, $verificationUrl);
}

function sendWelcomeEmail($to, $name, $verificationUrl = null) {
    $emailSender = new EmailSender();
    return $emailSender->sendWelcomeEmail($to, $name, $verificationUrl);
}

function sendPasswordReset($to, $name, $resetUrl) {
    $emailSender = new EmailSender();
    return $emailSender->sendPasswordReset($to, $name, $resetUrl);
}

function sendNewOrderNotification($admin_email, $orderData) {
    $emailSender = new EmailSender();
    return $emailSender->sendNewOrderNotification($admin_email, $orderData);
}

function sendOrderConfirmation($to, $name, $orderData) {
    $emailSender = new EmailSender();
    return $emailSender->sendOrderConfirmation($to, $name, $orderData);
}
?>