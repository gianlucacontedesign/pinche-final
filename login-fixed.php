<?php
/**
 * Página de Login de Administradores - VERSIÓN CORREGIDA
 * Solución para Error HTTP 500
 * 
 * CORRECCIONES IMPLEMENTADAS:
 * - Definición de constantes faltantes (SITE_NAME, ASSETS_URL, SITE_URL)
 * - Inicio correcto de sesiones PHP
 * - Inclusión de clase Auth adaptada para esta estructura
 * - Validación de base de datos y dependencias
 * - Manejo seguro de errores
 */

// =============================================
// CONFIGURACIÓN BASE
// =============================================

// Iniciar sesión ANTES de cualquier otra cosa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configuración de errores para desarrollo
error_reporting(E_ALL);
ini_set('display_errors', 1);

// =============================================
// DEFINICIÓN DE CONSTANTES FALTANTES
// =============================================

// Configuración básica del sitio
define('SITE_NAME', 'Pinche Supplies');
define('APP_NAME', 'Pinche Supplies');
define('APP_URL', 'https://pinchesupplies.com.ar');
define('ADMIN_URL', 'https://pinchesupplies.com.ar/admin');
define('SITE_URL', 'https://pinchesupplies.com.ar');

// URLs de assets
define('ASSETS_URL', 'https://pinchesupplies.com.ar/assets');

// Configuración de base de datos (desde config.php)
define('DB_HOST', 'localhost');
define('DB_NAME', 'a0030995_pinche');
define('DB_USER', 'a0030995_pinche');
define('DB_PASS', 'vawuDU97zu');

// Configuración de seguridad
define('SECURITY_SALT', 'pinche_supplies_salt_2025');
define('SESSION_LIFETIME', 1800); // 30 minutos

// =============================================
// INCLUSIÓN DE DEPENDENCIAS
// =============================================

try {
    // Incluir clase Database
    if (!class_exists('Database')) {
        require_once __DIR__ . '/classes/Database.php';
    }
    
    // Incluir funciones auxiliares
    if (!function_exists('e')) {
        require_once __DIR__ . '/includes/functions.php';
    }
    
    // Incluir clase Auth (adaptada)
    if (!class_exists('Auth')) {
        require_once __DIR__ . '/includes/class.auth.php';
    }
    
} catch (Exception $e) {
    error_log("Error cargando dependencias: " . $e->getMessage());
    die("Error del sistema. Por favor, contacte al administrador.");
}

// =============================================
// CLASE AUTH SIMPLIFICADA
// =============================================

if (!class_exists('Auth')) {
    class Auth {
        private $db;
        
        public function __construct() {
            try {
                $this->db = Database::getInstance();
            } catch (Exception $e) {
                error_log("Error conectando DB en Auth: " . $e->getMessage());
                $this->db = null;
            }
        }
        
        /**
         * Verificar si el admin está logueado
         */
        public function isLoggedIn() {
            if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
                return false;
            }
            
            // Verificar timeout de sesión
            if (isset($_SESSION['admin_last_activity']) && 
                (time() - $_SESSION['admin_last_activity'] > SESSION_LIFETIME)) {
                $this->adminLogout();
                return false;
            }
            
            $_SESSION['admin_last_activity'] = time();
            return true;
        }
        
        /**
         * Login de administrador
         */
        public function adminLogin($username, $password) {
            // Credenciales por defecto
            $default_admin = [
                'username' => 'admin',
                'password' => 'admin123'
            ];
            
            // Verificar credenciales por defecto
            if ($username === $default_admin['username'] && $password === $default_admin['password']) {
                // Regenerar ID de sesión por seguridad
                session_regenerate_id(true);
                
                // Guardar datos en sesión
                $_SESSION['admin_id'] = 1;
                $_SESSION['admin_username'] = 'admin';
                $_SESSION['admin_name'] = 'Administrador';
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_last_activity'] = time();
                
                logActivity('admin_login', 'Login exitoso con credenciales por defecto');
                return true;
            }
            
            // Si llegamos aquí, las credenciales son incorrectas
            logActivity('admin_login_failed', "Intento de login fallido para usuario: $username");
            return false;
        }
        
        /**
         * Logout de administrador
         */
        public function adminLogout() {
            // Limpiar variables de sesión del admin
            unset($_SESSION['admin_id']);
            unset($_SESSION['admin_username']);
            unset($_SESSION['admin_name']);
            unset($_SESSION['admin_logged_in']);
            unset($_SESSION['admin_last_activity']);
            
            logActivity('admin_logout', 'Logout exitoso');
        }
    }
}

// =============================================
// LÓGICA DE LA PÁGINA
// =============================================

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

// Verificar conexión a base de datos
$db_connected = false;
try {
    if ($auth->db && $auth->db->testConnection()) {
        $db_connected = true;
    }
} catch (Exception $e) {
    error_log("Error verificando conexión DB: " . $e->getMessage());
}

// Procesar formulario de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar token CSRF básico
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
        $error = 'Error de seguridad. Por favor, intente nuevamente.';
    } elseif (!$db_connected) {
        $error = 'Error de conexión a la base de datos. Contacte al administrador.';
    } else {
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
}

// Generar token CSRF
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
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
        :root {
            --color-primary: #2563eb;
            --color-primary-hover: #1d4ed8;
            --color-white: #ffffff;
            --color-gray-100: #f3f4f6;
            --color-gray-500: #6b7280;
            --color-error: #ef4444;
            --color-success: #10b981;
            --spacing-2: 0.5rem;
            --spacing-3: 0.75rem;
            --spacing-4: 1rem;
            --spacing-6: 1.5rem;
            --spacing-8: 2rem;
            --spacing-12: 3rem;
            --radius-base: 0.375rem;
            --radius-lg: 0.5rem;
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            --font-size-sm: 0.875rem;
            --font-size-lg: 1.125rem;
            --font-size-3xl: 1.875rem;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #1f2937;
            background-color: #f9fafb;
        }
        
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
        
        .form-group {
            margin-bottom: var(--spacing-6);
        }
        
        .form-group label {
            display: block;
            margin-bottom: var(--spacing-2);
            font-weight: 500;
            color: #374151;
        }
        
        .form-control {
            width: 100%;
            padding: var(--spacing-3) var(--spacing-4);
            border: 1px solid #d1d5db;
            border-radius: var(--radius-base);
            font-size: var(--font-size-sm);
            transition: border-color 0.15s ease-in-out;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        
        .password-input {
            position: relative;
        }
        
        .toggle-password {
            position: absolute;
            right: var(--spacing-3);
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: var(--color-gray-500);
            padding: var(--spacing-2);
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: var(--spacing-3) var(--spacing-4);
            font-size: var(--font-size-sm);
            font-weight: 500;
            border-radius: var(--radius-base);
            transition: all 0.15s ease-in-out;
            cursor: pointer;
            text-decoration: none;
            border: none;
        }
        
        .btn-primary {
            background-color: var(--color-primary);
            color: var(--color-white);
            width: 100%;
        }
        
        .btn-primary:hover {
            background-color: var(--color-primary-hover);
        }
        
        .btn-block {
            width: 100%;
        }
        
        .alert {
            padding: var(--spacing-4);
            border-radius: var(--radius-base);
            margin-bottom: var(--spacing-6);
            display: flex;
            align-items: center;
            gap: var(--spacing-3);
        }
        
        .alert-error {
            background-color: #fef2f2;
            color: var(--color-error);
            border: 1px solid #fecaca;
        }
        
        .alert-success {
            background-color: #f0fdf4;
            color: var(--color-success);
            border: 1px solid #bbf7d0;
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
        
        .db-status {
            background: <?php echo $db_connected ? '#f0fdf4' : '#fef2f2'; ?>;
            color: <?php echo $db_connected ? '#10b981' : '#ef4444'; ?>;
            padding: var(--spacing-3);
            border-radius: var(--radius-base);
            margin-bottom: var(--spacing-4);
            text-align: center;
            font-size: var(--font-size-sm);
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
            
            <!-- Estado de la base de datos -->
            <div class="db-status">
                <?php if ($db_connected): ?>
                    ✓ Conexión a base de datos: OK
                <?php else: ?>
                    ⚠ Advertencia: Problema con la base de datos
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
                    <?php echo e($success); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" class="admin-login-form" id="adminLoginForm">
                <input type="hidden" name="csrf_token" value="<?php echo e($_SESSION['csrf_token']); ?>">
                
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
                    <a href="<?php echo SITE_URL; ?>/" class="btn btn-primary" style="width: auto; padding: var(--spacing-2) var(--spacing-4); font-size: var(--font-size-sm);">
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
        
        // Verificar estado de la conexión cada 30 segundos
        <?php if (!$db_connected): ?>
        setInterval(function() {
            fetch(window.location.href, {
                method: 'HEAD',
                cache: 'no-cache'
            }).then(response => {
                if (response.ok) {
                    location.reload();
                }
            }).catch(() => {
                console.log('Esperando conexión a BD...');
            });
        }, 30000);
        <?php endif; ?>
    </script>
</body>
</html>
