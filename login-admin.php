<?php
/**
 * Página de Login de Administradores
 */

require_once 'config/config.php';
require_once 'includes/class.database.php';
require_once 'includes/class.auth.php';

// La sesión ya fue iniciada en config.php

$auth = new Auth();

// Si ya está logueado como admin, redirigir al dashboard
if ($auth->isLoggedIn()) {
    header('Location: ' . ADMIN_URL . '/index.php');
    exit;
}

$error = '';
$success = '';

// Mensaje de éxito si viene desde logout
if (isset($_GET['logged_out'])) {
    $success = 'Sesión cerrada correctamente';
}

// Procesar formulario de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Por favor, completa todos los campos';
    } else {
        if ($auth->adminLogin($username, $password)) {
            header('Location: ' . ADMIN_URL . '/index.php');
            exit;
        } else {
            $error = 'Usuario o contraseña incorrectos';
        }
    }
}

$pageTitle = 'Iniciar Sesión - Panel de Administración - ' . SITE_NAME;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($pageTitle); ?></title>
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .admin-login-container {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-hover) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: var(--spacing-4);
        }
        
        .admin-login-card {
            background: var(--color-white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            padding: var(--spacing-12);
            width: 100%;
            max-width: 450px;
        }
        
        .admin-login-header {
            text-align: center;
            margin-bottom: var(--spacing-8);
        }
        
        .admin-login-title {
            font-size: var(--font-size-3xl);
            font-weight: 800;
            color: var(--color-primary);
            margin-bottom: var(--spacing-2);
        }
        
        .admin-login-subtitle {
            color: var(--color-gray-500);
            font-size: var(--font-size-lg);
        }
        
        .admin-login-form {
            space-y: var(--spacing-6);
        }
        
        .admin-login-footer {
            margin-top: var(--spacing-8);
            text-align: center;
            font-size: var(--font-size-sm);
            color: var(--color-gray-500);
        }
        
        .admin-credentials {
            background: var(--color-gray-100);
            padding: var(--spacing-6);
            border-radius: var(--radius-base);
            margin-top: var(--spacing-6);
            text-align: center;
        }
        
        .admin-credentials h4 {
            color: var(--color-primary);
            margin-bottom: var(--spacing-3);
            font-weight: 600;
        }
        
        .admin-credentials p {
            margin-bottom: var(--spacing-2);
            font-family: 'Monaco', 'Courier New', monospace;
            font-size: var(--font-size-sm);
        }
        
        .warning-text {
            color: var(--color-error);
            font-weight: 600;
            margin-top: var(--spacing-3);
        }
    </style>
</head>
<body>
    <div class="admin-login-container">
        <div class="admin-login-card">
            <div class="admin-login-header">
                <h1 class="admin-login-title"><?php echo e(SITE_NAME); ?></h1>
                <p class="admin-login-subtitle">Panel de Administración</p>
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
            
            <form method="POST" action="" class="admin-login-form" id="adminLoginForm">
                <div class="form-group">
                    <label for="username">Usuario</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        value="<?php echo isset($_POST['username']) ? e($_POST['username']) : ''; ?>"
                        required
                        autofocus
                        class="form-control"
                        placeholder="Tu usuario de administrador"
                        autocomplete="username">
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
                
                <button type="submit" class="btn btn-primary btn-block">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                    </svg>
                    Iniciar Sesión
                </button>
            </form>
            
            <div class="admin-login-footer">
                <p>
                    <a href="<?php echo SITE_URL; ?>/" class="link-primary">
                        ← Volver al sitio web
                    </a>
                </p>
            </div>
            
            <div class="admin-credentials">
                <h4>Credenciales por Defecto</h4>
                <p><strong>Usuario:</strong> admin</p>
                <p><strong>Contraseña:</strong> admin123</p>
                <p class="warning-text">
                    ⚠️ Cambiar en producción
                </p>
            </div>
        </div>
    </div>

    <script>
        // Toggle password visibility
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const input = document.getElementById(targetId);
                const icon = this.querySelector('.eye-icon');
                
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.innerHTML = `
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L12 12m3.121 3.121l4.242 4.242M21 21l-9-9"></path>
                    `;
                } else {
                    input.type = 'password';
                    icon.innerHTML = `
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    `;
                }
            });
        });
        
        // Auto-focus en primer campo si hay error
        <?php if ($error): ?>
        document.getElementById('username').focus();
        <?php endif; ?>
    </script>
</body>
</html>