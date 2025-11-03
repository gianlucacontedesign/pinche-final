<?php
/**
 * Página de Configuración del Sistema
 */

require_once 'config/config.php';
require_once 'includes/class.database.php';
require_once 'includes/class.auth.php';

// Verificar que el usuario esté logueado como admin
$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header('Location: ' . ADMIN_URL . '/login.php');
    exit;
}

$message = '';
$error = '';

// Cargar configuraciones existentes
$settingsFile = dirname(__DIR__) . '/config/system-settings.json';
$settings = [];
if (file_exists($settingsFile)) {
    $settings = json_decode(file_get_contents($settingsFile), true);
}

// Procesar formulario de configuración
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Configuraciones generales
        $settings['site_name'] = $_POST['site_name'] ?? '';
        $settings['site_description'] = $_POST['site_description'] ?? '';
        $settings['site_email'] = $_POST['site_email'] ?? '';
        $settings['site_phone'] = $_POST['site_phone'] ?? '';
        $settings['site_address'] = $_POST['site_address'] ?? '';
        
        // Configuraciones de email
        $settings['email_smtp_host'] = $_POST['email_smtp_host'] ?? '';
        $settings['email_smtp_port'] = (int)($_POST['email_smtp_port'] ?? 587);
        $settings['email_smtp_username'] = $_POST['email_smtp_username'] ?? '';
        $settings['email_smtp_password'] = $_POST['email_smtp_password'] ?? '';
        $settings['email_from_name'] = $_POST['email_from_name'] ?? '';
        $settings['email_from_email'] = $_POST['email_from_email'] ?? '';
        $settings['email_enable'] = isset($_POST['email_enable']) ? true : false;
        
        // Configuraciones de comercio
        $settings['currency_symbol'] = $_POST['currency_symbol'] ?? '$';
        $settings['currency_code'] = $_POST['currency_code'] ?? 'USD';
        $settings['tax_rate'] = (float)($_POST['tax_rate'] ?? 0);
        $settings['shipping_cost'] = (float)($_POST['shipping_cost'] ?? 0);
        $settings['free_shipping_threshold'] = (float)($_POST['free_shipping_threshold'] ?? 0);
        
        // Configuraciones de mantenimiento
        $settings['maintenance_mode'] = isset($_POST['maintenance_mode']) ? true : false;
        $settings['maintenance_message'] = $_POST['maintenance_message'] ?? '';
        
        // Configuraciones de backup automático
        $settings['auto_backup'] = isset($_POST['auto_backup']) ? true : false;
        $settings['backup_frequency'] = $_POST['backup_frequency'] ?? 'daily';
        $settings['backup_retention'] = (int)($_POST['backup_retention'] ?? 30);
        
        // Configuraciones de seguridad
        $settings['max_login_attempts'] = (int)($_POST['max_login_attempts'] ?? 5);
        $settings['session_timeout'] = (int)($_POST['session_timeout'] ?? 3600);
        $settings['require_email_verification'] = isset($_POST['require_email_verification']) ? true : false;
        $settings['allow_registration'] = isset($_POST['allow_registration']) ? true : false;
        
        // Validaciones básicas
        if (empty($settings['site_name'])) {
            throw new Exception('El nombre del sitio es requerido');
        }
        
        if (!filter_var($settings['site_email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception('El email del sitio no es válido');
        }
        
        if ($settings['email_enable'] && !filter_var($settings['email_from_email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception('El email de envío no es válido');
        }
        
        // Crear directorio de configuración si no existe
        $configDir = dirname($settingsFile);
        if (!is_dir($configDir)) {
            mkdir($configDir, 0755, true);
        }
        
        // Guardar configuraciones
        if (file_put_contents($settingsFile, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
            $message = 'Configuración guardada exitosamente';
            
            // Registrar en log
            $logMessage = "[" . date('Y-m-d H:i:s') . "] Configuración del sistema actualizada por " . ($_SESSION['admin_username'] ?? 'admin') . "\n";
            file_put_contents(dirname(__DIR__) . '/logs/config.log', $logMessage, FILE_APPEND | LOCK_EX);
        } else {
            throw new Exception('Error al guardar la configuración');
        }
        
    } catch (Exception $e) {
        $error = 'Error: ' . $e->getMessage();
    }
}

$pageTitle = 'Configuración del Sistema - Panel de Administración';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($pageTitle); ?></title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .settings-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: var(--spacing-6);
        }
        
        .settings-header {
            background: var(--color-white);
            border-radius: var(--radius-lg);
            padding: var(--spacing-8);
            margin-bottom: var(--spacing-6);
            box-shadow: var(--shadow-base);
        }
        
        .settings-title {
            font-size: var(--font-size-3xl);
            font-weight: 800;
            color: var(--color-primary);
            margin-bottom: var(--spacing-2);
        }
        
        .settings-section {
            background: var(--color-white);
            border-radius: var(--radius-lg);
            padding: var(--spacing-8);
            margin-bottom: var(--spacing-6);
            box-shadow: var(--shadow-base);
        }
        
        .settings-section-title {
            font-size: var(--font-size-xl);
            font-weight: 700;
            color: var(--color-gray-900);
            margin-bottom: var(--spacing-6);
            padding-bottom: var(--spacing-3);
            border-bottom: 2px solid var(--color-gray-100);
        }
        
        .settings-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--spacing-6);
        }
        
        .settings-grid-full {
            grid-column: 1 / -1;
        }
        
        @media (max-width: 768px) {
            .settings-grid {
                grid-template-columns: 1fr;
            }
        }
        
        .form-group-inline {
            display: flex;
            align-items: center;
            gap: var(--spacing-4);
        }
        
        .settings-actions {
            background: var(--color-white);
            border-radius: var(--radius-lg);
            padding: var(--spacing-8);
            box-shadow: var(--shadow-base);
            position: sticky;
            bottom: var(--spacing-6);
        }
    </style>
</head>
<body>
    <div class="settings-container">
        <div class="settings-header">
            <h1 class="settings-title">
                <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right: var(--spacing-3); vertical-align: middle;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                Configuración del Sistema
            </h1>
            <p style="color: var(--color-gray-500); font-size: var(--font-size-lg);">
                Personaliza los ajustes generales de tu sitio web.
            </p>
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-success" style="margin-bottom: var(--spacing-6);">
                <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                <?php echo e($message); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error" style="margin-bottom: var(--spacing-6);">
                <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
                <?php echo e($error); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="" id="settingsForm">
            <!-- Configuración General -->
            <div class="settings-section">
                <h3 class="settings-section-title">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right: var(--spacing-2); vertical-align: middle;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    Información General
                </h3>
                
                <div class="settings-grid">
                    <div class="form-group">
                        <label for="site_name">Nombre del Sitio *</label>
                        <input type="text" id="site_name" name="site_name" 
                               value="<?php echo e($settings['site_name'] ?? SITE_NAME); ?>" 
                               required class="form-control" placeholder="Mi Tienda Online">
                    </div>
                    
                    <div class="form-group">
                        <label for="site_email">Email del Sitio *</label>
                        <input type="email" id="site_email" name="site_email" 
                               value="<?php echo e($settings['site_email'] ?? ''); ?>" 
                               required class="form-control" placeholder="contacto@misitio.com">
                    </div>
                    
                    <div class="form-group settings-grid-full">
                        <label for="site_description">Descripción del Sitio</label>
                        <textarea id="site_description" name="site_description" rows="3" 
                                  class="form-control" placeholder="Descripción de tu sitio web"><?php echo e($settings['site_description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="site_phone">Teléfono</label>
                        <input type="tel" id="site_phone" name="site_phone" 
                               value="<?php echo e($settings['site_phone'] ?? ''); ?>" 
                               class="form-control" placeholder="+1 234 567 8900">
                    </div>
                    
                    <div class="form-group">
                        <label for="site_address">Dirección</label>
                        <input type="text" id="site_address" name="site_address" 
                               value="<?php echo e($settings['site_address'] ?? ''); ?>" 
                               class="form-control" placeholder="Calle 123, Ciudad">
                    </div>
                </div>
            </div>
            
            <!-- Configuración de Email -->
            <div class="settings-section">
                <h3 class="settings-section-title">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right: var(--spacing-2); vertical-align: middle;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    Configuración de Email
                </h3>
                
                <div class="settings-grid">
                    <div class="form-group-inline">
                        <input type="checkbox" id="email_enable" name="email_enable" 
                               <?php echo ($settings['email_enable'] ?? false) ? 'checked' : ''; ?>>
                        <label for="email_enable" style="margin: 0;">Habilitar Sistema de Email</label>
                    </div>
                    
                    <div class="form-group">
                        <label for="email_from_name">Nombre del Remitente</label>
                        <input type="text" id="email_from_name" name="email_from_name" 
                               value="<?php echo e($settings['email_from_name'] ?? ''); ?>" 
                               class="form-control" placeholder="Mi Tienda">
                    </div>
                    
                    <div class="form-group">
                        <label for="email_from_email">Email del Remitente</label>
                        <input type="email" id="email_from_email" name="email_from_email" 
                               value="<?php echo e($settings['email_from_email'] ?? ''); ?>" 
                               class="form-control" placeholder="noreply@misitio.com">
                    </div>
                    
                    <div class="form-group">
                        <label for="email_smtp_host">Servidor SMTP</label>
                        <input type="text" id="email_smtp_host" name="email_smtp_host" 
                               value="<?php echo e($settings['email_smtp_host'] ?? 'smtp.gmail.com'); ?>" 
                               class="form-control" placeholder="smtp.gmail.com">
                    </div>
                    
                    <div class="form-group">
                        <label for="email_smtp_port">Puerto SMTP</label>
                        <input type="number" id="email_smtp_port" name="email_smtp_port" 
                               value="<?php echo e($settings['email_smtp_port'] ?? 587); ?>" 
                               class="form-control" min="1" max="65535">
                    </div>
                    
                    <div class="form-group">
                        <label for="email_smtp_username">Usuario SMTP</label>
                        <input type="text" id="email_smtp_username" name="email_smtp_username" 
                               value="<?php echo e($settings['email_smtp_username'] ?? ''); ?>" 
                               class="form-control" placeholder="tu-email@gmail.com">
                    </div>
                    
                    <div class="form-group">
                        <label for="email_smtp_password">Contraseña SMTP</label>
                        <input type="password" id="email_smtp_password" name="email_smtp_password" 
                               value="<?php echo e($settings['email_smtp_password'] ?? ''); ?>" 
                               class="form-control" placeholder="••••••••">
                    </div>
                </div>
            </div>
            
            <!-- Configuración de Comercio -->
            <div class="settings-section">
                <h3 class="settings-section-title">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right: var(--spacing-2); vertical-align: middle;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                    Configuración de Comercio
                </h3>
                
                <div class="settings-grid">
                    <div class="form-group">
                        <label for="currency_symbol">Símbolo de Moneda</label>
                        <input type="text" id="currency_symbol" name="currency_symbol" 
                               value="<?php echo e($settings['currency_symbol'] ?? '$'); ?>" 
                               maxlength="5" class="form-control" placeholder="$">
                    </div>
                    
                    <div class="form-group">
                        <label for="currency_code">Código de Moneda</label>
                        <input type="text" id="currency_code" name="currency_code" 
                               value="<?php echo e($settings['currency_code'] ?? 'USD'); ?>" 
                               maxlength="3" class="form-control" placeholder="USD">
                    </div>
                    
                    <div class="form-group">
                        <label for="tax_rate">Tasa de Impuestos (%)</label>
                        <input type="number" id="tax_rate" name="tax_rate" 
                               value="<?php echo e($settings['tax_rate'] ?? 0); ?>" 
                               step="0.01" min="0" max="100" class="form-control" placeholder="21.00">
                    </div>
                    
                    <div class="form-group">
                        <label for="shipping_cost">Costo de Envío</label>
                        <input type="number" id="shipping_cost" name="shipping_cost" 
                               value="<?php echo e($settings['shipping_cost'] ?? 0); ?>" 
                               step="0.01" min="0" class="form-control" placeholder="10.00">
                    </div>
                    
                    <div class="form-group">
                        <label for="free_shipping_threshold">Envío Gratis desde</label>
                        <input type="number" id="free_shipping_threshold" name="free_shipping_threshold" 
                               value="<?php echo e($settings['free_shipping_threshold'] ?? 0); ?>" 
                               step="0.01" min="0" class="form-control" placeholder="50.00">
                    </div>
                </div>
            </div>
            
            <!-- Configuración de Seguridad -->
            <div class="settings-section">
                <h3 class="settings-section-title">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right: var(--spacing-2); vertical-align: middle;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    Configuración de Seguridad
                </h3>
                
                <div class="settings-grid">
                    <div class="form-group">
                        <label for="max_login_attempts">Máximo Intentos de Login</label>
                        <input type="number" id="max_login_attempts" name="max_login_attempts" 
                               value="<?php echo e($settings['max_login_attempts'] ?? 5); ?>" 
                               min="1" max="20" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label for="session_timeout">Tiempo de Sesión (segundos)</label>
                        <input type="number" id="session_timeout" name="session_timeout" 
                               value="<?php echo e($settings['session_timeout'] ?? 3600); ?>" 
                               min="300" max="86400" class="form-control" 
                               placeholder="3600 = 1 hora">
                    </div>
                    
                    <div class="form-group-inline">
                        <input type="checkbox" id="require_email_verification" name="require_email_verification" 
                               <?php echo ($settings['require_email_verification'] ?? false) ? 'checked' : ''; ?>>
                        <label for="require_email_verification" style="margin: 0;">Requerir Verificación de Email</label>
                    </div>
                    
                    <div class="form-group-inline">
                        <input type="checkbox" id="allow_registration" name="allow_registration" 
                               <?php echo ($settings['allow_registration'] ?? true) ? 'checked' : ''; ?>>
                        <label for="allow_registration" style="margin: 0;">Permitir Registro de Usuarios</label>
                    </div>
                </div>
            </div>
            
            <!-- Configuración de Mantenimiento -->
            <div class="settings-section">
                <h3 class="settings-section-title">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right: var(--spacing-2); vertical-align: middle;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    Modo Mantenimiento
                </h3>
                
                <div class="settings-grid">
                    <div class="form-group-inline settings-grid-full">
                        <input type="checkbox" id="maintenance_mode" name="maintenance_mode" 
                               <?php echo ($settings['maintenance_mode'] ?? false) ? 'checked' : ''; ?>>
                        <label for="maintenance_mode" style="margin: 0;">Activar Modo Mantenimiento</label>
                    </div>
                    
                    <div class="form-group settings-grid-full">
                        <label for="maintenance_message">Mensaje de Mantenimiento</label>
                        <textarea id="maintenance_message" name="maintenance_message" rows="4" 
                                  class="form-control" placeholder="El sitio estará en mantenimiento por un tiempo breve..."><?php echo e($settings['maintenance_message'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>
            
            <!-- Configuración de Backup Automático -->
            <div class="settings-section">
                <h3 class="settings-section-title">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right: var(--spacing-2); vertical-align: middle;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                    Backup Automático
                </h3>
                
                <div class="settings-grid">
                    <div class="form-group-inline">
                        <input type="checkbox" id="auto_backup" name="auto_backup" 
                               <?php echo ($settings['auto_backup'] ?? false) ? 'checked' : ''; ?>>
                        <label for="auto_backup" style="margin: 0;">Activar Backup Automático</label>
                    </div>
                    
                    <div class="form-group">
                        <label for="backup_frequency">Frecuencia</label>
                        <select id="backup_frequency" name="backup_frequency" class="form-control">
                            <option value="daily" <?php echo ($settings['backup_frequency'] ?? 'daily') === 'daily' ? 'selected' : ''; ?>>Diario</option>
                            <option value="weekly" <?php echo ($settings['backup_frequency'] ?? 'daily') === 'weekly' ? 'selected' : ''; ?>>Semanal</option>
                            <option value="monthly" <?php echo ($settings['backup_frequency'] ?? 'daily') === 'monthly' ? 'selected' : ''; ?>>Mensual</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="backup_retention">Días de Retención</label>
                        <input type="number" id="backup_retention" name="backup_retention" 
                               value="<?php echo e($settings['backup_retention'] ?? 30); ?>" 
                               min="1" max="365" class="form-control" placeholder="30">
                    </div>
                </div>
            </div>
            
            <!-- Acciones -->
            <div class="settings-actions">
                <div style="display: flex; gap: var(--spacing-4); justify-content: space-between; align-items: center;">
                    <div>
                        <p style="color: var(--color-gray-500); font-size: var(--font-size-sm); margin: 0;">
                            ⚠️ Los cambios se guardarán inmediatamente
                        </p>
                    </div>
                    <div style="display: flex; gap: var(--spacing-4);">
                        <button type="button" class="btn btn-outline" onclick="resetForm()">
                            Restablecer
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Guardar Configuración
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
    
    <script>
        function resetForm() {
            if (confirm('¿Estás seguro de restablecer todos los valores? Se perderán los cambios no guardados.')) {
                document.getElementById('settingsForm').reset();
            }
        }
        
        // Auto-submit form when toggling checkboxes (optional feature)
        document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                if (this.id === 'maintenance_mode') {
                    if (this.checked && !confirm('¿Estás seguro de activar el modo mantenimiento? Los usuarios no podrán acceder al sitio.')) {
                        this.checked = false;
                    }
                }
            });
        });
    </script>
</body>
</html>