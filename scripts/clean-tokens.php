<?php
/**
 * LIMPIEZA DE TOKENS EXPIRADOS
 * Script para limpiar tokens de verificación y reset de contraseña expirados
 * Versión: 1.0
 * Fecha: 31/10/2025
 */

// Configuración inicial
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Función para logging
function logCleanup($action, $status, $count, $details = '') {
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[{$timestamp}] [{$action}] [{$status}] {$count} registros procesados" . ($details ? " - {$details}" : '') . PHP_EOL;
    file_put_contents(__DIR__ . '/cleanup-log.txt', $logEntry, FILE_APPEND | LOCK_EX);
}

// Función para verificar conexión a BD
function getDatabaseConnection() {
    try {
        // Intentar cargar config.php
        if (file_exists('config.php')) {
            require_once 'config.php';
            
            if (defined('DB_HOST') && defined('DB_NAME') && defined('DB_USER') && defined('DB_PASS')) {
                $pdo = new PDO(
                    "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                    DB_USER,
                    DB_PASS,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                    ]
                );
                return ['success' => true, 'pdo' => $pdo, 'method' => 'config.php'];
            }
        }
        
        // Si no hay config.php, intentar configuración manual desde POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['manual_config'])) {
            $host = $_POST['db_host'] ?? 'localhost';
            $database = $_POST['db_name'] ?? '';
            $username = $_POST['db_user'] ?? '';
            $password = $_POST['db_pass'] ?? '';
            
            if (!empty($database) && !empty($username)) {
                $pdo = new PDO(
                    "mysql:host={$host};dbname={$database};charset=utf8mb4",
                    $username,
                    $password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                    ]
                );
                return ['success' => true, 'pdo' => $pdo, 'method' => 'manual'];
            }
        }
        
        return ['success' => false, 'error' => 'No se pudo establecer conexión a la base de datos'];
        
    } catch (Exception $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

// Función para obtener estadísticas de tokens
function getTokenStatistics($pdo) {
    $stats = [];
    
    try {
        // Total de tokens
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM email_tokens");
        $stats['total_tokens'] = $stmt->fetchColumn();
        
        // Tokens expirados
        $stmt = $pdo->query("SELECT COUNT(*) as expired FROM email_tokens WHERE expires_at < NOW()");
        $stats['expired_tokens'] = $stmt->fetchColumn();
        
        // Tokens activos
        $stmt = $pdo->query("SELECT COUNT(*) as active FROM email_tokens WHERE expires_at >= NOW()");
        $stats['active_tokens'] = $stmt->fetchColumn();
        
        // Tokens usados
        $stmt = $pdo->query("SELECT COUNT(*) as used FROM email_tokens WHERE used_at IS NOT NULL");
        $stats['used_tokens'] = $stmt->fetchColumn();
        
        // Tokens por tipo
        $stmt = $pdo->query("SELECT type, COUNT(*) as count FROM email_tokens GROUP BY type");
        $stats['by_type'] = $stmt->fetchAll();
        
        // Tokens más antiguos
        $stmt = $pdo->query("SELECT token, type, created_at, expires_at, used_at FROM email_tokens ORDER BY created_at ASC LIMIT 5");
        $stats['oldest_tokens'] = $stmt->fetchAll();
        
        logCleanup('STATS', 'SUCCESS', $stats['total_tokens'], 'Estadísticas obtenidas');
        
    } catch (Exception $e) {
        logCleanup('STATS', 'FAIL', 0, $e->getMessage());
        $stats['error'] = $e->getMessage();
    }
    
    return $stats;
}

// Función para limpiar tokens expirados
function cleanExpiredTokens($pdo) {
    $results = [];
    
    try {
        // Obtener tokens expirados antes de eliminar
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM email_tokens WHERE expires_at < NOW()");
        $expired_count = $stmt->fetchColumn();
        
        if ($expired_count > 0) {
            // Eliminar tokens expirados
            $stmt = $pdo->query("DELETE FROM email_tokens WHERE expires_at < NOW()");
            $deleted_count = $stmt->rowCount();
            
            $results['expired_tokens'] = [
                'success' => true,
                'deleted' => $deleted_count,
                'message' => "Se eliminaron {$deleted_count} tokens expirados"
            ];
            
            logCleanup('EXPIRED_TOKENS', 'SUCCESS', $deleted_count, 'Tokens expirados eliminados');
        } else {
            $results['expired_tokens'] = [
                'success' => true,
                'deleted' => 0,
                'message' => 'No hay tokens expirados para eliminar'
            ];
            logCleanup('EXPIRED_TOKENS', 'SUCCESS', 0, 'No había tokens expirados');
        }
        
    } catch (Exception $e) {
        $results['expired_tokens'] = [
            'success' => false,
            'error' => $e->getMessage(),
            'message' => 'Error eliminando tokens expirados: ' . $e->getMessage()
        ];
        logCleanup('EXPIRED_TOKENS', 'FAIL', 0, $e->getMessage());
    }
    
    return $results;
}

// Función para limpiar tokens usados
function cleanUsedTokens($pdo) {
    $results = [];
    
    try {
        // Obtener tokens usados antes de eliminar
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM email_tokens WHERE used_at IS NOT NULL");
        $used_count = $stmt->fetchColumn();
        
        if ($used_count > 0) {
            // Eliminar tokens usados (opcional, mantenemos por historial)
            // Por defecto no eliminamos, solo reportamos
            $results['used_tokens'] = [
                'success' => true,
                'count' => $used_count,
                'message' => "Se encontraron {$used_count} tokens usados (no eliminados por seguridad)"
            ];
            logCleanup('USED_TOKENS', 'SUCCESS', $used_count, 'Tokens usados encontrados (no eliminados)');
        } else {
            $results['used_tokens'] = [
                'success' => true,
                'count' => 0,
                'message' => 'No hay tokens usados para revisar'
            ];
            logCleanup('USED_TOKENS', 'SUCCESS', 0, 'No había tokens usados');
        }
        
    } catch (Exception $e) {
        $results['used_tokens'] = [
            'success' => false,
            'error' => $e->getMessage(),
            'message' => 'Error revisando tokens usados: ' . $e->getMessage()
        ];
        logCleanup('USED_TOKENS', 'FAIL', 0, $e->getMessage());
    }
    
    return $results;
}

// Función para limpiar tokens antiguos (opcional)
function cleanOldTokens($pdo, $days = 30) {
    $results = [];
    
    try {
        // Tokens más antiguos que X días y no usados
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count 
            FROM email_tokens 
            WHERE created_at < DATE_SUB(NOW(), INTERVAL :days DAY) 
            AND used_at IS NULL
        ");
        $stmt->execute(['days' => $days]);
        $old_count = $stmt->fetchColumn();
        
        if ($old_count > 0) {
            // Eliminar tokens antiguos no usados
            $stmt = $pdo->prepare("
                DELETE FROM email_tokens 
                WHERE created_at < DATE_SUB(NOW(), INTERVAL :days DAY) 
                AND used_at IS NULL
            ");
            $stmt->execute(['days' => $days]);
            $deleted_count = $stmt->rowCount();
            
            $results['old_tokens'] = [
                'success' => true,
                'deleted' => $deleted_count,
                'message' => "Se eliminaron {$deleted_count} tokens antiguos (>{$days} días)"
            ];
            
            logCleanup('OLD_TOKENS', 'SUCCESS', $deleted_count, "Tokens antiguos (>{$days} días) eliminados");
        } else {
            $results['old_tokens'] = [
                'success' => true,
                'deleted' => 0,
                'message' => "No hay tokens antiguos (>{$days} días) para eliminar"
            ];
            logCleanup('OLD_TOKENS', 'SUCCESS', 0, "No había tokens antiguos (>{$days} días)");
        }
        
    } catch (Exception $e) {
        $results['old_tokens'] = [
            'success' => false,
            'error' => $e->getMessage(),
            'message' => 'Error eliminando tokens antiguos: ' . $e->getMessage()
        ];
        logCleanup('OLD_TOKENS', 'FAIL', 0, $e->getMessage());
    }
    
    return $results;
}

// Función para optimizar tabla
function optimizeTable($pdo) {
    $results = [];
    
    try {
        $stmt = $pdo->query("OPTIMIZE TABLE email_tokens");
        
        $results['optimize'] = [
            'success' => true,
            'message' => 'Tabla email_tokens optimizada correctamente'
        ];
        
        logCleanup('OPTIMIZE', 'SUCCESS', 1, 'Tabla optimizada');
        
    } catch (Exception $e) {
        $results['optimize'] = [
            'success' => false,
            'error' => $e->getMessage(),
            'message' => 'Error optimizando tabla: ' . $e->getMessage()
        ];
        logCleanup('OPTIMIZE', 'FAIL', 0, $e->getMessage());
    }
    
    return $results;
}

// Procesar limpieza si se solicita
$cleanup_results = [];
$stats_before = null;
$stats_after = null;
$db_connection = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db_connection = getDatabaseConnection();
    
    if ($db_connection['success']) {
        $pdo = $db_connection['pdo'];
        
        // Obtener estadísticas antes de la limpieza
        $stats_before = getTokenStatistics($pdo);
        
        $cleanup_type = $_POST['cleanup_type'] ?? '';
        
        if ($cleanup_type === 'expired') {
            $cleanup_results = cleanExpiredTokens($pdo);
        } elseif ($cleanup_type === 'old') {
            $days = (int)($_POST['days'] ?? 30);
            $cleanup_results = cleanOldTokens($pdo, $days);
        } elseif ($cleanup_type === 'all') {
            $cleanup_results = array_merge(
                cleanExpiredTokens($pdo),
                cleanOldTokens($pdo, 30)
            );
        }
        
        // Optimizar tabla después de limpieza
        $optimize_result = optimizeTable($pdo);
        $cleanup_results = array_merge($cleanup_results, $optimize_result);
        
        // Obtener estadísticas después de la limpieza
        $stats_after = getTokenStatistics($pdo);
        
    } else {
        $cleanup_results['connection'] = [
            'success' => false,
            'error' => $db_connection['error'],
            'message' => 'Error de conexión: ' . $db_connection['error']
        ];
    }
}

// Obtener estadísticas actuales si hay conexión
$current_stats = null;
if ($db_connection && $db_connection['success']) {
    $current_stats = getTokenStatistics($db_connection['pdo']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Limpieza de Tokens - DonWeb</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
        }
        .cleanup-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .cleanup-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }
        .cleanup-body {
            padding: 40px;
        }
        .stats-card {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 25px;
            margin: 20px 0;
            border-left: 5px solid #667eea;
        }
        .stat-item {
            text-align: center;
            padding: 15px;
        }
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #667eea;
        }
        .stat-label {
            color: #666;
            font-size: 0.9rem;
        }
        .action-section {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin: 20px 0;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border-left: 4px solid #667eea;
        }
        .btn-cleanup {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            border-radius: 10px;
            padding: 12px 25px;
            font-weight: 600;
            color: white;
        }
        .btn-cleanup:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(40, 167, 69, 0.3);
            color: white;
        }
        .result-item {
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
        }
        .result-success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .result-error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .token-list {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin: 10px 0;
        }
        .config-form {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="cleanup-container">
        <div class="cleanup-header">
            <h1><i class="fas fa-broom"></i> Limpieza de Tokens</h1>
            <p>Limpieza de tokens de verificación y reset expirados</p>
            <small>Ejecutado: <?php echo date('d/m/Y H:i:s'); ?></small>
        </div>
        
        <div class="cleanup-body">
            <?php if (!$db_connection || !$db_connection['success']): ?>
                <!-- Configuración manual -->
                <div class="config-form">
                    <h4><i class="fas fa-database"></i> Configuración de Base de Datos</h4>
                    <p>Necesario para acceder a la tabla de tokens. Usar config.php existente o configuración manual:</p>
                    
                    <form method="POST">
                        <input type="hidden" name="manual_config" value="1">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Host</label>
                                <input type="text" class="form-control" name="db_host" value="localhost" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Base de Datos</label>
                                <input type="text" class="form-control" name="db_name" placeholder="a0030995_pinche" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Usuario</label>
                                <input type="text" class="form-control" name="db_user" placeholder="a0030995_pinche" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Contraseña</label>
                                <input type="password" class="form-control" name="db_pass" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-cleanup">
                            <i class="fas fa-plug"></i> Conectar a Base de Datos
                        </button>
                    </form>
                </div>
                
            <?php else: ?>
                <!-- Estadísticas actuales -->
                <?php if ($current_stats && !isset($current_stats['error'])): ?>
                    <div class="stats-card">
                        <h4><i class="fas fa-chart-pie"></i> Estadísticas de Tokens</h4>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="stat-item">
                                    <div class="stat-number"><?php echo number_format($current_stats['total_tokens']); ?></div>
                                    <div class="stat-label">Total de Tokens</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-item">
                                    <div class="stat-number text-warning"><?php echo number_format($current_stats['expired_tokens']); ?></div>
                                    <div class="stat-label">Expirados</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-item">
                                    <div class="stat-number text-success"><?php echo number_format($current_stats['active_tokens']); ?></div>
                                    <div class="stat-label">Activos</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-item">
                                    <div class="stat-number text-info"><?php echo number_format($current_stats['used_tokens']); ?></div>
                                    <div class="stat-label">Usados</div>
                                </div>
                            </div>
                        </div>
                        
                        <?php if (!empty($current_stats['by_type'])): ?>
                            <div class="mt-3">
                                <h6>Por tipo de token:</h6>
                                <?php foreach ($current_stats['by_type'] as $type): ?>
                                    <span class="badge bg-primary me-2">
                                        <?php echo ucfirst($type['type']); ?>: <?php echo $type['count']; ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Acciones de limpieza -->
                <div class="action-section">
                    <h4><i class="fas fa-tools"></i> Acciones de Limpieza</h4>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <form method="POST">
                                <input type="hidden" name="cleanup_type" value="expired">
                                <button type="submit" class="btn btn-cleanup w-100">
                                    <i class="fas fa-clock"></i> Limpiar Expirados
                                </button>
                                <small class="text-muted">Eliminar tokens cuya fecha de expiración ya pasó</small>
                            </form>
                        </div>
                        
                        <div class="col-md-4">
                            <form method="POST">
                                <input type="hidden" name="cleanup_type" value="old">
                                <input type="hidden" name="days" value="30">
                                <button type="submit" class="btn btn-cleanup w-100">
                                    <i class="fas fa-calendar"></i> Limpiar Antiguos
                                </button>
                                <small class="text-muted">Eliminar tokens no usados de más de 30 días</small>
                            </form>
                        </div>
                        
                        <div class="col-md-4">
                            <form method="POST">
                                <input type="hidden" name="cleanup_type" value="all">
                                <button type="submit" class="btn btn-cleanup w-100">
                                    <i class="fas fa-broom"></i> Limpieza Completa
                                </button>
                                <small class="text-muted">Eliminar expirados + antiguos + optimizar</small>
                            </form>
                        </div>
                    </div>
                </div>
                
                <?php if (!empty($cleanup_results)): ?>
                    <!-- Resultados de limpieza -->
                    <div class="action-section">
                        <h4><i class="fas fa-check-circle"></i> Resultados de Limpieza</h4>
                        
                        <?php foreach ($cleanup_results as $action => $result): ?>
                            <div class="result-item <?php echo $result['success'] ? 'result-success' : 'result-error'; ?>">
                                <h6><?php echo ucwords(str_replace('_', ' ', $action)); ?></h6>
                                <p><?php echo htmlspecialchars($result['message']); ?></p>
                                <?php if (!$result['success'] && isset($result['error'])): ?>
                                    <small class="text-danger">Error: <?php echo htmlspecialchars($result['error']); ?></small>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Comparación antes/después -->
                <?php if ($stats_before && $stats_after && !isset($stats_before['error'])): ?>
                    <div class="stats-card">
                        <h4><i class="fas fa-balance-scale"></i> Comparación Antes/Después</h4>
                        <div class="row">
                            <div class="col-md-4">
                                <h6>Antes de la Limpieza</h6>
                                <p><strong>Total:</strong> <?php echo number_format($stats_before['total_tokens']); ?></p>
                                <p><strong>Expirados:</strong> <?php echo number_format($stats_before['expired_tokens']); ?></p>
                                <p><strong>Activos:</strong> <?php echo number_format($stats_before['active_tokens']); ?></p>
                            </div>
                            <div class="col-md-4">
                                <h6>Después de la Limpieza</h6>
                                <p><strong>Total:</strong> <?php echo number_format($stats_after['total_tokens']); ?></p>
                                <p><strong>Expirados:</strong> <?php echo number_format($stats_after['expired_tokens']); ?></p>
                                <p><strong>Activos:</strong> <?php echo number_format($stats_after['active_tokens']); ?></p>
                            </div>
                            <div class="col-md-4">
                                <h6>Reducción</h6>
                                <p><strong>Total:</strong> -<?php echo number_format($stats_before['total_tokens'] - $stats_after['total_tokens']); ?></p>
                                <p><strong>Expirados:</strong> -<?php echo number_format($stats_before['expired_tokens'] - $stats_after['expired_tokens']); ?></p>
                                <?php
                                $reduction_percent = $stats_before['total_tokens'] > 0 
                                    ? round((($stats_before['total_tokens'] - $stats_after['total_tokens']) / $stats_before['total_tokens']) * 100, 1)
                                    : 0;
                                ?>
                                <p><strong>Reducción:</strong> <?php echo $reduction_percent; ?>%</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Tokens más antiguos -->
                <?php if ($current_stats && isset($current_stats['oldest_tokens']) && !empty($current_stats['oldest_tokens'])): ?>
                    <div class="action-section">
                        <h4><i class="fas fa-history"></i> Tokens Más Antiguos</h4>
                        <div class="token-list">
                            <?php foreach ($current_stats['oldest_tokens'] as $token): ?>
                                <div class="border-bottom pb-2 mb-2">
                                    <strong><?php echo ucfirst($token['type']); ?></strong> - 
                                    <?php echo htmlspecialchars(substr($token['token'], 0, 20)) . '...'; ?>
                                    <br>
                                    <small class="text-muted">
                                        Creado: <?php echo $token['created_at']; ?> | 
                                        Expira: <?php echo $token['expires_at']; ?>
                                        <?php if ($token['used_at']): ?>
                                            | Usado: <?php echo $token['used_at']; ?>
                                        <?php else: ?>
                                            | No usado
                                        <?php endif; ?>
                                    </small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="text-center mt-4">
                    <a href="diagnostico-sistema.php" class="btn btn-cleanup me-2">
                        <i class="fas fa-stethoscope"></i> Diagnóstico Completo
                    </a>
                    <a href="?" class="btn btn-cleanup">
                        <i class="fas fa-refresh"></i> Actualizar Estadísticas
                    </a>
                </div>
                
            <?php endif; ?>
            
            <!-- Información -->
            <div class="action-section">
                <h4><i class="fas fa-info-circle"></i> Información sobre la Limpieza</h4>
                <div class="row">
                    <div class="col-md-6">
                        <h5>Tipos de Tokens:</h5>
                        <ul>
                            <li><strong>verification:</strong> Tokens para verificar email</li>
                            <li><strong>password_reset:</strong> Tokens para reset de contraseña</li>
                        </ul>
                        
                        <h5>Tipos de Limpieza:</h5>
                        <ul>
                            <li><strong>Expirados:</strong> Tokens cuya fecha de expiración ya pasó</li>
                            <li><strong>Antiguos:</strong> Tokens no usados de más de 30 días</li>
                            <li><strong>Completa:</strong> Combinación de ambas + optimización</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h5>Beneficios:</h5>
                        <ul>
                            <li>🗑️ Reduce el tamaño de la base de datos</li>
                            <li>⚡ Mejora el rendimiento de consultas</li>
                            <li>🔒 Mantiene la seguridad eliminando tokens viejos</li>
                            <li>📊 Optimiza el uso de espacio en disco</li>
                        </ul>
                        
                        <h5>Frecuencia Recomendada:</h5>
                        <ul>
                            <li>Ejecutar semanalmente en sitios activos</li>
                            <li>Ejecutar mensualmente en sitios con poco tráfico</li>
                            <li>Después de campañas grandes de registro</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>