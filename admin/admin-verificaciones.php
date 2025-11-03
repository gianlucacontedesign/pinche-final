<?php
/**
 * Panel de Administración - Verificaciones de Email
 * Sistema completo para gestionar verificaciones de usuarios
 * 
 * @author Sistema Admin Pinche Supplies
 * @version 1.0
 */

// Incluir configuración
require_once __DIR__ . '/config-admin.php';

// Configuración de la sesión y autenticación
session_start();

// Configuración de tiempo de sesión
if (defined('SESSION_TIMEOUT')) {
    ini_set('session.gc_maxlifetime', SESSION_TIMEOUT * 60);
}

// Conexión a la base de datos
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", 
        DB_USER, 
        DB_PASS, 
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch(PDOException $e) {
    // Log del error
    if (function_exists('logAdminActivity')) {
        logAdminActivity('DB_CONNECTION_ERROR', $e->getMessage());
    }
    
    // Mostrar error amigable
    die('
    <div style="max-width: 600px; margin: 50px auto; padding: 30px; background: #fee2e2; border: 1px solid #fecaca; border-radius: 8px; color: #991b1b;">
        <h3>Error de Conexión</h3>
        <p>No se puede conectar con la base de datos. Verifica la configuración en <code>config-admin.php</code></p>
        <p><small>Error técnico: ' . htmlspecialchars($e->getMessage()) . '</small></p>
    </div>
    ');
}

// Variables de autenticación
$is_logged_in = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
$current_admin = $_SESSION['admin_email'] ?? '';

// Función para escapar HTML
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Función para enviar emails (simplificada)
function sendVerificationEmail($email, $name, $verificationUrl) {
    $subject = "Verificación de Email - Pinche Supplies";
    $message = "
    <html>
    <head>
        <title>Verificación de Email</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #2563eb; color: white; padding: 20px; text-align: center; }
            .content { padding: 30px 20px; background: #f9f9f9; }
            .button { display: inline-block; background: #2563eb; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
            .footer { padding: 20px; text-align: center; font-size: 12px; color: #666; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Verificación de Email</h1>
            </div>
            <div class='content'>
                <h2>Hola $name,</h2>
                <p>Gracias por registrarte en Pinche Supplies. Para completar tu registro, necesitas verificar tu dirección de email.</p>
                <p>Haz clic en el botón de abajo para verificar tu email:</p>
                <div style='text-align: center;'>
                    <a href='$verificationUrl' class='button'>Verificar Email</a>
                </div>
                <p>Si el botón no funciona, copia y pega este enlace en tu navegador:</p>
                <p><a href='$verificationUrl'>$verificationUrl</a></p>
                <p><strong>Este enlace expira en 24 horas.</strong></p>
            </div>
            <div class='footer'>
                <p>Si no creaste esta cuenta, ignora este email.</p>
                <p>&copy; 2024 Pinche Supplies. Todos los derechos reservados.</p>
            </div>
        </div>
    </body>
    </html>";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: no-reply@pinchesupplies.com.ar" . "\r\n";
    
    return mail($email, $subject, $message, $headers);
}

// Procesar login
if (!$is_logged_in && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $admin_email = $_POST['email'] ?? '';
    $admin_password = $_POST['password'] ?? '';
    
    // Verificar credenciales
    $login_success = false;
    
    // Opción 1: Verificación simple
    if ($admin_email === ADMIN_EMAIL && $admin_password === ADMIN_PASSWORD) {
        $login_success = true;
    }
    // Opción 2: Verificación con hash (si está configurado)
    elseif (defined('ADMIN_PASSWORD_HASH') && $admin_email === ADMIN_EMAIL && password_verify($admin_password, ADMIN_PASSWORD_HASH)) {
        $login_success = true;
    }
    
    if ($login_success) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_email'] = $admin_email;
        $_SESSION['login_time'] = time();
        $is_logged_in = true;
        $current_admin = $admin_email;
        
        // Log de actividad
        if (function_exists('logAdminActivity')) {
            logAdminActivity('LOGIN_SUCCESS', 'Admin login successful', null);
        }
    } else {
        $login_error = "❌ Email o contraseña incorrectos.";
        
        // Log de intento fallido
        if (function_exists('logAdminActivity')) {
            logAdminActivity('LOGIN_FAILED', 'Failed login attempt for: ' . $admin_email);
        }
    }
}

// Procesar logout
if (isset($_GET['logout'])) {
    // Log de actividad
    if (function_exists('logAdminActivity') && isset($_SESSION['admin_email'])) {
        logAdminActivity('LOGOUT', 'Admin logged out', null);
    }
    
    session_destroy();
    header('Location: admin-verificaciones.php');
    exit;
}

// Si no está logueado, mostrar formulario de login
if (!$is_logged_in):
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Pinche Supplies</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            max-width: 400px;
            width: 100%;
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-header i {
            font-size: 3rem;
            color: #667eea;
            margin-bottom: 15px;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <i class="fas fa-shield-alt"></i>
            <h2>Panel de Administración</h2>
            <p class="text-muted">Pinche Supplies - Verificaciones</p>
        </div>
        
        <?php if (isset($login_error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i> <?= e($login_error) ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="mb-3">
                <label for="email" class="form-label">
                    <i class="fas fa-envelope"></i> Email de Administrador
                </label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            
            <div class="mb-3">
                <label for="password" class="form-label">
                    <i class="fas fa-lock"></i> Contraseña
                </label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            
            <button type="submit" name="login" class="btn btn-primary w-100">
                <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
            </button>
        </form>
        
        <div class="text-center mt-3">
            <small class="text-muted">
                <i class="fas fa-info-circle"></i> 
                Sistema seguro - Solo administradores autorizados
            </small>
        </div>
    </div>
</body>
</html>
<?php
exit;
endif;

// Obtener estadísticas
function obtenerEstadisticas() {
    global $pdo;
    
    try {
        $stats = [];
        
        // Total usuarios
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
        $stats['total_usuarios'] = $stmt->fetch()['total'];
        
        // Usuarios verificados
        $stmt = $pdo->query("SELECT COUNT(*) as verificados FROM users WHERE email_verified = 1");
        $stats['usuarios_verificados'] = $stmt->fetch()['verificados'];
        
        // Usuarios no verificados
        $stmt = $pdo->query("SELECT COUNT(*) as no_verificados FROM users WHERE email_verified = 0");
        $stats['usuarios_no_verificados'] = $stmt->fetch()['no_verificados'];
        
        // Tokens activos
        $stmt = $pdo->query("SELECT COUNT(*) as tokens_activos FROM users WHERE verification_token IS NOT NULL AND verification_expires > NOW()");
        $stats['tokens_activos'] = $stmt->fetch()['tokens_activos'];
        
        // Porcentaje de verificación
        $stats['porcentaje_verificacion'] = $stats['total_usuarios'] > 0 ? 
            round(($stats['usuarios_verificados'] / $stats['total_usuarios']) * 100, 1) : 0;
        
        return $stats;
        
    } catch(PDOException $e) {
        error_log("Error estadísticas: " . $e->getMessage());
        return [
            'total_usuarios' => 0,
            'usuarios_verificados' => 0,
            'usuarios_no_verificados' => 0,
            'tokens_activos' => 0,
            'porcentaje_verificacion' => 0
        ];
    }
}

// Procesar acciones AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'reenviar_email':
            $user_id = (int)$_POST['user_id'];
            
            try {
                // Obtener datos del usuario
                $stmt = $pdo->prepare("SELECT id, name, email, email_verified FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $user = $stmt->fetch();
                
                if (!$user) {
                    echo json_encode(['success' => false, 'message' => '❌ Usuario no encontrado.']);
                    exit;
                }
                
                if ($user['email_verified']) {
                    echo json_encode(['success' => false, 'message' => '✅ El email ya está verificado.']);
                    exit;
                }
                
                // Generar nuevo token
                $newToken = bin2hex(random_bytes(32));
                $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));
                
                // Actualizar token
                $updateStmt = $pdo->prepare("UPDATE users SET verification_token = ?, verification_expires = ? WHERE id = ?");
                $updateStmt->execute([$newToken, $expiresAt, $user_id]);
                
                // Enviar email
                $verificationUrl = "https://tudominio.com/verificar-email.php?token=" . $newToken . "&email=" . urlencode($user['email']);
                
                if (sendVerificationEmail($user['email'], $user['name'], $verificationUrl)) {
                    echo json_encode(['success' => true, 'message' => '✅ Email de verificación reenviado.']);
                } else {
                    echo json_encode(['success' => false, 'message' => '❌ Error al enviar el email.']);
                }
                
            } catch(PDOException $e) {
                echo json_encode(['success' => false, 'message' => '❌ Error en la base de datos.']);
            }
            exit;
            
        case 'marcar_verificado':
            $user_id = (int)$_POST['user_id'];
            
            try {
                $stmt = $pdo->prepare("UPDATE users SET email_verified = 1, verification_token = NULL, verification_expires = NULL WHERE id = ?");
                $result = $stmt->execute([$user_id]);
                
                if ($result) {
                    echo json_encode(['success' => true, 'message' => '✅ Usuario marcado como verificado.']);
                } else {
                    echo json_encode(['success' => false, 'message' => '❌ Error al actualizar.']);
                }
                
            } catch(PDOException $e) {
                echo json_encode(['success' => false, 'message' => '❌ Error en la base de datos.']);
            }
            exit;
    }
}

// Obtener filtros
$search = $_GET['search'] ?? '';
$filter_status = $_GET['status'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;

// Construir consulta
$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(name LIKE ? OR email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($filter_status === 'verified') {
    $where_conditions[] = "email_verified = 1";
} elseif ($filter_status === 'unverified') {
    $where_conditions[] = "email_verified = 0";
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Contar total
$count_sql = "SELECT COUNT(*) as total FROM users $where_clause";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_records = $count_stmt->fetch()['total'];
$total_pages = ceil($total_records / $limit);

// Obtener usuarios
$sql = "SELECT id, name, email, email_verified, verification_token, verification_expires, created_at 
        FROM users $where_clause 
        ORDER BY created_at DESC 
        LIMIT $limit OFFSET $offset";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

$stats = obtenerEstadisticas();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin - Verificaciones | Pinche Supplies</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #667eea;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #3b82f6;
        }
        
        body {
            background: #f8fafc;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .sidebar {
            background: linear-gradient(180deg, var(--primary) 0%, #4f46e5 100%);
            min-height: 100vh;
            color: white;
        }
        
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            margin: 5px 0;
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background: rgba(255,255,255,0.1);
        }
        
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
            transition: transform 0.2s;
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
        }
        
        .stat-icon.primary { background: var(--primary); }
        .stat-icon.success { background: var(--success); }
        .stat-icon.warning { background: var(--warning); }
        .stat-icon.info { background: var(--info); }
        
        .user-table {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
        
        .table th {
            background: #f8fafc;
            border: none;
            font-weight: 600;
            padding: 15px;
            color: #374151;
        }
        
        .table td {
            padding: 15px;
            border-color: #e5e7eb;
            vertical-align: middle;
        }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-verified {
            background: #d1fae5;
            color: #065f46;
        }
        
        .status-unverified {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .action-btn.reenviar {
            background: var(--info);
            color: white;
        }
        
        .action-btn.verificar {
            background: var(--success);
            color: white;
        }
        
        .action-btn:hover {
            transform: translateY(-1px);
            opacity: 0.9;
        }
        
        .search-box {
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            padding: 12px 15px;
            transition: border-color 0.2s;
        }
        
        .search-box:focus {
            border-color: var(--primary);
            outline: none;
        }
        
        .progress-custom {
            height: 8px;
            border-radius: 4px;
            background: #e5e7eb;
        }
        
        .progress-bar-custom {
            background: linear-gradient(90deg, var(--success), var(--primary));
            border-radius: 4px;
        }
        
        .page-header {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            margin-bottom: 25px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0">
                <div class="sidebar">
                    <div class="p-4">
                        <h4 class="mb-0">
                            <i class="fas fa-store"></i> Pinche Supplies
                        </h4>
                        <small class="opacity-75">Panel de Administración</small>
                    </div>
                    
                    <nav class="nav flex-column px-3">
                        <a class="nav-link active" href="admin-verificaciones.php">
                            <i class="fas fa-envelope-check me-2"></i> Verificaciones Email
                        </a>
                        <a class="nav-link" href="#">
                            <i class="fas fa-users me-2"></i> Usuarios
                        </a>
                        <a class="nav-link" href="#">
                            <i class="fas fa-shopping-cart me-2"></i> Pedidos
                        </a>
                        <a class="nav-link" href="#">
                            <i class="fas fa-cog me-2"></i> Configuración
                        </a>
                    </nav>
                    
                    <div class="mt-auto p-3">
                        <div class="border-top border-light pt-3">
                            <small class="opacity-75">
                                <i class="fas fa-user-shield me-1"></i>
                                Conectado como: <?= e($current_admin) ?>
                            </small>
                            <br>
                            <a href="?logout=1" class="btn btn-sm btn-outline-light mt-2">
                                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Contenido principal -->
            <div class="col-md-9 col-lg-10">
                <div class="p-4">
                    <!-- Header -->
                    <div class="page-header">
                        <div class="row align-items-center">
                            <div class="col">
                                <h1 class="h3 mb-1">
                                    <i class="fas fa-envelope-check text-primary me-2"></i>
                                    Gestión de Verificaciones
                                </h1>
                                <p class="text-muted mb-0">Administra las verificaciones de email de los usuarios</p>
                            </div>
                            <div class="col-auto">
                                <div class="text-muted">
                                    <i class="fas fa-calendar me-1"></i>
                                    <?= date('d/m/Y H:i') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Estadísticas -->
                    <div class="row mb-4">
                        <div class="col-md-3 mb-3">
                            <div class="stat-card">
                                <div class="d-flex align-items-center">
                                    <div class="stat-icon primary me-3">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <div>
                                        <h3 class="mb-1"><?= $stats['total_usuarios'] ?></h3>
                                        <p class="text-muted mb-0">Total Usuarios</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <div class="stat-card">
                                <div class="d-flex align-items-center">
                                    <div class="stat-icon success me-3">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <div>
                                        <h3 class="mb-1"><?= $stats['usuarios_verificados'] ?></h3>
                                        <p class="text-muted mb-0">Verificados</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <div class="stat-card">
                                <div class="d-flex align-items-center">
                                    <div class="stat-icon warning me-3">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                    <div>
                                        <h3 class="mb-1"><?= $stats['usuarios_no_verificados'] ?></h3>
                                        <p class="text-muted mb-0">No Verificados</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <div class="stat-card">
                                <div class="d-flex align-items-center">
                                    <div class="stat-icon info me-3">
                                        <i class="fas fa-percentage"></i>
                                    </div>
                                    <div>
                                        <h3 class="mb-1"><?= $stats['porcentaje_verificacion'] ?>%</h3>
                                        <p class="text-muted mb-0">Tasa Éxito</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Barra de progreso -->
                    <div class="stat-card mb-4">
                        <h5 class="mb-3">Progreso de Verificación</h5>
                        <div class="progress progress-custom mb-2">
                            <div class="progress-bar progress-bar-custom" style="width: <?= $stats['porcentaje_verificacion'] ?>%"></div>
                        </div>
                        <small class="text-muted">
                            <?= $stats['usuarios_verificados'] ?> de <?= $stats['total_usuarios'] ?> usuarios han verificado su email
                            (<?= $stats['porcentaje_verificacion'] ?>%)
                        </small>
                    </div>
                    
                    <!-- Filtros y búsqueda -->
                    <div class="stat-card mb-4">
                        <form method="GET" class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Buscar usuarios</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-search"></i>
                                    </span>
                                    <input type="text" class="form-control search-box" name="search" 
                                           placeholder="Nombre o email..." value="<?= e($search) ?>">
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label">Estado</label>
                                <select name="status" class="form-select">
                                    <option value="">Todos los estados</option>
                                    <option value="verified" <?= $filter_status === 'verified' ? 'selected' : '' ?>>Verificados</option>
                                    <option value="unverified" <?= $filter_status === 'unverified' ? 'selected' : '' ?>>No verificados</option>
                                </select>
                            </div>
                            
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-filter"></i> Filtrar
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Tabla de usuarios -->
                    <div class="user-table">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Usuario</th>
                                        <th>Email</th>
                                        <th>Estado</th>
                                        <th>Fecha Registro</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($users)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-4">
                                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                                <p class="text-muted mb-0">No se encontraron usuarios</p>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($users as $user): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar bg-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                                            <i class="fas fa-user text-white"></i>
                                                        </div>
                                                        <div>
                                                            <strong><?= e($user['name']) ?></strong>
                                                            <br>
                                                            <small class="text-muted">ID: <?= $user['id'] ?></small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <i class="fas fa-envelope text-muted me-1"></i>
                                                    <?= e($user['email']) ?>
                                                </td>
                                                <td>
                                                    <?php if ($user['email_verified']): ?>
                                                        <span class="status-badge status-verified">
                                                            <i class="fas fa-check me-1"></i> Verificado
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="status-badge status-unverified">
                                                            <i class="fas fa-clock me-1"></i> Pendiente
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <i class="fas fa-calendar text-muted me-1"></i>
                                                    <?= date('d/m/Y', strtotime($user['created_at'])) ?>
                                                    <br>
                                                    <small class="text-muted"><?= date('H:i', strtotime($user['created_at'])) ?></small>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <?php if (!$user['email_verified']): ?>
                                                            <button class="action-btn reenviar" 
                                                                    onclick="reenviarEmail(<?= $user['id'] ?>)"
                                                                    title="Reenviar email">
                                                                <i class="fas fa-envelope"></i> Reenviar
                                                            </button>
                                                            
                                                            <button class="action-btn verificar" 
                                                                    onclick="marcarVerificado(<?= $user['id'] ?>)"
                                                                    title="Marcar como verificado">
                                                                <i class="fas fa-check"></i> Verificar
                                                            </button>
                                                        <?php else: ?>
                                                            <span class="text-success">
                                                                <i class="fas fa-check-circle"></i> Completado
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Paginación -->
                    <?php if ($total_pages > 1): ?>
                        <nav aria-label="Paginación" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($filter_status) ?>">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($filter_status) ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($filter_status) ?>">
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast para notificaciones -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="notificationToast" class="toast" role="alert">
            <div class="toast-header">
                <i class="fas fa-bell text-primary me-2"></i>
                <strong class="me-auto">Notificación</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body" id="toastMessage">
                <!-- Mensaje dinámico -->
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Función para mostrar notificaciones
        function showNotification(message, type = 'success') {
            const toast = document.getElementById('notificationToast');
            const toastMessage = document.getElementById('toastMessage');
            const toastHeader = toast.querySelector('.toast-header');
            
            // Cambiar icono según el tipo
            const icon = toastHeader.querySelector('i');
            if (type === 'success') {
                icon.className = 'fas fa-check-circle text-success me-2';
            } else {
                icon.className = 'fas fa-exclamation-triangle text-danger me-2';
            }
            
            toastMessage.textContent = message;
            
            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();
        }
        
        // Función para reenviar email
        async function reenviarEmail(userId) {
            try {
                const formData = new FormData();
                formData.append('action', 'reenviar_email');
                formData.append('user_id', userId);
                
                const response = await fetch('admin-verificaciones.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showNotification(result.message, 'success');
                } else {
                    showNotification(result.message, 'error');
                }
                
            } catch (error) {
                showNotification('Error al procesar la solicitud', 'error');
            }
        }
        
        // Función para marcar como verificado
        async function marcarVerificado(userId) {
            if (!confirm('¿Estás seguro de marcar este usuario como verificado?')) {
                return;
            }
            
            try {
                const formData = new FormData();
                formData.append('action', 'marcar_verificado');
                formData.append('user_id', userId);
                
                const response = await fetch('admin-verificaciones.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showNotification(result.message, 'success');
                    // Recargar la página después de un momento
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    showNotification(result.message, 'error');
                }
                
            } catch (error) {
                showNotification('Error al procesar la solicitud', 'error');
            }
        }
        
        // Auto-refresh cada 30 segundos
        setInterval(() => {
            const urlParams = new URLSearchParams(window.location.search);
            if (!urlParams.has('search') && !urlParams.has('status')) {
                location.reload();
            }
        }, 30000);
    </script>
</body>
</html>