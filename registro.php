<?php
/**
 * EJEMPLO DE REGISTRO.PHP ACTUALIZADO
 * Con verificación por email
 */

// Incluir configuración y funciones
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/email-sender.php';

// Variables para mensajes
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener datos del formulario
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $phone = sanitize($_POST['phone'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    
    // Validaciones básicas
    $errors = [];
    
    if (empty($name)) {
        $errors[] = "El nombre es obligatorio";
    }
    
    if (empty($email)) {
        $errors[] = "El email es obligatorio";
    } elseif (!isValidEmail($email)) {
        $errors[] = "El formato del email no es válido";
    }
    
    if (empty($password)) {
        $errors[] = "La contraseña es obligatoria";
    } elseif (strlen($password) < 6) {
        $errors[] = "La contraseña debe tener al menos 6 caracteres";
    }
    
    if ($password !== $password_confirm) {
        $errors[] = "Las contraseñas no coinciden";
    }
    
    // Si no hay errores, proceder con el registro
    if (empty($errors)) {
        // Usar la función actualizada que incluye verificación
        $result = registrarUsuario($name, $email, $password, $phone, $address);
        
        if ($result['success']) {
            // ÉXITO: Redirigir a página de verificación
            $success = $result['message'];
            
            // Redirigir después de 3 segundos
            header('refresh:3;url=verificar-email.php?email=' . urlencode($email));
            
        } else {
            // ERROR: Mostrar mensaje de error
            $error = $result['message'];
        }
    } else {
        // ERROR DE VALIDACIÓN: Mostrar errores
        $error = implode('<br>', $errors);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Pinche Supplies</title>
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h2>🎯 Registrarse en Pinche Supplies</h2>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?= $success ?>
                    <br><small>Serás redirigido automáticamente...</small>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?= $error ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="name">Nombre Completo *</label>
                    <input type="text" id="name" name="name" 
                           value="<?= e($_POST['name'] ?? '') ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" 
                           value="<?= e($_POST['email'] ?? '') ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Contraseña *</label>
                    <input type="password" id="password" name="password" required>
                    <small>Mínimo 6 caracteres</small>
                </div>
                
                <div class="form-group">
                    <label for="password_confirm">Confirmar Contraseña *</label>
                    <input type="password" id="password_confirm" name="password_confirm" required>
                </div>
                
                <div class="form-group">
                    <label for="phone">Teléfono</label>
                    <input type="tel" id="phone" name="phone" 
                           value="<?= e($_POST['phone'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="address">Dirección</label>
                    <textarea id="address" name="address" rows="3"><?= e($_POST['address'] ?? '') ?></textarea>
                </div>
                
                <button type="submit" class="btn-primary">🚀 Registrarse</button>
            </form>
            
            <div class="form-links">
                <p>¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a></p>
                <p><a href="index.php">Volver al inicio</a></p>
            </div>
        </div>
    </div>
</body>
</html>