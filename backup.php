<?php
/**
 * Página de Backup de Base de Datos
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
$backups = [];

// Procesar backup
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_backup'])) {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            // Obtener información de la base de datos
            $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
            
            if (empty($tables)) {
                throw new Exception('No se encontraron tablas en la base de datos');
            }
            
            $backupContent = "-- Backup de Base de Datos\n";
            $backupContent .= "-- Generado el: " . date('Y-m-d H:i:s') . "\n";
            $backupContent .= "-- Base de datos: " . DB_NAME . "\n\n";
            
            foreach ($tables as $table) {
                $backupContent .= "-- Estructura de tabla: $table\n";
                $backupContent .= "DROP TABLE IF EXISTS `$table`;\n";
                
                // Obtener estructura de la tabla
                $createTable = $db->query("SHOW CREATE TABLE `$table`")->fetch();
                $backupContent .= $createTable['Create Table'] . ";\n\n";
                
                $backupContent .= "-- Datos de la tabla: $table\n";
                
                // Obtener datos de la tabla
                $rows = $db->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
                
                if ($rows) {
                    foreach ($rows as $row) {
                        $backupContent .= "INSERT INTO `$table` (";
                        $backupContent .= implode(', ', array_map(function($col) {
                            return "`$col`";
                        }, array_keys($row)));
                        $backupContent .= ") VALUES (";
                        $backupContent .= implode(', ', array_map(function($value) use ($db) {
                            return $value === null ? 'NULL' : $db->quote($value);
                        }, array_values($row)));
                        $backupContent .= ");\n";
                    }
                    $backupContent .= "\n";
                } else {
                    $backupContent .= "-- Tabla vacía\n\n";
                }
            }
            
            $backupContent .= "-- Fin del backup\n";
            
            // Crear directorio de backups si no existe
            $backupDir = dirname(__DIR__) . '/backups';
            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0755, true);
            }
            
            // Crear directorio de fecha si no existe
            $dateDir = $backupDir . '/' . date('Y-m-d');
            if (!is_dir($dateDir)) {
                mkdir($dateDir, 0755, true);
            }
            
            // Generar nombre del archivo
            $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
            $filepath = $dateDir . '/' . $filename;
            
            // Escribir archivo
            if (file_put_contents($filepath, $backupContent)) {
                $message = "Backup creado exitosamente: $filename";
                
                // Registrar en log
                $logMessage = "[" . date('Y-m-d H:i:s') . "] Backup creado: $filename\n";
                file_put_contents($backupDir . '/backup.log', $logMessage, FILE_APPEND | LOCK_EX);
                
                // Permitir descarga del archivo
                if (isset($_POST['download'])) {
                    header('Content-Type: application/sql');
                    header('Content-Disposition: attachment; filename="' . $filename . '"');
                    header('Content-Length: ' . filesize($filepath));
                    readfile($filepath);
                    exit;
                }
            } else {
                throw new Exception('Error al escribir el archivo de backup');
            }
            
        } catch (Exception $e) {
            $error = 'Error al crear backup: ' . $e->getMessage();
        }
    }
    
    // Eliminar backup
    if (isset($_POST['delete_backup'])) {
        $backupFile = $_POST['backup_file'] ?? '';
        $backupPath = dirname(__DIR__) . '/backups/' . $backupFile;
        
        if (file_exists($backupPath) && unlink($backupPath)) {
            $message = 'Backup eliminado exitosamente';
            
            // Registrar en log
            $logMessage = "[" . date('Y-m-d H:i:s') . "] Backup eliminado: $backupFile\n";
            file_put_contents(dirname(__DIR__) . '/backups/backup.log', $logMessage, FILE_APPEND | LOCK_EX);
        } else {
            $error = 'Error al eliminar el backup';
        }
    }
}

// Obtener lista de backups
$backupDir = dirname(__DIR__) . '/backups';
if (is_dir($backupDir)) {
    $directories = array_filter(glob($backupDir . '/*'), 'is_dir');
    foreach ($directories as $dir) {
        $date = basename($dir);
        $files = glob($dir . '/*.sql');
        foreach ($files as $file) {
            $backups[] = [
                'date' => $date,
                'filename' => basename($file),
                'filepath' => $file,
                'size' => filesize($file),
                'modified' => filemtime($file)
            ];
        }
    }
    
    // Ordenar por fecha de modificación (más recientes primero)
    usort($backups, function($a, $b) {
        return $b['modified'] - $a['modified'];
    });
}

$pageTitle = 'Backup de Base de Datos - Panel de Administración';
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
        .backup-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: var(--spacing-6);
        }
        
        .backup-header {
            background: var(--color-white);
            border-radius: var(--radius-lg);
            padding: var(--spacing-8);
            margin-bottom: var(--spacing-6);
            box-shadow: var(--shadow-base);
        }
        
        .backup-title {
            font-size: var(--font-size-3xl);
            font-weight: 800;
            color: var(--color-primary);
            margin-bottom: var(--spacing-2);
        }
        
        .backup-description {
            color: var(--color-gray-500);
            font-size: var(--font-size-lg);
        }
        
        .backup-actions {
            display: flex;
            gap: var(--spacing-4);
            margin-top: var(--spacing-6);
            flex-wrap: wrap;
        }
        
        .backup-form {
            background: var(--color-white);
            border-radius: var(--radius-lg);
            padding: var(--spacing-8);
            margin-bottom: var(--spacing-6);
            box-shadow: var(--shadow-base);
        }
        
        .backup-backups-list {
            background: var(--color-white);
            border-radius: var(--radius-lg);
            padding: var(--spacing-8);
            box-shadow: var(--shadow-base);
        }
        
        .backup-list {
            width: 100%;
            border-collapse: collapse;
            margin-top: var(--spacing-4);
        }
        
        .backup-list th,
        .backup-list td {
            padding: var(--spacing-3);
            text-align: left;
            border-bottom: 1px solid var(--color-gray-300);
        }
        
        .backup-list th {
            background: var(--color-gray-100);
            font-weight: 600;
            color: var(--color-gray-700);
        }
        
        .backup-actions-cell {
            display: flex;
            gap: var(--spacing-2);
        }
        
        .backup-size {
            font-family: 'Monaco', 'Courier New', monospace;
        }
        
        .backup-date {
            color: var(--color-gray-500);
            font-size: var(--font-size-sm);
        }
    </style>
</head>
<body>
    <div class="backup-container">
        <div class="backup-header">
            <h1 class="backup-title">
                <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right: var(--spacing-3); vertical-align: middle;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
                Backup de Base de Datos
            </h1>
            <p class="backup-description">
                Crea respaldos completos de tu base de datos para proteger tu información.
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
        
        <div class="backup-form">
            <h3 style="margin-bottom: var(--spacing-4); color: var(--color-gray-900);">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right: var(--spacing-2); vertical-align: middle;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Crear Nuevo Backup
            </h3>
            
            <form method="POST" action="">
                <div class="backup-actions">
                    <button type="submit" name="create_backup" class="btn btn-primary">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        Crear Backup
                    </button>
                    
                    <button type="submit" name="create_backup" value="1" class="btn btn-outline" onclick="this.form.download.value='1'; return true;">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-4-4m4 4l4-4m4 4V4"></path>
                        </svg>
                        Crear y Descargar
                    </button>
                </div>
                
                <input type="hidden" name="download" value="0">
            </form>
            
            <div style="margin-top: var(--spacing-4); padding: var(--spacing-4); background: var(--color-gray-100); border-radius: var(--radius-base);">
                <h4 style="color: var(--color-gray-700); margin-bottom: var(--spacing-2);">Información:</h4>
                <ul style="color: var(--color-gray-600); font-size: var(--font-size-sm); margin-left: var(--spacing-6);">
                    <li>El backup incluye toda la estructura y datos de las tablas</li>
                    <li>Los archivos se guardan en la carpeta /backups/YYYY-MM-DD/</li>
                    <li>Se mantiene un registro de todas las operaciones en backup.log</li>
                </ul>
            </div>
        </div>
        
        <div class="backup-backups-list">
            <h3 style="margin-bottom: var(--spacing-4); color: var(--color-gray-900);">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right: var(--spacing-2); vertical-align: middle;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                Backups Existentes (<?php echo count($backups); ?>)
            </h3>
            
            <?php if (empty($backups)): ?>
                <div style="text-align: center; padding: var(--spacing-8); color: var(--color-gray-500);">
                    <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin: 0 auto var(--spacing-4);">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <p>No hay backups disponibles.</p>
                    <p>Crea tu primer backup usando el formulario anterior.</p>
                </div>
            <?php else: ?>
                <table class="backup-list">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Archivo</th>
                            <th>Tamaño</th>
                            <th>Modificado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($backups as $backup): ?>
                            <tr>
                                <td>
                                    <strong><?php echo e($backup['date']); ?></strong>
                                </td>
                                <td>
                                    <code><?php echo e($backup['filename']); ?></code>
                                </td>
                                <td>
                                    <span class="backup-size">
                                        <?php echo formatBytes($backup['size']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="backup-date">
                                        <?php echo date('d/m/Y H:i:s', $backup['modified']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="backup-actions-cell">
                                        <a href="<?php echo str_replace(dirname(__DIR__), '', $backup['filepath']); ?>" 
                                           class="btn btn-sm btn-outline" 
                                           download>
                                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-4-4m4 4l4-4m4 4V4"></path>
                                            </svg>
                                            Descargar
                                        </a>
                                        
                                        <form method="POST" action="" style="display: inline;" 
                                              onsubmit="return confirm('¿Estás seguro de eliminar este backup?');">
                                            <input type="hidden" name="backup_file" 
                                                   value="<?php echo $backup['date'] . '/' . $backup['filename']; ?>">
                                            <button type="submit" name="delete_backup" 
                                                    class="btn btn-sm btn-outline" 
                                                    style="color: var(--color-error); border-color: var(--color-error);">
                                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                                Eliminar
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        // Auto-submit form when clicking "Crear y Descargar"
        document.querySelector('form').addEventListener('submit', function(e) {
            if (this.querySelector('button[value="1"]')) {
                e.preventDefault();
                this.querySelector('input[name="download"]').value = '1';
                this.submit();
            }
        });
    </script>
</body>
</html>

<?php
// Función para formatear bytes
function formatBytes($size, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    
    for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
        $size /= 1024;
    }
    
    return round($size, $precision) . ' ' . $units[$i];
}
?>