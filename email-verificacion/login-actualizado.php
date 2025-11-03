<?php
// =================================
// PÁGINA DE LOGIN ACTUALIZADA
// Para verificar que el email esté confirmado
// archivo: login.php
// =================================

require_once 'includes/config.php';

// Si ya está logueado, redirigir al dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$message = '';
$messageType = '';

// Incluir funciones actualizadas
require_once 'includes/funciones-registro-actualizado.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $message = '❌ Todos los campos son obligatorios.';
        $messageType = 'error';
    } else {
        $result = verificarLogin($email, $password);
        
        if ($result['success']) {
            // Login exitoso
            $_SESSION['user_id'] = $result['user']['id'];
            $_SESSION['user_name'] = $result['user']['name'];
            $_SESSION['user_email'] = $result['user']['email'];
            
            header('Location: dashboard.php');
            exit;
        } else {
            $message = $result['message'];
            $messageType = 'error';
            
            // Si el error es por email no verificado, mostrar opción de reenvío
            if (isset($result['email_not_verified']) && $result['email_not_verified']) {
                $pendingEmail = $result['user_email'];
                $showResendButton = true;
            }
        }
    }
}

// Manejar reenvío de email de verificación
if (isset($_POST['resend_verification']) && isset($_POST['email'])) {
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    
    if ($email) {
        $result = reenviarEmailVerificacion($email);
        $message = $result['message'];
        $messageType = $result['success'] ? 'success' : 'error';
    }
}

// Obtener parámetros de la URL (para mostrar mensajes desde otras páginas)
if (isset($_GET['msg'])) {
    $message = urldecode($_GET['msg']);
    $messageType = $_GET['type'] ?? 'info';
}

if (isset($_GET['email'])) {
    $pendingEmail = filter_var($_GET['email'], FILTER_VALIDATE_EMAIL);
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Pinche Supplies</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 450px;
            position: relative;
        }
        .logo {
            text-align: center;
            font-size: 2.5em;
            color: #333;
            margin-bottom: 10px;
            font-weight: bold;
        }
        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
            font-size: 1.1em;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: bold;
        }
        .form-input {
            width: 100%;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1em;
            transition: border-color 0.3s ease;
        }
        .form-input:focus {
            outline: none;
            border-color: #667eea;
        }
        .login-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1em;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.3s ease;
            margin-top: 10px;
        }
        .login-btn:hover {
            transform: translateY(-2px);
        }
        .message {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
        }
        .message.error {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }
        .message.success {
            background: #e8f5e8;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }
        .message.info {
            background: #e3f2fd;
            color: #1565c0;
            border: 1px solid #bbdefb;
        }
        .links {
            text-align: center;
            margin-top: 25px;
        }
        .link {
            color: #667eea;
            text-decoration: none;
            margin: 0 15px;
            font-weight: bold;
            transition: color 0.3s ease;
        }
        .link:hover {
            color: #764ba2;
        }
        .resend-section {
            background: #fff3e0;
            border: 1px solid #ffcc02;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
        }
        .resend-title {
            color: #f57c00;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .resend-btn {
            background: #ff9800;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            margin-top: 10px;
        }
        .resend-btn:hover {
            background: #f57c00;
        }
        .verification-notice {
            background: #e1f5fe;
            border: 1px solid #81d4fa;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            text-align: center;
        }
        .verification-notice .icon {
            font-size: 2em;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">🎯 Pinche Supplies</div>
        <div class="subtitle">Inicia sesión en tu cuenta</div>
        
        <?php if ($message): ?>
            <div class="message <?= $messageType ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($showResendButton) && $showResendButton): ?>
            <div class="verification-notice">
                <div class="icon">📧</div>
                <div><strong>Email no verificado</strong></div>
                <div style="margin-top: 10px; font-size: 0.9em;">
                    Debes verificar tu email antes de iniciar sesión.<br>
                    Revisa tu bandeja de entrada (y carpeta de spam).
                </div>
            </div>
            
            <div class="resend-section">
                <div class="resend-title">¿No recibiste el email?</div>
                <p>Podemos enviarte un nuevo enlace de verificación</p>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="email" value="<?= htmlspecialchars($pendingEmail) ?>">
                    <input type="hidden" name="resend_verification" value="1">
                    <button type="submit" class="resend-btn">📤 Reenviar Email de Verificación</button>
                </form>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label class="form-label">📧 Email</label>
                <input type="email" name="email" class="form-input" 
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">🔒 Contraseña</label>
                <input type="password" name="password" class="form-input" required>
            </div>
            
            <button type="submit" class="login-btn">🚀 Iniciar Sesión</button>
        </form>
        
        <div class="links">
            <a href="registro.php" class="link">📝 ¿No tienes cuenta? Regístrate</a>
            <a href="recuperar-password.php" class="link">🔐 ¿Olvidaste tu contraseña?</a>
        </div>
    </div>
</body>
</html>