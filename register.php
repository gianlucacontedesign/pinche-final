<?php
/**
 * Página de Registro de Clientes
 */

require_once 'config/config.php';
require_once 'includes/class.database.php';
require_once 'includes/class.customer.php';
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

// Procesar formulario de registro
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customerModel = new Customer();
    
    // Validar confirmación de contraseña
    if ($_POST['password'] !== $_POST['confirm_password']) {
        $error = 'Las contraseñas no coinciden';
    } else {
        // Intentar registrar
        $result = $customerModel->register([
            'email' => $_POST['email'] ?? '',
            'password' => $_POST['password'] ?? '',
            'first_name' => $_POST['first_name'] ?? '',
            'last_name' => $_POST['last_name'] ?? '',
            'phone' => $_POST['phone'] ?? null,
            'address' => $_POST['address'] ?? null,
            'city' => $_POST['city'] ?? null,
            'state' => $_POST['state'] ?? null,
            'zip_code' => $_POST['zip_code'] ?? null
        ]);
        
        if ($result['success']) {
            // Auto-login después del registro
            $auth->customerLogin($_POST['email'], $_POST['password']);
            header('Location: ' . SITE_URL . '/account.php?welcome=1');
            exit;
        } else {
            $error = $result['message'];
        }
    }
}

$pageTitle = 'Crear Cuenta - ' . SITE_NAME;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($pageTitle); ?></title>
    <meta name="description" content="Creá tu cuenta en <?php echo e(SITE_NAME); ?> y accedé a beneficios exclusivos: seguimiento de pedidos, ofertas especiales y más.">
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>

<div class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1>Crear Cuenta</h1>
                <p>Registrate para acceder a beneficios exclusivos</p>
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
            
            <form method="POST" action="" class="auth-form" id="registerForm">
                <!-- Información Personal -->
                <div class="form-section">
                    <h3>Información Personal</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name">Nombre <span class="required">*</span></label>
                            <input 
                                type="text" 
                                id="first_name" 
                                name="first_name" 
                                value="<?php echo isset($_POST['first_name']) ? e($_POST['first_name']) : ''; ?>"
                                required
                                class="form-control"
                                placeholder="Tu nombre">
                        </div>
                        
                        <div class="form-group">
                            <label for="last_name">Apellido <span class="required">*</span></label>
                            <input 
                                type="text" 
                                id="last_name" 
                                name="last_name" 
                                value="<?php echo isset($_POST['last_name']) ? e($_POST['last_name']) : ''; ?>"
                                required
                                class="form-control"
                                placeholder="Tu apellido">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email <span class="required">*</span></label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            value="<?php echo isset($_POST['email']) ? e($_POST['email']) : ''; ?>"
                            required
                            class="form-control"
                            placeholder="tu@email.com">
                        <small class="form-hint" id="emailAvailability"></small>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Teléfono</label>
                        <input 
                            type="tel" 
                            id="phone" 
                            name="phone" 
                            value="<?php echo isset($_POST['phone']) ? e($_POST['phone']) : ''; ?>"
                            class="form-control"
                            placeholder="+54 11 1234-5678">
                    </div>
                </div>
                
                <!-- Contraseña -->
                <div class="form-section">
                    <h3>Seguridad</h3>
                    
                    <div class="form-group">
                        <label for="password">Contraseña <span class="required">*</span></label>
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
                        <div class="password-strength" id="passwordStrength">
                            <div class="strength-bar"></div>
                            <small class="strength-text"></small>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirmar Contraseña <span class="required">*</span></label>
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
                        <small class="form-hint" id="passwordMatch"></small>
                    </div>
                </div>
                
                <!-- Dirección (Opcional) -->
                <div class="form-section form-section-collapsible">
                    <h3>
                        <button type="button" class="collapse-toggle" data-target="addressSection">
                            Dirección de Envío (Opcional)
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                    </h3>
                    
                    <div id="addressSection" class="collapsible-content" style="display: none;">
                        <div class="form-group">
                            <label for="address">Dirección</label>
                            <input 
                                type="text" 
                                id="address" 
                                name="address" 
                                value="<?php echo isset($_POST['address']) ? e($_POST['address']) : ''; ?>"
                                class="form-control"
                                placeholder="Calle y número">
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="city">Ciudad</label>
                                <input 
                                    type="text" 
                                    id="city" 
                                    name="city" 
                                    value="<?php echo isset($_POST['city']) ? e($_POST['city']) : ''; ?>"
                                    class="form-control"
                                    placeholder="Buenos Aires">
                            </div>
                            
                            <div class="form-group">
                                <label for="state">Provincia</label>
                                <input 
                                    type="text" 
                                    id="state" 
                                    name="state" 
                                    value="<?php echo isset($_POST['state']) ? e($_POST['state']) : ''; ?>"
                                    class="form-control"
                                    placeholder="CABA">
                            </div>
                            
                            <div class="form-group">
                                <label for="zip_code">Código Postal</label>
                                <input 
                                    type="text" 
                                    id="zip_code" 
                                    name="zip_code" 
                                    value="<?php echo isset($_POST['zip_code']) ? e($_POST['zip_code']) : ''; ?>"
                                    class="form-control"
                                    placeholder="1234">
                            </div>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    Crear Cuenta
                </button>
                
                <div class="auth-footer">
                    <p>¿Ya tenés cuenta? <a href="<?php echo SITE_URL; ?>/login.php">Iniciar Sesión</a></p>
                </div>
            </form>
        </div>
        
        <div class="auth-benefits">
            <h3>Beneficios de Registrarte</h3>
            <ul>
                <li>
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span>Seguimiento de tus pedidos en tiempo real</span>
                </li>
                <li>
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span>Historial completo de compras</span>
                </li>
                <li>
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span>Direcciones guardadas para comprar más rápido</span>
                </li>
                <li>
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span>Ofertas y descuentos exclusivos</span>
                </li>
            </ul>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
