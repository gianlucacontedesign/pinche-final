<?php
// =================================
// FUNCIONES ACTUALIZADAS PARA REGISTRO CON VERIFICACIÓN
// archivo: includes/funciones-registro-actualizado.php
// =================================

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/email-sender.php';

/**
 * Función para registrar un nuevo usuario con verificación por email
 */
function registrarUsuarioConVerificacion($name, $email, $password, $phone = '', $address = '') {
    global $pdo;
    
    try {
        // Validar datos
        if (empty($name) || empty($email) || empty($password)) {
            return ['success' => false, 'message' => '❌ Todos los campos obligatorios deben estar completos.'];
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => '❌ El formato del email no es válido.'];
        }
        
        if (strlen($password) < 6) {
            return ['success' => false, 'message' => '❌ La contraseña debe tener al menos 6 caracteres.'];
        }
        
        // Verificar si el email ya existe
        $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $checkStmt->execute([$email]);
        
        if ($checkStmt->fetch()) {
            return ['success' => false, 'message' => '❌ Este email ya está registrado.'];
        }
        
        // Generar hash de contraseña
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Generar token de verificación único
        $verificationToken = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours')); // Expira en 24 horas
        
        // Insertar nuevo usuario (inactivo hasta verificar email)
        $insertStmt = $pdo->prepare("
            INSERT INTO users (name, email, password, phone, address, email_verified, verification_token, verification_expires, active, created_at) 
            VALUES (?, ?, ?, ?, ?, 0, ?, ?, 0, NOW())
        ");
        
        $result = $insertStmt->execute([
            $name, 
            $email, 
            $hashedPassword, 
            $phone, 
            $address, 
            $verificationToken, 
            $expiresAt
        ]);
        
        if ($result) {
            $userId = $pdo->lastInsertId();
            
            // Enviar email de verificación
            $verificationUrl = "https://pinchesupplies.com.ar/verificar-email.php?token=" . $verificationToken . "&email=" . urlencode($email);
            
            if (sendVerificationEmail($email, $name, $verificationUrl)) {
                return [
                    'success' => true, 
                    'message' => '✅ Registro exitoso. Revisa tu email para verificar tu cuenta.',
                    'user_id' => $userId,
                    'email_sent' => true
                ];
            } else {
                // Si falla el envío del email, eliminar el usuario creado
                $deleteStmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $deleteStmt->execute([$userId]);
                
                return ['success' => false, 'message' => '❌ Error al enviar el email de verificación. Intenta nuevamente.'];
            }
        } else {
            return ['success' => false, 'message' => '❌ Error al crear la cuenta. Intenta nuevamente.'];
        }
        
    } catch(PDOException $e) {
        error_log("Error registro usuario: " . $e->getMessage());
        return ['success' => false, 'message' => '❌ Error en la base de datos. Intenta nuevamente.'];
    }
}

/**
 * Función para verificar las credenciales de login con verificación de email
 */
function verificarLogin($email, $password) {
    global $pdo;
    
    try {
        // Buscar usuario por email
        $stmt = $pdo->prepare("
            SELECT id, name, email, password, email_verified, active 
            FROM users 
            WHERE email = ?
        ");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            // Verificar contraseña
            if (password_verify($password, $user['password'])) {
                
                // Verificar si el email está verificado
                if (!$user['email_verified']) {
                    return [
                        'success' => false, 
                        'message' => '❌ Debes verificar tu email antes de iniciar sesión.',
                        'email_not_verified' => true,
                        'user_email' => $user['email']
                    ];
                }
                
                // Verificar si la cuenta está activa
                if (!$user['active']) {
                    return [
                        'success' => false, 
                        'message' => '❌ Tu cuenta está desactivada. Contacta al administrador.'
                    ];
                }
                
                // Login exitoso
                return [
                    'success' => true, 
                    'message' => '✅ Login exitoso.',
                    'user' => [
                        'id' => $user['id'],
                        'name' => $user['name'],
                        'email' => $user['email']
                    ]
                ];
                
            } else {
                return ['success' => false, 'message' => '❌ Email o contraseña incorrectos.'];
            }
        } else {
            return ['success' => false, 'message' => '❌ Email o contraseña incorrectos.'];
        }
        
    } catch(PDOException $e) {
        error_log("Error verificación login: " . $e->getMessage());
        return ['success' => false, 'message' => '❌ Error en la base de datos. Intenta nuevamente.'];
    }
}

/**
 * Función para limpiar tokens expirados (ejecutar diariamente)
 */
function limpiarTokensExpirados() {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            UPDATE users 
            SET verification_token = NULL, 
                verification_expires = NULL 
            WHERE verification_expires < NOW() 
            AND verification_token IS NOT NULL
        ");
        
        $affected = $stmt->execute();
        
        if ($affected > 0) {
            error_log("Limpieza automática: " . $affected . " tokens expirados eliminados.");
        }
        
        return true;
        
    } catch(PDOException $e) {
        error_log("Error limpieza tokens: " . $e->getMessage());
        return false;
    }
}

/**
 * Función para obtener estadísticas de verificación (para admin)
 */
function obtenerEstadisticasVerificacion() {
    global $pdo;
    
    try {
        $stats = [];
        
        // Total usuarios
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
        $stats['total_usuarios'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Usuarios verificados
        $stmt = $pdo->query("SELECT COUNT(*) as verificados FROM users WHERE email_verified = 1");
        $stats['usuarios_verificados'] = $stmt->fetch(PDO::FETCH_ASSOC)['verificados'];
        
        // Usuarios no verificados
        $stmt = $pdo->query("SELECT COUNT(*) as no_verificados FROM users WHERE email_verified = 0");
        $stats['usuarios_no_verificados'] = $stmt->fetch(PDO::FETCH_ASSOC)['no_verificados'];
        
        // Tokens activos (no expirados)
        $stmt = $pdo->query("SELECT COUNT(*) as tokens_activos FROM users WHERE verification_token IS NOT NULL AND verification_expires > NOW()");
        $stats['tokens_activos'] = $stmt->fetch(PDO::FETCH_ASSOC)['tokens_activos'];
        
        // Porcentaje de verificación
        $stats['porcentaje_verificacion'] = $stats['total_usuarios'] > 0 ? 
            round(($stats['usuarios_verificados'] / $stats['total_usuarios']) * 100, 1) : 0;
        
        return $stats;
        
    } catch(PDOException $e) {
        error_log("Error estadísticas verificación: " . $e->getMessage());
        return false;
    }
}

/**
 * Función para reenviar email de verificación a un usuario específico
 */
function reenviarEmailVerificacion($email) {
    global $pdo;
    
    try {
        // Buscar usuario
        $stmt = $pdo->prepare("SELECT id, name, email_verified FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            return ['success' => false, 'message' => '❌ Usuario no encontrado.'];
        }
        
        if ($user['email_verified']) {
            return ['success' => false, 'message' => '✅ Tu email ya está verificado.'];
        }
        
        // Generar nuevo token
        $newToken = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));
        
        // Actualizar token
        $updateStmt = $pdo->prepare("
            UPDATE users 
            SET verification_token = ?, verification_expires = ?
            WHERE id = ?
        ");
        $updateStmt->execute([$newToken, $expiresAt, $user['id']]);
        
        // Enviar email
        $verificationUrl = "https://pinchesupplies.com.ar/verificar-email.php?token=" . $newToken . "&email=" . urlencode($email);
        
        if (sendVerificationEmail($email, $user['name'], $verificationUrl)) {
            return ['success' => true, 'message' => '✅ Email de verificación reenviado exitosamente.'];
        } else {
            return ['success' => false, 'message' => '❌ Error al enviar el email.'];
        }
        
    } catch(PDOException $e) {
        error_log("Error reenvío email: " . $e->getMessage());
        return ['success' => false, 'message' => '❌ Error en la base de datos.'];
    }
}

// =================================
// CONFIGURACIÓN DE TAREAS AUTOMÁTICAS
// =================================

// Para ejecutar limpieza de tokens diariamente, agregar a cronjob:
// 0 2 * * * php /path/to/your/site/limpiar-tokens.php

?>