<?php
/**
 * Página de Recuperación de Contraseña
 */

require_once 'config/config.php';
require_once 'includes/class.database.php';
require_once 'includes/class.customer.php';
require_once 'includes/class.auth.php';
require_once 'includes/functions.php';

// La sesión ya fue iniciada en config.php

// Si ya está logueado, redirigir
$auth = new Auth();
if ($auth->isCustomerLoggedIn()) {
    header('Location: ' . SITE_URL . '/account.php');
    exit;
}

$error = '';
$success = '';
$emailSent = false;

// Procesar solicitud de reset
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    
    if (empty($email)) {
        $error = 'Por favor, ingresa tu email';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'El formato del email no es válido';
    } else {
        $customerModel = new Customer();
        $result = $customerModel->requestPasswordReset($email);
        
        if ($result['success']) {
            $emailSent = true;
            
            // En producción, aquí se enviaría un email real
            // Por ahora, mostramos el link para testing (SOLO EN DESARROLLO)
            if (defined('DEVELOPMENT') && DEVELOPMENT === true) {
                $resetLink = SITE_URL . '/reset-password.php?token=' . $result['reset_token'];
                $success = 'Email de recuperación enviado. [DESARROLLO] <a href="' . $resetLink . '" target="_blank">Click aquí para resetear</a>';
            } else {
                $success = 'Si el email existe en nuestro sistema, recibirás instrucciones para resetear tu contraseña';
            }
        }
    }
}

$pageTitle = 'Recuperar Contraseña - ' . SITE_NAME;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($pageTitle); ?></title>
    <meta name="description" content="Recuperá tu contraseña de <?php echo e(SITE_NAME); ?> de forma segura.">
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
                <?php if ($emailSent): ?>
                    <div class="success-icon">
                        <svg width="64" height="64" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 19v-8.93a2 2 0 01.89-1.664l7-4.666a2 2 0 012.22 0l7 4.666A2 2 0 0121 10.07V19M3 19a2 2 0 002 2h14a2 2 0 002-2M3 19l6.75-4.5M21 19l-6.75-4.5M3 10l6.75 4.5M21 10l-6.75 4.5m0 0l-1.14.76a2 2 0 01-2.22 0l-1.14-.76"></path>
                        </svg>
                    </div>
                    <h1>¡Email Enviado!</h1>
                    <p>Revisá tu casilla de correo para continuar</p>
                <?php else: ?>
                    <h1>¿Olvidaste tu Contraseña?</h1>
                    <p>No te preocupes, te enviamos un link para que puedas crear una nueva</p>
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
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!$emailSent): ?>
                <form method="POST" action="" class="auth-form">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            value="<?php echo isset($_POST['email']) ? e($_POST['email']) : ''; ?>"
                            required
                            class="form-control"
                            placeholder="tu@email.com"
                            autocomplete="email">
                        <small class="form-hint">Ingresa el email asociado a tu cuenta</small>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">
                        Enviar Instrucciones
                    </button>
                    
                    <div class="auth-footer">
                        <a href="<?php echo SITE_URL; ?>/login.php" class="link-primary">
                            ← Volver al Login
                        </a>
                    </div>
                </form>
            <?php else: ?>
                <div class="auth-instructions">
                    <h3>Próximos pasos:</h3>
                    <ol>
                        <li>Revisá tu casilla de correo <strong><?php echo isset($_POST['email']) ? e($_POST['email']) : ''; ?></strong></li>
                        <li>Buscá el email de <?php echo e(SITE_NAME); ?> (puede estar en spam)</li>
                        <li>Hace click en el link para crear tu nueva contraseña</li>
                        <li>El link expira en 1 hora por seguridad</li>
                    </ol>
                    
                    <div class="auth-help">
                        <p>¿No recibiste el email?</p>
                        <a href="<?php echo SITE_URL; ?>/forgot-password.php" class="btn btn-outline">
                            Solicitar Nuevamente
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
