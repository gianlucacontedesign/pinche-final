<?php
/**
 * Página de Reset de Contraseña
 */

require_once 'config/config.php';
require_once 'includes/class.database.php';
require_once 'includes/class.customer.php';
require_once 'includes/class.auth.php';
require_once 'includes/functions.php';

// La sesión ya fue iniciada en config.php

$error = '';
$success = '';
$tokenValid = false;

// Verificar token
$token = $_GET['token'] ?? '';

if (empty($token)) {
    $error = 'Token de reset inválido';
} else {
    // Verificar que el token existe y es válido
    $customerModel = new Customer();
    $db = Database::getInstance();
    $sql = "SELECT * FROM customers WHERE reset_token = ? AND reset_token_expires > NOW() LIMIT 1";
    $customer = $db->fetchOne($sql, [$token]);
    
    if ($customer) {
        $tokenValid = true;
    } else {
        $error = 'El token ha expirado o no es válido. Por favor, solicita un nuevo link de recuperación.';
    }
}

// Procesar reset de contraseña
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tokenValid) {
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (empty($password) || empty($confirmPassword)) {
        $error = 'Por favor, completa todos los campos';
    } elseif ($password !== $confirmPassword) {
        $error = 'Las contraseñas no coinciden';
    } elseif (strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres';
    } else {
        $result = $customerModel->resetPassword($token, $password);
        
        if ($result['success']) {
            $success = true;
        } else {
            $error = $result['message'];
        }
    }
}

$pageTitle = 'Restablecer Contraseña - ' . SITE_NAME;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($pageTitle); ?></title>
    <meta name="description" content="Restablecé tu contraseña de <?php echo e(SITE_NAME); ?> de forma segura.">
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>

<div class="auth-page">
    <div class="auth-container auth-container-small">
        <div class="auth-card">
            <div class="auth-header">
                <?php if ($success): ?>
                    <div class="success-icon">
                        <svg width="64" height="64" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h1>¡Contraseña Actualizada!</h1>
                    <p>Tu contraseña ha sido cambiada exitosamente</p>
                <?php else: ?>
                    <h1>Nueva Contraseña</h1>
                    <p>Crea una contraseña segura para tu cuenta</p>
                <?php endif; ?>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                    <?php echo e($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    Tu contraseña ha sido actualizada correctamente
                </div>
                
                <div class="auth-success-actions">
                    <a href="<?php echo SITE_URL; ?>/login.php" class="btn btn-primary btn-block">
                        Iniciar Sesión
                    </a>
                </div>
            <?php elseif ($tokenValid): ?>
                <form method="POST" action="" class="auth-form">
                    <div class="form-group">
                        <label for="password">Nueva Contraseña</label>
                        <div class="password-input">
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                required
                                minlength="6"
                                class="form-control"
                                placeholder="Mínimo 6 caracteres">
                            <button type="button" class="toggle-password" data-target="password">
                                <svg class="eye-icon" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </button>
                        </div>
                        <small class="form-hint">Debe tener al menos 6 caracteres</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirmar Contraseña</label>
                        <div class="password-input">
                            <input 
                                type="password" 
                                id="confirm_password" 
                                name="confirm_password" 
                                required
                                minlength="6"
                                class="form-control"
                                placeholder="Repetir contraseña">
                            <button type="button" class="toggle-password" data-target="confirm_password">
                                <svg class="eye-icon" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">
                        Cambiar Contraseña
                    </button>
                </form>
            <?php else: ?>
                <div class="auth-error-actions">
                    <a href="<?php echo SITE_URL; ?>/forgot-password.php" class="btn btn-primary btn-block">
                        Solicitar Nuevo Link
                    </a>
                    <a href="<?php echo SITE_URL; ?>/login.php" class="link-primary">
                        Volver al Login
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
