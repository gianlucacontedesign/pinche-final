<?php
// =================================
// SCRIPT DE LIMPIEZA AUTOMÁTICA
// Archivo: limpiar-tokens.php
// Este script debe ejecutarse diariamente (cronjob)
// =================================

require_once 'includes/config.php';
require_once 'includes/email-sender.php';

echo "=== LIMPIEZA AUTOMÁTICA DE TOKENS EXPIRADOS ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";

try {
    // Contar tokens expirados antes de limpiar
    $countStmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM users 
        WHERE verification_expires < NOW() 
        AND verification_token IS NOT NULL
    ");
    $countStmt->execute();
    $expiredCount = $countStmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    echo "🔍 Tokens expirados encontrados: " . $expiredCount . "\n";
    
    if ($expiredCount > 0) {
        // Limpiar tokens expirados
        $cleanupStmt = $pdo->prepare("
            UPDATE users 
            SET verification_token = NULL, 
                verification_expires = NULL 
            WHERE verification_expires < NOW() 
            AND verification_token IS NOT NULL
        ");
        
        $result = $cleanupStmt->execute();
        
        if ($result) {
            echo "✅ Tokens expirados eliminados exitosamente\n";
        } else {
            echo "❌ Error al limpiar tokens expirados\n";
        }
    } else {
        echo "ℹ️ No hay tokens expirados para limpiar\n";
    }
    
    // Mostrar estadísticas actuales
    echo "\n=== ESTADÍSTICAS ACTUALES ===\n";
    
    $totalStmt = $pdo->query("SELECT COUNT(*) as total FROM users");
    $totalUsers = $totalStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $verifiedStmt = $pdo->query("SELECT COUNT(*) as verified FROM users WHERE email_verified = 1");
    $verifiedUsers = $verifiedStmt->fetch(PDO::FETCH_ASSOC)['verified'];
    
    $unverifiedStmt = $pdo->query("SELECT COUNT(*) as unverified FROM users WHERE email_verified = 0");
    $unverifiedUsers = $unverifiedStmt->fetch(PDO::FETCH_ASSOC)['unverified'];
    
    $activeTokensStmt = $pdo->query("SELECT COUNT(*) as active FROM users WHERE verification_token IS NOT NULL AND verification_expires > NOW()");
    $activeTokens = $activeTokensStmt->fetch(PDO::FETCH_ASSOC)['active'];
    
    echo "👥 Total usuarios: " . $totalUsers . "\n";
    echo "✅ Usuarios verificados: " . $verifiedUsers . "\n";
    echo "❌ Usuarios no verificados: " . $unverifiedUsers . "\n";
    echo "🔑 Tokens activos: " . $activeTokens . "\n";
    
    if ($totalUsers > 0) {
        $percentage = round(($verifiedUsers / $totalUsers) * 100, 1);
        echo "📊 Porcentaje de verificación: " . $percentage . "%\n";
    }
    
    // Log del proceso
    $logMessage = date('Y-m-d H:i:s') . " - Limpieza automática: " . $expiredCount . " tokens eliminados, " . $totalUsers . " usuarios totales, " . $percentage . "% verificados\n";
    file_put_contents('logs/limpieza-tokens.log', $logMessage, FILE_APPEND | LOCK_EX);
    
    echo "\n✅ Limpieza completada exitosamente\n";
    
} catch(PDOException $e) {
    echo "❌ Error en la base de datos: " . $e->getMessage() . "\n";
    error_log("Error limpieza tokens automática: " . $e->getMessage());
}

// =================================
// CONFIGURACIÓN DE CRONJOB
// =================================
/*
Para configurar limpieza automática diaria, agregar esta línea al crontab:

0 2 * * * /usr/bin/php /ruta/a/tu/sitio/limpiar-tokens.php >> logs/cron.log 2>&1

Esto ejecutará el script todos los días a las 2:00 AM.
*/
?>