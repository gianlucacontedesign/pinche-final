<?php
/**
 * Instalador Automático del Panel de Administración
 * Verifica la configuración y estructura necesaria
 */

// Configuración de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Instalador - Panel Admin</title>
    <link href='https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        body { background: #f8fafc; padding: 20px; }
        .installer-card { max-width: 800px; margin: 0 auto; }
        .status-success { color: #10b981; }
        .status-error { color: #ef4444; }
        .status-warning { color: #f59e0b; }
        .code-block { background: #f1f5f9; padding: 15px; border-radius: 8px; font-family: monospace; }
    </style>
</head>
<body>
<div class='container'>
    <div class='card installer-card'>
        <div class='card-header bg-primary text-white'>
            <h3 class='mb-0'><i class='fas fa-cog'></i> Instalador del Panel de Administración</h3>
        </div>
        <div class='card-body'>";

$errors = [];
$warnings = [];
$success = [];

// Verificar PHP
if (version_compare(PHP_VERSION, '7.4.0', '<')) {
    $errors[] = "PHP 7.4+ requerido. Versión actual: " . PHP_VERSION;
} else {
    $success[] = "PHP " . PHP_VERSION . " ✓";
}

// Verificar extensiones
$required_extensions = ['pdo', 'pdo_mysql', 'mysqli', 'mbstring', 'openssl'];
foreach ($required_extensions as $ext) {
    if (!extension_loaded($ext)) {
        $errors[] = "Extensión PHP requerida: $ext";
    } else {
        $success[] = "Extensión $ext ✓";
    }
}

// Verificar archivos
$required_files = ['admin-verificaciones.php', 'config-admin.php'];
foreach ($required_files as $file) {
    if (file_exists(__DIR__ . '/' . $file)) {
        $success[] = "Archivo $file encontrado ✓";
    } else {
        $errors[] = "Archivo $file no encontrado";
    }
}

// Verificar permisos de escritura
if (!is_writable(__DIR__)) {
    $errors[] = "El directorio admin no tiene permisos de escritura";
} else {
    $success[] = "Permisos de escritura OK ✓";
}

// Verificar config-admin.php si existe
if (file_exists(__DIR__ . '/config-admin.php')) {
    include_once __DIR__ . '/config-admin.php';
    
    // Verificar configuración de BD
    if (defined('DB_NAME') && DB_NAME !== 'tu_base_datos') {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS
            );
            $success[] = "Conexión a base de datos OK ✓";
            
            // Verificar tabla users
            $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
            if ($stmt->rowCount() > 0) {
                $success[] = "Tabla users encontrada ✓";
                
                // Verificar campos necesarios
                $stmt = $pdo->query("DESCRIBE users");
                $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
                
                $required_columns = ['email_verified', 'verification_token', 'verification_expires', 'created_at'];
                foreach ($required_columns as $col) {
                    if (in_array($col, $columns)) {
                        $success[] = "Campo $col existe ✓";
                    } else {
                        $warnings[] = "Campo $col no encontrado en tabla users";
                    }
                }
            } else {
                $errors[] = "Tabla 'users' no encontrada en la base de datos";
            }
            
        } catch(PDOException $e) {
            $errors[] = "Error de conexión a BD: " . $e->getMessage();
        }
    } else {
        $warnings[] = "Configuración de BD no completada en config-admin.php";
    }
    
    // Verificar credenciales admin
    if (defined('ADMIN_EMAIL') && ADMIN_EMAIL !== 'admin@pinchesupplies.com.ar') {
        $success[] = "Email de administrador configurado ✓";
    } else {
        $warnings[] = "Email de administrador por defecto (debe cambiarse)";
    }
    
    if (defined('ADMIN_PASSWORD') && ADMIN_PASSWORD !== 'admin123') {
        $success[] = "Contraseña de administrador configurada ✓";
    } else {
        $warnings[] = "Contraseña de administrador por defecto (debe cambiarse)";
    }
} else {
    $errors[] = "Archivo config-admin.php no encontrado";
}

// Mostrar resultados
if (!empty($success)) {
    echo "<div class='alert alert-success'>
            <h5><i class='fas fa-check-circle'></i> Verificaciones Exitosas</h5>";
    foreach ($success as $msg) {
        echo "<div class='status-success'>✓ $msg</div>";
    }
    echo "</div>";
}

if (!empty($warnings)) {
    echo "<div class='alert alert-warning'>
            <h5><i class='fas fa-exclamation-triangle'></i> Advertencias</h5>";
    foreach ($warnings as $msg) {
        echo "<div class='status-warning'>⚠ $msg</div>";
    }
    echo "</div>";
}

if (!empty($errors)) {
    echo "<div class='alert alert-danger'>
            <h5><i class='fas fa-times-circle'></i> Errores Críticos</h5>";
    foreach ($errors as $msg) {
        echo "<div class='status-error'>❌ $msg</div>";
    }
    echo "</div>";
}

// Si no hay errores críticos, mostrar siguiente paso
if (empty($errors)) {
    echo "<div class='alert alert-info'>
            <h5><i class='fas fa-info-circle'></i> Siguiente Paso</h5>
            <p>Si todas las verificaciones son exitosas, puedes:</p>
            <ul>
                <li><a href='admin-verificaciones.php' class='btn btn-primary'>Ir al Panel de Administración</a></li>
                <li><a href='config-admin.php' class='btn btn-outline-secondary'>Editar Configuración</a></li>
            </ul>
          </div>";
    
    // Mostrar configuración recomendada
    echo "<div class='card mt-4'>
            <div class='card-header'>
                <h5><i class='fas fa-wrench'></i> Configuración Recomendada</h5>
            </div>
            <div class='card-body'>
                <h6>Archivo config-admin.php:</h6>
                <div class='code-block'>";
    
    if (!defined('DB_NAME') || DB_NAME === 'tu_base_datos') {
        echo "// CONFIGURACIÓN DE BASE DE DATOS (OBLIGATORIO)
define('DB_HOST', 'localhost');
define('DB_NAME', 'tu_base_datos');     // ← Cambiar por tu BD
define('DB_USER', 'tu_usuario');        // ← Cambiar por tu usuario
define('DB_PASS', 'tu_password');       // ← Cambiar por tu password

// CONFIGURACIÓN DE ADMINISTRADOR (OBLIGATORIO)
define('ADMIN_EMAIL', 'admin@tudominio.com');    // ← Tu email
define('ADMIN_PASSWORD', 'password_seguro_123'); // ← Contraseña segura

// CONFIGURACIÓN DEL SITIO
define('SITE_URL', 'https://tudominio.com');     // ← Tu dominio
define('EMAIL_FROM_ADDRESS', 'no-reply@tudominio.com'); // ← Tu email";
    } else {
        echo "✓ Base de datos configurada
✓ Credenciales de administrador configuradas

Para mayor seguridad, considera usar hash de contraseña:
\$hash = password_hash('tu_password', PASSWORD_DEFAULT);";
    }
    
    echo "</div>
            </div>
          </div>";
    
} else {
    echo "<div class='alert alert-danger'>
            <h5><i class='fas fa-times'></i> Instalación Incompleta</h5>
            <p>Por favor corrige los errores antes de continuar:</p>
            <ol>";
    foreach ($errors as $error) {
        echo "<li>$error</li>";
    }
    echo "</ol>
            <p><strong>Una vez corregidos, actualiza esta página para verificar nuevamente.</strong></p>
          </div>";
}

echo "      </div>
        <div class='card-footer'>
            <small class='text-muted'>
                <i class='fas fa-info-circle'></i> 
                Instalador v1.0 - Panel de Administración Pinche Supplies
            </small>
        </div>
    </div>
</div>
</body>
</html>";
?>