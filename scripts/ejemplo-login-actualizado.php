<?php
/**
 * EJEMPLO DE LOGIN.PHP ACTUALIZADO
 * Con verificación por email
 */

// Incluir configuración y funciones
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Si ya está logueado, redirigir al dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

// Variables para mensajes
$error = '';
$success = '';
$showResendButton = false;
$pendingEmail = '';

// Procesar formulario de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Manejar reenvío de verificación primero
    if (isset($_POST['resend_verification']) && isset($_POST['email'])) {
        $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
        
        if ($email) {
            $result = reenviarEmailVerificacion($email);
            $success = $result['message'];
            $pendingEmail = $email;
        }
    } 
    // Procesar login normal
    else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($email) || empty($password)) {
            $error = '❌ Todos los campos son obligatorios.';
        } else {
            // Usar la función actualizada que verifica email confirmado
            $result = loginUsuario($email, $password);
            
            if ($result['success']) {
                // Login exitoso: crear sesión
                $_SESSION['user_id'] = $result['user']['id'];
                $_SESSION['user_name'] = $result['user']['name'];
                $_SESSION['user_email'] = $result['user']['email'];
                
                header('Location: dashboard.php');
                exit;
            } else {
                $error = $result['message'];
                
                // Si el error es por email no verificado
                if (isset($result['email_not_verified']) && $result['email_not_verified']) {
                    $pendingEmail = $result['user_email'];
                    $showResendButton = true;
                }
            }
        }
    }
}

// Obtener mensajes de la URL (de otras páginas)
if (isset($_GET['msg'])) {
    $success = urldecode($_GET['msg']);
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
    <link href="css/style.css" rel="stylesheet">
    <style>
        .login-container {
            max-width: 450px;
            margin: 50px auto;
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: bold;
        }
        
        .form-group input {
            width: 100%;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1em;
            transition: border-color 0.3s ease;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .btn-primary {
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
        
        .btn-primary:hover {
            transform: translateY(-2px);
        }
        
        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
        }
        
        .alert-error {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }
        
        .alert-success {
            background: #e8f5e8;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }
        
        .verification-notice {
            background: #e1f5fe;
            border: 1px solid #81d4fa;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .resend-section {
            background: #fff3e0;
            border: 1px solid #ffcc02;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
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
        
        .form-links {
            text-align: center;
            margin-top: 25px;
        }
        
        .form-links a {
            color: #667eea;
            text-decoration: none;
            margin: 0 15px;
            font-weight: bold;
        }
        
        .form-links a:hover {
            color: #764ba2;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div style="text-align: center; margin-bottom: 30px;">
                <h1 style="font-size: 2.5em; color: #333; margin-bottom: 10px;">🎯 Pinche Supplies</h1>
                <p style="color: #666;">Inicia sesión en tu cuenta</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?= $error ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?= $success ?>
                </div>
            <?php endif; ?>
            
            <?php if ($showResendButton): ?>
                <div class="verification-notice">
                    <div style="font-size: 2em; margin-bottom: 10px;">📧</div>
                    <div><strong>Email no verificado</strong></div>
                    <div style="margin-top: 10px; font-size: 0.9em;">
                        Debes verificar tu email antes de iniciar sesión.<br>
                        Revisa tu bandeja de entrada (y carpeta de spam).
                    </div>
                </div>
                
                <div class="resend-section">
                    <div style="color: #f57c00; font-weight: bold; margin-bottom: 10px;">
                        ¿No recibiste el email?
                    </div>
                    <p>Podemos enviarte un nuevo enlace de verificación</p>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="email" value="<?= e($pendingEmail) ?>">
                        <input type="hidden" name="resend_verification" value="1">
                        <button type="submit" class="resend-btn">📤 Reenviar Email de Verificación</button>
                    </form>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">📧 Email</label>
                    <input type="email" id="email" name="email" 
                           value="<?= e($_POST['email'] ?? '') ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="password">🔒 Contraseña</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn-primary">🚀 Iniciar Sesión</button>
            </form>
            
            <div class="form-links">
                <a href="registro.php">📝 ¿No tienes cuenta? Regístrate</a>
                <a href="recuperar-password.php">🔐 ¿Olvidaste tu contraseña?</a>
            </div>
        </div>
    </div>
</body>
</html>