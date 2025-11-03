<?php
/**
 * Página de Login de Clientes
 */

require_once 'config/config.php';
require_once 'includes/class.database.php';
require_once 'includes/class.auth.php';
require_once 'includes/functions.php';

// La sesión ya fue iniciada en config.php

// Si ya está logueado, redirigir a cuenta
$auth = new Auth();
if ($auth->isCustomerLoggedIn()) {
    header('Location: ' . SITE_URL . '/account.php');
    exit;
}

$error = '';
$success = '';

// Mensaje de éxito si viene desde registro
if (isset($_GET['registered'])) {
    $success = 'Cuenta creada exitosamente. ¡Bienvenido!';
}

// Procesar formulario de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']) ? true : false;
    
    if (empty($email) || empty($password)) {
        $error = 'Por favor, completa todos los campos';
    } else {
        if ($auth->customerLogin($email, $password, $remember)) {
            // Redirigir a página anterior o a cuenta
            $redirect = $_GET['redirect'] ?? SITE_URL . '/account.php';
            header('Location: ' . $redirect);
            exit;
        } else {
            $error = 'Email o contraseña incorrectos';
        }
    }
}

$pageTitle = 'Iniciar Sesión - ' . SITE_NAME;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($pageTitle); ?></title>
    <meta name="description" content="Iniciá sesión en <?php echo e(SITE_NAME); ?> para acceder a tu cuenta y disfrutar de beneficios exclusivos.">
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
                <h1>Iniciar Sesión</h1>
                <p>Accede a tu cuenta de <?php echo e(SITE_NAME); ?></p>
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
                    <?php echo e($success); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" class="auth-form" id="loginForm">
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
                </div>
                
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <div class="password-input">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            required
                            class="form-control"
                            placeholder="Tu contraseña"
                            autocomplete="current-password">
                        <button type="button" class="toggle-password" data-target="password">
                            <svg class="eye-icon" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <div class="form-row form-row-space-between">
                    <div class="form-check">
                        <input 
                            type="checkbox" 
                            id="remember" 
                            name="remember" 
                            class="form-check-input">
                        <label for="remember" class="form-check-label">
                            Recordarme
                        </label>
                    </div>
                    
                    <a href="<?php echo SITE_URL; ?>/forgot-password.php" class="link-primary">
                        ¿Olvidaste tu contraseña?
                    </a>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    Iniciar Sesión
                </button>
                
                <div class="auth-divider">
                    <span>o</span>
                </div>
                
                <a href="<?php echo SITE_URL; ?>/register.php" class="btn btn-outline btn-block">
                    Crear Nueva Cuenta
                </a>
                
                <div class="auth-footer">
                    <p class="text-center">
                        <small>
                            Al iniciar sesión aceptás nuestros 
                            <a href="<?php echo SITE_URL; ?>/terminos.php">Términos y Condiciones</a>
                        </small>
                    </p>
                </div>
            </form>
        </div>
        
        <div class="auth-benefits">
            <h3>¿Por qué crear una cuenta?</h3>
            <div class="benefit-card">
                <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
                <h4>Compras más Rápidas</h4>
                <p>Guardá tus direcciones y métodos de pago para comprar en segundos</p>
            </div>
            
            <div class="benefit-card">
                <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                </svg>
                <h4>Seguí tus Pedidos</h4>
                <p>Accede a tu historial completo y rastreá tus envíos en tiempo real</p>
            </div>
            
            <div class="benefit-card">
                <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path>
                </svg>
                <h4>Ofertas Exclusivas</h4>
                <p>Recibí descuentos y promociones especiales solo para clientes registrados</p>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
