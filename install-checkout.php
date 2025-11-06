<?php
/**
 * Script de Instalación del Sistema de Checkout
 * Este script verifica y crea las tablas necesarias en la base de datos
 */

// Cargar configuración
require_once __DIR__ . '/includes/config.php';

// Configuración de visualización de errores para instalación
ini_set('display_errors', 1);
error_reporting(E_ALL);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalación del Sistema de Checkout - Pinche Supplies</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .install-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 800px;
            width: 100%;
            padding: 40px;
        }
        .step {
            padding: 15px;
            margin: 10px 0;
            border-radius: 8px;
            border-left: 4px solid #dee2e6;
        }
        .step.success {
            background: #d1fae5;
            border-left-color: #10b981;
        }
        .step.error {
            background: #fee2e2;
            border-left-color: #ef4444;
        }
        .step.warning {
            background: #fef3c7;
            border-left-color: #f59e0b;
        }
        .step.info {
            background: #dbeafe;
            border-left-color: #3b82f6;
        }
        pre {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            font-size: 12px;
            max-height: 300px;
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <div class="install-card">
        <h1 class="mb-4"><i class="bi bi-gear"></i> Instalación del Sistema de Checkout</h1>
        <p class="text-muted mb-4">Este script verificará y configurará la base de datos para el sistema de pedidos.</p>
        
        <?php
        $steps = [];
        $hasErrors = false;
        
        // Paso 1: Verificar conexión a la base de datos
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
            $steps[] = [
                'type' => 'success',
                'title' => 'Conexión a la base de datos',
                'message' => 'Conexión exitosa a la base de datos: ' . DB_NAME
            ];
        } catch (PDOException $e) {
            $steps[] = [
                'type' => 'error',
                'title' => 'Error de conexión',
                'message' => 'No se pudo conectar a la base de datos: ' . $e->getMessage()
            ];
            $hasErrors = true;
        }
        
        if (!$hasErrors) {
            // Paso 2: Verificar si existe la tabla orders
            try {
                $stmt = $pdo->query("SHOW TABLES LIKE 'orders'");
                $tableExists = $stmt->rowCount() > 0;
                
                if ($tableExists) {
                    $steps[] = [
                        'type' => 'success',
                        'title' => 'Tabla orders',
                        'message' => 'La tabla orders ya existe en la base de datos'
                    ];
                } else {
                    $steps[] = [
                        'type' => 'warning',
                        'title' => 'Tabla orders',
                        'message' => 'La tabla orders no existe. Se intentará crear...'
                    ];
                    
                    // Leer el archivo SQL de la base de datos
                    $sqlFile = __DIR__ . '/database/database-completa.sql';
                    
                    if (file_exists($sqlFile)) {
                        $sql = file_get_contents($sqlFile);
                        
                        // Ejecutar el SQL
                        try {
                            $pdo->exec($sql);
                            $steps[] = [
                                'type' => 'success',
                                'title' => 'Creación de tablas',
                                'message' => 'Todas las tablas han sido creadas exitosamente'
                            ];
                        } catch (PDOException $e) {
                            $steps[] = [
                                'type' => 'error',
                                'title' => 'Error al crear tablas',
                                'message' => 'Error: ' . $e->getMessage()
                            ];
                            $hasErrors = true;
                        }
                    } else {
                        $steps[] = [
                            'type' => 'error',
                            'title' => 'Archivo SQL no encontrado',
                            'message' => 'No se encontró el archivo database/database-completa.sql'
                        ];
                        $hasErrors = true;
                    }
                }
            } catch (PDOException $e) {
                $steps[] = [
                    'type' => 'error',
                    'title' => 'Error al verificar tablas',
                    'message' => $e->getMessage()
                ];
                $hasErrors = true;
            }
            
            // Paso 3: Verificar tabla order_items
            if (!$hasErrors) {
                try {
                    $stmt = $pdo->query("SHOW TABLES LIKE 'order_items'");
                    $tableExists = $stmt->rowCount() > 0;
                    
                    if ($tableExists) {
                        $steps[] = [
                            'type' => 'success',
                            'title' => 'Tabla order_items',
                            'message' => 'La tabla order_items existe correctamente'
                        ];
                    } else {
                        $steps[] = [
                            'type' => 'error',
                            'title' => 'Tabla order_items',
                            'message' => 'La tabla order_items no existe'
                        ];
                        $hasErrors = true;
                    }
                } catch (PDOException $e) {
                    $steps[] = [
                        'type' => 'error',
                        'title' => 'Error al verificar order_items',
                        'message' => $e->getMessage()
                    ];
                    $hasErrors = true;
                }
            }
            
            // Paso 4: Verificar estructura de la tabla orders
            if (!$hasErrors) {
                try {
                    $stmt = $pdo->query("DESCRIBE orders");
                    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    
                    $requiredColumns = [
                        'id', 'order_number', 'customer_email', 'customer_name',
                        'total_amount', 'order_status', 'created_at'
                    ];
                    
                    $missingColumns = array_diff($requiredColumns, $columns);
                    
                    if (empty($missingColumns)) {
                        $steps[] = [
                            'type' => 'success',
                            'title' => 'Estructura de la tabla orders',
                            'message' => 'La tabla orders tiene todas las columnas necesarias'
                        ];
                    } else {
                        $steps[] = [
                            'type' => 'error',
                            'title' => 'Estructura de la tabla orders',
                            'message' => 'Faltan columnas: ' . implode(', ', $missingColumns)
                        ];
                        $hasErrors = true;
                    }
                } catch (PDOException $e) {
                    $steps[] = [
                        'type' => 'error',
                        'title' => 'Error al verificar estructura',
                        'message' => $e->getMessage()
                    ];
                    $hasErrors = true;
                }
            }
            
            // Paso 5: Verificar archivos necesarios
            $requiredFiles = [
                'save-order-db.php' => 'Archivo para guardar pedidos en la base de datos',
                'checkout.php' => 'Página de checkout',
                'order-confirmation.php' => 'Página de confirmación de pedido',
                'includes/class.database.php' => 'Clase de base de datos',
                'includes/class.order.php' => 'Clase de pedidos'
            ];
            
            $missingFiles = [];
            foreach ($requiredFiles as $file => $description) {
                if (!file_exists(__DIR__ . '/' . $file)) {
                    $missingFiles[] = "$file ($description)";
                }
            }
            
            if (empty($missingFiles)) {
                $steps[] = [
                    'type' => 'success',
                    'title' => 'Archivos del sistema',
                    'message' => 'Todos los archivos necesarios están presentes'
                ];
            } else {
                $steps[] = [
                    'type' => 'warning',
                    'title' => 'Archivos faltantes',
                    'message' => 'Faltan los siguientes archivos:<br>- ' . implode('<br>- ', $missingFiles)
                ];
            }
            
            // Paso 6: Verificar permisos de escritura
            $writableDirs = ['logs', 'uploads'];
            $permissionIssues = [];
            
            foreach ($writableDirs as $dir) {
                $dirPath = __DIR__ . '/' . $dir;
                if (!is_dir($dirPath)) {
                    @mkdir($dirPath, 0755, true);
                }
                if (!is_writable($dirPath)) {
                    $permissionIssues[] = $dir;
                }
            }
            
            if (empty($permissionIssues)) {
                $steps[] = [
                    'type' => 'success',
                    'title' => 'Permisos de escritura',
                    'message' => 'Todos los directorios tienen permisos correctos'
                ];
            } else {
                $steps[] = [
                    'type' => 'warning',
                    'title' => 'Permisos de escritura',
                    'message' => 'Los siguientes directorios no tienen permisos de escritura: ' . implode(', ', $permissionIssues)
                ];
            }
        }
        
        // Mostrar resultados
        foreach ($steps as $step) {
            echo '<div class="step ' . $step['type'] . '">';
            echo '<h5>' . $step['title'] . '</h5>';
            echo '<p class="mb-0">' . $step['message'] . '</p>';
            echo '</div>';
        }
        
        // Resumen final
        if ($hasErrors) {
            echo '<div class="alert alert-danger mt-4">';
            echo '<h5>❌ Instalación incompleta</h5>';
            echo '<p>Se encontraron errores durante la instalación. Por favor, corrige los problemas indicados arriba y vuelve a ejecutar este script.</p>';
            echo '</div>';
        } else {
            echo '<div class="alert alert-success mt-4">';
            echo '<h5>✅ Instalación completada</h5>';
            echo '<p>El sistema de checkout está listo para usar. Puedes proceder a probar el flujo de compra.</p>';
            echo '<a href="index.php" class="btn btn-primary mt-2">Ir a la tienda</a>';
            echo '<a href="admin/orders.php" class="btn btn-secondary mt-2 ms-2">Ver panel de pedidos</a>';
            echo '</div>';
        }
        ?>
        
        <div class="mt-4">
            <h5>Información del sistema</h5>
            <pre><?php
                echo "PHP Version: " . phpversion() . "\n";
                echo "Database: " . DB_NAME . "\n";
                echo "Host: " . DB_HOST . "\n";
                echo "Site URL: " . SITE_URL . "\n";
            ?></pre>
        </div>
    </div>
</body>
</html>
