<?php
/**
 * Script de Diagnóstico Básico
 * Para identificar problemas de configuración
 */

// Activar reporte de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico del Servidor</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 900px;
            margin: 30px auto;
            padding: 20px;
            background: #f0f0f0;
        }
        .container {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #667eea;
            padding-bottom: 10px;
        }
        h2 {
            color: #555;
            margin-top: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #667eea;
            color: white;
        }
        tr:hover {
            background: #f5f5f5;
        }
        .ok {
            color: #10b981;
            font-weight: bold;
        }
        .error {
            color: #ef4444;
            font-weight: bold;
        }
        .warning {
            color: #f59e0b;
            font-weight: bold;
        }
        pre {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Diagnóstico del Servidor</h1>
        
        <h2>Información de PHP</h2>
        <table>
            <tr>
                <th>Parámetro</th>
                <th>Valor</th>
            </tr>
            <tr>
                <td>Versión de PHP</td>
                <td class="<?php echo version_compare(PHP_VERSION, '7.4.0', '>=') ? 'ok' : 'error'; ?>">
                    <?php echo PHP_VERSION; ?>
                </td>
            </tr>
            <tr>
                <td>Sistema Operativo</td>
                <td><?php echo PHP_OS; ?></td>
            </tr>
            <tr>
                <td>Servidor Web</td>
                <td><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'No disponible'; ?></td>
            </tr>
            <tr>
                <td>Document Root</td>
                <td><?php echo $_SERVER['DOCUMENT_ROOT'] ?? 'No disponible'; ?></td>
            </tr>
            <tr>
                <td>Script Path</td>
                <td><?php echo __DIR__; ?></td>
            </tr>
        </table>
        
        <h2>Extensiones PHP Requeridas</h2>
        <table>
            <tr>
                <th>Extensión</th>
                <th>Estado</th>
            </tr>
            <?php
            $extensions = [
                'pdo' => 'PDO (Database)',
                'pdo_mysql' => 'PDO MySQL',
                'json' => 'JSON',
                'curl' => 'cURL',
                'mbstring' => 'Multibyte String',
                'session' => 'Session'
            ];
            
            foreach ($extensions as $ext => $name) {
                $loaded = extension_loaded($ext);
                echo '<tr>';
                echo '<td>' . $name . '</td>';
                echo '<td class="' . ($loaded ? 'ok' : 'error') . '">';
                echo $loaded ? '✓ Instalada' : '✗ No instalada';
                echo '</td>';
                echo '</tr>';
            }
            ?>
        </table>
        
        <h2>Configuración PHP Importante</h2>
        <table>
            <tr>
                <th>Directiva</th>
                <th>Valor</th>
            </tr>
            <tr>
                <td>display_errors</td>
                <td><?php echo ini_get('display_errors') ? 'On' : 'Off'; ?></td>
            </tr>
            <tr>
                <td>error_reporting</td>
                <td><?php echo error_reporting(); ?></td>
            </tr>
            <tr>
                <td>max_execution_time</td>
                <td><?php echo ini_get('max_execution_time'); ?> segundos</td>
            </tr>
            <tr>
                <td>memory_limit</td>
                <td><?php echo ini_get('memory_limit'); ?></td>
            </tr>
            <tr>
                <td>upload_max_filesize</td>
                <td><?php echo ini_get('upload_max_filesize'); ?></td>
            </tr>
            <tr>
                <td>post_max_size</td>
                <td><?php echo ini_get('post_max_size'); ?></td>
            </tr>
        </table>
        
        <h2>Prueba de Conexión a Base de Datos</h2>
        <?php
        // Configuración - CREDENCIALES CORRECTAS DE TU CONFIG.PHP
        $db_host = 'localhost';
        $db_name = 'a0030995_pinche';
        $db_user = 'a0030995_pinche';  // ✅ CORREGIDO
        $db_pass = 'vawuDU97zu';
        
        try {
            $pdo = new PDO(
                "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4",
                $db_user,
                $db_pass,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            
            echo '<p class="ok">✓ Conexión exitosa a la base de datos: ' . $db_name . '</p>';
            
            // Verificar tablas
            $tables = ['orders', 'order_items', 'products', 'categories'];
            echo '<h3>Tablas en la Base de Datos:</h3>';
            echo '<table><tr><th>Tabla</th><th>Estado</th></tr>';
            
            foreach ($tables as $table) {
                $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
                $exists = $stmt->rowCount() > 0;
                echo '<tr>';
                echo '<td>' . $table . '</td>';
                echo '<td class="' . ($exists ? 'ok' : 'error') . '">';
                echo $exists ? '✓ Existe' : '✗ No existe';
                echo '</td>';
                echo '</tr>';
            }
            echo '</table>';
            
        } catch (PDOException $e) {
            echo '<p class="error">✗ Error de conexión: ' . htmlspecialchars($e->getMessage()) . '</p>';
        }
        ?>
        
        <h2>Archivos del Sistema</h2>
        <?php
        $files = [
            'save-order-db-simple.php',
            'checkout.php',
            'install-checkout-simple.php',
            'test-checkout.php',
            'config.php',
            'includes/config.php',
            'includes/class.database.php',
            'admin/config-admin.php'
        ];
        
        echo '<table><tr><th>Archivo</th><th>Estado</th></tr>';
        foreach ($files as $file) {
            $exists = file_exists(__DIR__ . '/' . $file);
            echo '<tr>';
            echo '<td>' . $file . '</td>';
            echo '<td class="' . ($exists ? 'ok' : 'error') . '">';
            echo $exists ? '✓ Existe' : '✗ No existe';
            echo '</td>';
            echo '</tr>';
        }
        echo '</table>';
        ?>
        
        <h2>Permisos de Directorios</h2>
        <?php
        $dirs = ['logs', 'uploads'];
        echo '<table><tr><th>Directorio</th><th>Existe</th><th>Escribible</th></tr>';
        
        foreach ($dirs as $dir) {
            $path = __DIR__ . '/' . $dir;
            $exists = is_dir($path);
            $writable = $exists && is_writable($path);
            
            echo '<tr>';
            echo '<td>' . $dir . '</td>';
            echo '<td class="' . ($exists ? 'ok' : 'warning') . '">';
            echo $exists ? '✓ Sí' : '✗ No';
            echo '</td>';
            echo '<td class="' . ($writable ? 'ok' : 'warning') . '">';
            echo $writable ? '✓ Sí' : '✗ No';
            echo '</td>';
            echo '</tr>';
        }
        echo '</table>';
        ?>
        
        <h2>Variables de Sesión</h2>
        <?php
        session_start();
        echo '<pre>';
        echo 'Session ID: ' . session_id() . "\n";
        echo 'Session Status: ' . (session_status() === PHP_SESSION_ACTIVE ? 'Activa' : 'Inactiva') . "\n";
        echo 'Carrito: ' . (isset($_SESSION['cart']) ? count($_SESSION['cart']) . ' items' : 'Vacío') . "\n";
        echo '</pre>';
        ?>
        
        <hr>
        <p><strong>Próximos pasos:</strong></p>
        <ul>
            <li><a href="install-checkout-simple.php">→ Ejecutar Instalación Simplificada</a></li>
            <li><a href="test-checkout.php">→ Probar Sistema de Checkout</a></li>
            <li><a href="admin/orders.php">→ Ver Panel de Administración</a></li>
        </ul>
    </div>
</body>
</html>
