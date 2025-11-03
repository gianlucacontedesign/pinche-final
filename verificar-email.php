<?php
// =================================
// PÁGINA DE VERIFICACIÓN DE EMAIL
// archivo: verificar-email.php
// =================================

require_once 'includes/config.php';

// Función para generar token único
function generateVerificationToken() {
    return bin2hex(random_bytes(32));
}

// Verificar si hay token en la URL
if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = $_GET['token'];
    
    try {
        // Buscar usuario con este token
        $stmt = $pdo->prepare("
            SELECT id, email, verification_expires, email_verified 
            FROM users 
            WHERE verification_token = ?
        ");
        $stmt->execute([$token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            // Verificar si el token ha expirado
            if ($user['verification_expires'] && strtotime($user['verification_expires']) < time()) {
                $error = "❌ El enlace de verificación ha expirado. Solicita un nuevo enlace.";
            } 
            // Verificar si ya está verificado
            elseif ($user['email_verified']) {
                $success = "✅ ¡Tu email ya está verificado! Puedes iniciar sesión normalmente.";
            }
            else {
                // Marcar email como verificado y limpiar token
                $updateStmt = $pdo->prepare("
                    UPDATE users 
                    SET email_verified = 1, 
                        verification_token = NULL, 
                        verification_expires = NULL 
                    WHERE id = ?
                ");
                $updateStmt->execute([$user['id']]);
                
                $success = "🎉 ¡Email verificado exitosamente! Tu cuenta está ahora activada.";
            }
        } else {
            $error = "❌ Token de verificación inválido. El enlace puede estar corrupto.";
        }
        
    } catch(PDOException $e) {
        $error = "❌ Error en la base de datos. Intenta nuevamente.";
        error_log("Error verificación email: " . $e->getMessage());
    }
} 
else {
    $error = "❌ Token de verificación faltante. Verifica el enlace completo.";
}

// Obtener email del parámetro para reenvío
$resendEmail = isset($_GET['email']) ? filter_var($_GET['email'], FILTER_VALIDATE_EMAIL) : false;

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar Email - Pinche Supplies</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            text-align: center;
            max-width: 500px;
            width: 90%;
        }
        .logo {
            font-size: 2.5em;
            color: #333;
            margin-bottom: 10px;
            font-weight: bold;
        }
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 1.1em;
        }
        .message {
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            font-size: 1.1em;
            line-height: 1.6;
        }
        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 25px;
            margin: 10px;
            font-weight: bold;
            transition: transform 0.3s ease;
        }
        .button:hover {
            transform: translateY(-2px);
        }
        .resend-form {
            margin-top: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
        }
        .email-input {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            margin: 10px 0;
            font-size: 1em;
        }
        .submit-btn {
            background: #28a745;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1em;
            font-weight: bold;
        }
        .submit-btn:hover {
            background: #218838;
        }
        .hidden {
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">🎯 Pinche Supplies</div>
        <div class="subtitle">Verificación de Email</div>
        
        <?php if (isset($success)): ?>
            <div class="message success">
                <?= $success ?>
            </div>
            <a href="login.php" class="button">🚀 Iniciar Sesión</a>
            <a href="index.php" class="button">🏠 Volver al Inicio</a>
            
        <?php elseif (isset($error)): ?>
            <div class="message error">
                <?= $error ?>
            </div>
            
            <?php if ($resendEmail): ?>
                <div class="resend-form">
                    <h3>📧 Reenviar Email de Verificación</h3>
                    <p>¿Quieres que te enviemos un nuevo enlace de verificación?</p>
                    <form action="reenviar-verificacion.php" method="POST">
                        <input type="hidden" name="email" value="<?= htmlspecialchars($resendEmail) ?>">
                        <button type="submit" class="submit-btn">📤 Enviar Nuevo Enlace</button>
                    </form>
                </div>
            <?php endif; ?>
            
            <a href="registro.php" class="button">📝 Registrarse Nuevamente</a>
            <a href="index.php" class="button">🏠 Volver al Inicio</a>
        <?php endif; ?>
    </div>
</body>
</html>