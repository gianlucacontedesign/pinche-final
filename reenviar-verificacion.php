<?php
// =================================
// PÁGINA DE REENVÍO DE VERIFICACIÓN
// archivo: reenviar-verificacion.php
// =================================

require_once 'includes/config.php';

// Incluir función de envío de emails
require_once 'includes/email-sender.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    
    if ($email) {
        try {
            // Buscar usuario por email
            $stmt = $pdo->prepare("SELECT id, name, email_verified FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                // Si ya está verificado, no hacer nada
                if ($user['email_verified']) {
                    $message = "✅ Tu email ya está verificado. Puedes iniciar sesión normalmente.";
                    $messageType = "success";
                } else {
                    // Generar nuevo token
                    $newToken = bin2hex(random_bytes(32));
                    $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));
                    
                    // Actualizar token en base de datos
                    $updateStmt = $pdo->prepare("
                        UPDATE users 
                        SET verification_token = ?, verification_expires = ?
                        WHERE id = ?
                    ");
                    $updateStmt->execute([$newToken, $expiresAt, $user['id']]);
                    
                    // Enviar email de verificación
                    $verificationUrl = "https://pinchesupplies.com.ar/verificar-email.php?token=" . $newToken . "&email=" . urlencode($email);
                    
                    if (sendVerificationEmail($email, $user['name'], $verificationUrl)) {
                        $message = "📧 Email de verificación enviado. Revisa tu bandeja de entrada.";
                        $messageType = "success";
                    } else {
                        $message = "❌ Error al enviar el email. Intenta nuevamente.";
                        $messageType = "error";
                    }
                }
            } else {
                $message = "❌ No encontramos una cuenta con ese email.";
                $messageType = "error";
            }
            
        } catch(PDOException $e) {
            $message = "❌ Error en la base de datos. Intenta nuevamente.";
            $messageType = "error";
            error_log("Error reenvío verificación: " . $e->getMessage());
        }
    } else {
        $message = "❌ Email inválido.";
        $messageType = "error";
    }
}

// Redirigir con mensaje
$redirectUrl = isset($_POST['email']) ? 
    "verificar-email.php?email=" . urlencode($_POST['email']) : 
    "verificar-email.php";

header("Location: " . $redirectUrl . (isset($messageType) ? "&msg=" . urlencode($message) . "&type=" . $messageType : ""));
exit;
?>