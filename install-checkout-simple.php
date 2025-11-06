<?php
/**
 * Script de Instalación Simplificado
 * Versión sin dependencias para diagnosticar problemas
 */

// Activar reporte de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuración de base de datos - EDITAR AQUÍ
$db_host = 'localhost';
$db_name = 'a0030995_pinche';
$db_user = 'a0030995_pinche'; // ✅ CORREGIDO
$db_pass = 'vawuDU97zu';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalación Simplificada - Pinche Supplies</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .card {
            background: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .step {
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            border-left: 4px solid #ccc;
        }
        .success {
            background: #d1fae5;
            border-left-color: #10b981;
        }
        .error {
            background: #fee2e2;
            border-left-color: #ef4444;
        }
        .warning {
            background: #fef3c7;
            border-left-color: #f59e0b;
        }
        .info {
            background: #dbeafe;
            border-left-color: #3b82f6;
        }
        h1 { color: #333; }
        h3 { margin: 10px 0 5px 0; }
        p { margin: 5px 0; }
        pre {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>🔧 Instalación Simplificada - Diagnóstico</h1>
        <p>Este script verifica la configuración básica del sistema.</p>
        <hr>
        
        <?php
        $steps = [];
        $hasErrors = false;
        
        // Paso 1: Verificar versión de PHP
        $phpVersion = phpversion();
        if (version_compare($phpVersion, '7.4.0', '>=')) {
            $steps[] = [
                'type' => 'success',
                'title' => 'Versión de PHP',
                'message' => "PHP $phpVersion - Compatible ✓"
            ];
        } else {
            $steps[] = [
                'type' => 'error',
                'title' => 'Versión de PHP',
                'message' => "PHP $phpVersion - Se requiere PHP 7.4 o superior"
            ];
            $hasErrors = true;
        }
        
        // Paso 2: Verificar extensiones PHP
        $requiredExtensions = ['pdo', 'pdo_mysql', 'json', 'curl'];
        $missingExtensions = [];
        
        foreach ($requiredExtensions as $ext) {
            if (!extension_loaded($ext)) {
                $missingExtensions[] = $ext;
            }
        }
        
        if (empty($missingExtensions)) {
            $steps[] = [
                'type' => 'success',
                'title' => 'Extensiones PHP',
                'message' => 'Todas las extensiones necesarias están instaladas ✓'
            ];
        } else {
            $steps[] = [
                'type' => 'error',
                'title' => 'Extensiones PHP',
                'message' => 'Faltan extensiones: ' . implode(', ', $missingExtensions)
            ];
            $hasErrors = true;
        }
        
        // Paso 3: Verificar conexión a base de datos
        try {
            $pdo = new PDO(
                "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4",
                $db_user,
                $db_pass,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
            
            $steps[] = [
                'type' => 'success',
                'title' => 'Conexión a Base de Datos',
                'message' => "Conectado exitosamente a: $db_name ✓"
            ];
            
            // Paso 4: Verificar tabla orders
            try {
                $stmt = $pdo->query("SHOW TABLES LIKE 'orders'");
                if ($stmt->rowCount() > 0) {
                    $steps[] = [
                        'type' => 'success',
                        'title' => 'Tabla orders',
                        'message' => 'La tabla orders existe ✓'
                    ];
                    
                    // Verificar estructura
                    $stmt = $pdo->query("DESCRIBE orders");
                    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    
                    $requiredColumns = ['id', 'order_number', 'customer_email', 'total_amount'];
                    $missingColumns = array_diff($requiredColumns, $columns);
                    
                    if (empty($missingColumns)) {
                        $steps[] = [
                            'type' => 'success',
                            'title' => 'Estructura de orders',
                            'message' => 'La tabla tiene todas las columnas necesarias ✓'
                        ];
                    } else {
                        $steps[] = [
                            'type' => 'error',
                            'title' => 'Estructura de orders',
                            'message' => 'Faltan columnas: ' . implode(', ', $missingColumns)
                        ];
                        $hasErrors = true;
                    }
                } else {
                    $steps[] = [
                        'type' => 'error',
                        'title' => 'Tabla orders',
                        'message' => 'La tabla orders NO existe. Debes importar database/database-completa.sql'
                    ];
                    $hasErrors = true;
                }
            } catch (PDOException $e) {
                $steps[] = [
                    'type' => 'error',
                    'title' => 'Verificación de tabla',
                    'message' => 'Error: ' . $e->getMessage()
                ];
                $hasErrors = true;
            }
            
            // Paso 5: Verificar tabla order_items
            try {
                $stmt = $pdo->query("SHOW TABLES LIKE 'order_items'");
                if ($stmt->rowCount() > 0) {
                    $steps[] = [
                        'type' => 'success',
                        'title' => 'Tabla order_items',
                        'message' => 'La tabla order_items existe ✓'
                    ];
                } else {
                    $steps[] = [
                        'type' => 'error',
                        'title' => 'Tabla order_items',
                        'message' => 'La tabla order_items NO existe'
                    ];
                    $hasErrors = true;
                }
            } catch (PDOException $e) {
                $steps[] = [
                    'type' => 'error',
                    'title' => 'Tabla order_items',
                    'message' => 'Error: ' . $e->getMessage()
                ];
                $hasErrors = true;
            }
            
        } catch (PDOException $e) {
            $steps[] = [
                'type' => 'error',
                'title' => 'Conexión a Base de Datos',
                'message' => 'Error de conexión: ' . $e->getMessage()
            ];
            $hasErrors = true;
        }
        
        // Paso 6: Verificar archivos
        $requiredFiles = [
            'save-order-db.php' => 'Archivo para guardar pedidos',
            'checkout.php' => 'Página de checkout',
            'includes/config.php' => 'Archivo de configuración',
            'includes/class.database.php' => 'Clase de base de datos'
        ];
        
        $missingFiles = [];
        foreach ($requiredFiles as $file => $desc) {
            if (!file_exists(__DIR__ . '/' . $file)) {
                $missingFiles[] = "$file ($desc)";
            }
        }
        
        if (empty($missingFiles)) {
            $steps[] = [
                'type' => 'success',
                'title' => 'Archivos del Sistema',
                'message' => 'Todos los archivos necesarios están presentes ✓'
            ];
        } else {
            $steps[] = [
                'type' => 'warning',
                'title' => 'Archivos Faltantes',
                'message' => 'Faltan: ' . implode(', ', $missingFiles)
            ];
        }
        
        // Paso 7: Verificar permisos
        $logsDir = __DIR__ . '/logs';
        if (!is_dir($logsDir)) {
            @mkdir($logsDir, 0755, true);
        }
        
        if (is_writable($logsDir)) {
            $steps[] = [
                'type' => 'success',
                'title' => 'Permisos de Escritura',
                'message' => 'El directorio logs/ tiene permisos correctos ✓'
            ];
        } else {
            $steps[] = [
                'type' => 'warning',
                'title' => 'Permisos de Escritura',
                'message' => 'El directorio logs/ no tiene permisos de escritura'
            ];
        }
        
        // Mostrar resultados
        foreach ($steps as $step) {
            echo '<div class="step ' . $step['type'] . '">';
            echo '<h3>' . $step['title'] . '</h3>';
            echo '<p>' . $step['message'] . '</p>';
            echo '</div>';
        }
        
        // Resumen final
        echo '<hr>';
        if ($hasErrors) {
            echo '<div class="step error">';
            echo '<h3>❌ Hay problemas que corregir</h3>';
            echo '<p>Revisa los errores indicados arriba y corrígelos antes de continuar.</p>';
            echo '</div>';
        } else {
            echo '<div class="step success">';
            echo '<h3>✅ Sistema listo</h3>';
            echo '<p>Todo está configurado correctamente. Puedes proceder a probar el checkout.</p>';
            echo '<p><a href="test-checkout.php" style="color: #10b981; font-weight: bold;">→ Ir a Prueba de Checkout</a></p>';
            echo '</div>';
        }
        
        // Información del sistema
        echo '<hr>';
        echo '<h3>Información del Sistema</h3>';
        echo '<pre>';
        echo "PHP Version: " . phpversion() . "\n";
        echo "Server: " . $_SERVER['SERVER_SOFTWARE'] . "\n";
        echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
        echo "Script Path: " . __DIR__ . "\n";
        echo '</pre>';
        ?>
    </div>
</body>
</html>
