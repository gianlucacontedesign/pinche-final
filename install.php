<?php
/**
 * Script de Instalación - Sistema Pinche Supplies
 * Versión: 1.0
 * Fecha: 31 de Octubre de 2025
 * Hosting: DonWeb cPanel
 * 
 * IMPORTANTE: Ejecutar solo una vez durante la instalación inicial
 */

// Configuración inicial
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/config.php';
require_once 'includes/functions.php';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalación - Sistema Pinche Supplies</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .install-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
            padding: 40px;
            max-width: 600px;
            width: 90%;
        }
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo h1 { color: #667eea; font-size: 28px; margin-bottom: 10px; }
        .logo p { color: #666; }
        .step {
            margin-bottom: 25px;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }
        .step h3 { color: #333; margin-bottom: 10px; }
        .step p { color: #666; line-height: 1.6; }
        .btn {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            display: inline-block;
            margin: 10px 5px;
        }
        .btn:hover { opacity: 0.9; }
        .success { color: #4CAF50; font-weight: bold; }
        .error { color: #f44336; font-weight: bold; }
        .warning { color: #ff9800; font-weight: bold; }
        pre { 
            background: #f5f5f5; 
            padding: 15px; 
            border-radius: 5px; 
            overflow-x: auto;
            font-size: 12px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="install-container">
        <div class="logo">
            <h1>🏪 Pinche Supplies</h1>
            <p>Sistema de Registro y Verificación por Email</p>
            <p><strong>Instalación v1.0</strong></p>
        </div>

        <?php
        // Verificar si ya está instalado
        if (file_exists('includes/config.php')) {
            include_once 'includes/config.php';
            
            // Verificar conexión a la base de datos
            $db_check = false;
            try {
                $pdo = getDBConnection();
                $db_check = true;
            } catch (Exception $e) {
                $db_error = $e->getMessage();
            }
        }
        ?>

        <div class="step">
            <h3>📋 Paso 1: Verificación del Sistema</h3>
            
            <h4>Requisitos del Sistema:</h4>
            <ul style="margin: 10px 0; padding-left: 20px;">
                <li>PHP 7.4+ ✅ <?php echo version_compare(PHP_VERSION, '7.4.0') >= 0 ? '<span class="success">Disponible</span>' : '<span class="error">No disponible</span>'; ?></li>
                <li>Extensión MySQLi ✅ <?php echo extension_loaded('mysqli') ? '<span class="success">Disponible</span>' : '<span class="error">No disponible</span>'; ?></li>
                <li>Extensión PDO ✅ <?php echo extension_loaded('pdo') ? '<span class="success">Disponible</span>' : '<span class="error">No disponible</span>'; ?></li>
                <li>Extensión OpenSSL ✅ <?php echo extension_loaded('openssl') ? '<span class="success">Disponible</span>' : '<span class="error">No disponible</span>'; ?></li>
                <li>Permisos de escritura ✅ <?php echo is_writable('.') ? '<span class="success">Disponible</span>' : '<span class="error">No disponible</span>'; ?></li>
            </ul>

            <h4>Configuración de Base de Datos:</h4>
            <?php if (isset($db_check)): ?>
                <?php if ($db_check): ?>
                    <p class="success">✅ Conexión a la base de datos exitosa</p>
                <?php else: ?>
                    <p class="error">❌ Error de conexión: <?php echo htmlspecialchars($db_error ?? 'Error desconocido'); ?></p>
                <?php endif; ?>
            <?php else: ?>
                <p class="warning">⚠️ Configuración no verificada</p>
            <?php endif; ?>
        </div>

        <div class="step">
            <h3>📁 Paso 2: Estructura de Archivos</h3>
            <p>Verificando estructura de directorios necesarios...</p>
            
            <?php
            $required_dirs = ['assets', 'includes', 'logs', 'uploads'];
            foreach ($required_dirs as $dir) {
                if (is_dir($dir)) {
                    echo "<p class='success'>✅ Directorio $dir existe</p>";
                } else {
                    echo "<p class='error'>❌ Directorio $dir no existe</p>";
                    // Crear directorio si no existe
                    if (mkdir($dir, 0755, true)) {
                        echo "<p class='success'>✅ Directorio $dir creado</p>";
                    }
                }
            }
            ?>
        </div>

        <div class="step">
            <h3>🗄️ Paso 3: Base de Datos</h3>
            <p>Ejecutar el script de base de datos para crear las tablas necesarias.</p>
            
            <?php if ($db_check): ?>
                <form method="post" style="margin-top: 15px;">
                    <input type="hidden" name="install_db" value="1">
                    <button type="submit" class="btn">Crear Tablas de Base de Datos</button>
                </form>
                
                <?php
                if (isset($_POST['install_db'])) {
                    try {
                        // Leer y ejecutar el script SQL
                        if (file_exists('database/database-update.sql')) {
                            $sql = file_get_contents('database/database-update.sql');
                            $pdo = getDBConnection();
                            
                            // Dividir en comandos individuales
                            $commands = explode(';', $sql);
                            $executed = 0;
                            
                            foreach ($commands as $command) {
                                $command = trim($command);
                                if (!empty($command)) {
                                    try {
                                        $pdo->exec($command);
                                        $executed++;
                                    } catch (PDOException $e) {
                                        // Ignorar errores de tabla existente
                                        if (strpos($e->getMessage(), 'already exists') === false) {
                                            throw $e;
                                        }
                                    }
                                }
                            }
                            
                            echo "<p class='success'>✅ Base de datos configurada exitosamente ($executed comandos ejecutados)</p>";
                        } else {
                            echo "<p class='error'>❌ Archivo database-update.sql no encontrado</p>";
                        }
                    } catch (Exception $e) {
                        echo "<p class='error'>❌ Error al configurar base de datos: " . htmlspecialchars($e->getMessage()) . "</p>";
                    }
                }
                ?>
            <?php else: ?>
                <p class="error">❌ No se puede continuar sin conexión a la base de datos</p>
            <?php endif; ?>
        </div>

        <div class="step">
            <h3>🔐 Paso 4: Configuración de Seguridad</h3>
            <p>Generando claves de seguridad únicas...</p>
            
            <?php
            $salt_file = 'includes/security.salt';
            if (!file_exists($salt_file)) {
                $new_salt = bin2hex(random_bytes(32));
                file_put_contents($salt_file, $new_salt);
                echo "<p class='success'>✅ Clave de seguridad generada</p>";
            } else {
                echo "<p class='success'>✅ Clave de seguridad existente</p>";
            }
            ?>
        </div>

        <div class="step">
            <h3>✅ Paso 5: Finalización</h3>
            <p>Una vez completados todos los pasos anteriores:</p>
            
            <ol style="margin: 10px 0; padding-left: 20px; line-height: 1.6;">
                <li>Editar <code>includes/config.php</code> con las credenciales reales</li>
                <li>Configurar SMTP en <code>includes/config.php</code></li>
                <li>Eliminar este archivo <code>install.php</code> por seguridad</li>
                <li>Probar el sistema en <a href="registro.php">registro.php</a></li>
            </ol>
            
            <div style="text-align: center; margin-top: 20px;">
                <a href="registro.php" class="btn">Ir al Registro</a>
                <a href="admin/" class="btn">Panel de Admin</a>
            </div>
        </div>

        <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
            <p style="color: #666; font-size: 14px;">
                🏪 <strong>Pinche Supplies</strong> v1.0<br>
                Sistema de Registro y Verificación por Email<br>
                © 2025 - DonWeb Hosting
            </p>
        </div>
    </div>
</body>
</html>