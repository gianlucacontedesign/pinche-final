<?php
require_once __DIR__ . '/../config/config.php';

$auth = new Auth();

// Si ya está logueado, redirigir al dashboard
if ($auth->isLoggedIn()) {
    header('Location: ' . ADMIN_URL . '/index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($auth->login($username, $password)) {
        header('Location: ' . ADMIN_URL . '/index.php');
        exit;
    } else {
        $error = 'Usuario o contraseña incorrectos';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Panel de Administración</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body style="background: linear-gradient(135deg, #6b46c1 0%, #553c9a 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 1rem; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); padding: 3rem; width: 100%; max-width: 450px;">
        <div style="text-align: center; margin-bottom: 2rem;">
            <h1 style="font-size: 2rem; font-weight: 800; color: #6b46c1; margin-bottom: 0.5rem;">
                <?php echo e(SITE_NAME); ?>
            </h1>
            <p style="color: #737373;">Panel de Administración</p>
        </div>
        
        <?php if ($error): ?>
        <div style="background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem;">
            <?php echo e($error); ?>
        </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #404040;">
                    Usuario
                </label>
                <input type="text" 
                       name="username" 
                       required
                       autofocus
                       style="width: 100%; padding: 0.75rem; border: 2px solid #d4d4d4; border-radius: 0.5rem; font-size: 1rem;">
            </div>
            
            <div style="margin-bottom: 2rem;">
                <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #404040;">
                    Contraseña
                </label>
                <input type="password" 
                       name="password" 
                       required
                       style="width: 100%; padding: 0.75rem; border: 2px solid #d4d4d4; border-radius: 0.5rem; font-size: 1rem;">
            </div>
            
            <button type="submit" class="btn btn-primary btn-full btn-lg">
                Iniciar Sesión
            </button>
        </form>
        
        <div style="margin-top: 2rem; text-align: center; font-size: 0.875rem; color: #737373;">
            <p>Usuario por defecto: <strong>admin</strong></p>
            <p>Contraseña por defecto: <strong>admin123</strong></p>
            <p style="color: #dc2626; margin-top: 0.5rem;">⚠️ Cambiar en producción</p>
        </div>
    </div>
</body>
</html>
