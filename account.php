<?php
/**
 * Página de Perfil/Cuenta de Cliente
 */

require_once 'config/config.php';
require_once 'includes/class.database.php';
require_once 'includes/class.auth.php';
require_once 'includes/class.customer.php';
require_once 'includes/class.order.php';
require_once 'includes/functions.php';

// La sesión ya fue iniciada en config.php

// Requiere autenticación
$auth = new Auth();
$auth->requireCustomerLogin();

$customerModel = new Customer();
$orderModel = new Order();

// Obtener datos del cliente
$customerId = $auth->getCustomerId();
$customer = $customerModel->getById($customerId);
$stats = $customerModel->getStats($customerId);

// Mensaje de bienvenida
$showWelcome = isset($_GET['welcome']) && $_GET['welcome'] == '1';

$error = '';
$success = '';

// Tab activo
$activeTab = $_GET['tab'] ?? 'profile';

// Procesar actualizaciones de perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    // Actualizar perfil
    if ($_POST['action'] === 'update_profile') {
        $result = $customerModel->updateProfile($customerId, [
            'first_name' => $_POST['first_name'] ?? '',
            'last_name' => $_POST['last_name'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'address' => $_POST['address'] ?? '',
            'city' => $_POST['city'] ?? '',
            'state' => $_POST['state'] ?? '',
            'zip_code' => $_POST['zip_code'] ?? ''
        ]);
        
        if ($result['success']) {
            $success = $result['message'];
            // Actualizar datos en sesión
            $_SESSION['customer_name'] = $_POST['first_name'] . ' ' . $_POST['last_name'];
            $_SESSION['customer_first_name'] = $_POST['first_name'];
            $_SESSION['customer_last_name'] = $_POST['last_name'];
            // Recargar datos
            $customer = $customerModel->getById($customerId);
        } else {
            $error = $result['message'];
        }
    }
    
    // Cambiar contraseña
    if ($_POST['action'] === 'change_password') {
        $result = $customerModel->changePassword(
            $customerId,
            $_POST['current_password'] ?? '',
            $_POST['new_password'] ?? ''
        );
        
        if ($result['success']) {
            $success = $result['message'];
            $activeTab = 'security';
        } else {
            $error = $result['message'];
            $activeTab = 'security';
        }
    }
}

// Obtener pedidos
$orders = $customerModel->getOrders($customerId, 20);

$pageTitle = 'Mi Cuenta - ' . SITE_NAME;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($pageTitle); ?></title>
    <meta name="description" content="Gestioná tu cuenta, perfil, pedidos y preferencias en <?php echo e(SITE_NAME); ?>.">
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>

<div class="account-page">
    <div class="account-container">
        
        <?php if ($showWelcome): ?>
            <div class="welcome-banner">
                <div class="welcome-content">
                    <h1>¡Bienvenido, <?php echo e($customer['first_name']); ?>!</h1>
                    <p>Tu cuenta ha sido creada exitosamente. Ahora podés disfrutar de todos nuestros beneficios.</p>
                </div>
                <button type="button" class="close-welcome" onclick="this.parentElement.remove()">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>
        <?php endif; ?>
        
        <div class="account-header">
            <h1>Mi Cuenta</h1>
            <p>Gestiona tu perfil, pedidos y preferencias</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error">
                <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
                <?php echo e($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                <?php echo e($success); ?>
            </div>
        <?php endif; ?>
        
        <div class="account-content">
            <!-- Sidebar con estadísticas -->
            <aside class="account-sidebar">
                <div class="account-user-card">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($customer['first_name'], 0, 1)); ?>
                    </div>
                    <h3><?php echo e($customer['first_name'] . ' ' . $customer['last_name']); ?></h3>
                    <p><?php echo e($customer['email']); ?></p>
                </div>
                
                <div class="account-stats">
                    <div class="stat-card">
                        <div class="stat-icon">🛍️</div>
                        <div class="stat-value"><?php echo $stats['total_orders']; ?></div>
                        <div class="stat-label">Pedidos</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">💰</div>
                        <div class="stat-value">$<?php echo number_format($stats['total_spent'], 0, ',', '.'); ?></div>
                        <div class="stat-label">Total Gastado</div>
                    </div>
                </div>
                
                <nav class="account-nav">
                    <a href="?tab=profile" class="account-nav-item <?php echo $activeTab === 'profile' ? 'active' : ''; ?>">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <span>Mi Perfil</span>
                    </a>
                    <a href="?tab=orders" class="account-nav-item <?php echo $activeTab === 'orders' ? 'active' : ''; ?>">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                        <span>Mis Pedidos</span>
                        <?php if ($stats['total_orders'] > 0): ?>
                            <span class="badge"><?php echo $stats['total_orders']; ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="?tab=security" class="account-nav-item <?php echo $activeTab === 'security' ? 'active' : ''; ?>">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        <span>Seguridad</span>
                    </a>
                </nav>
            </aside>
            
            <!-- Contenido principal -->
            <main class="account-main">
                
                <!-- Tab: Mi Perfil -->
                <?php if ($activeTab === 'profile'): ?>
                    <div class="account-section">
                        <div class="section-header">
                            <h2>Información Personal</h2>
                            <p>Actualiza tus datos personales y dirección de envío</p>
                        </div>
                        
                        <form method="POST" action="" class="account-form">
                            <input type="hidden" name="action" value="update_profile">
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="first_name">Nombre</label>
                                    <input type="text" id="first_name" name="first_name" 
                                           value="<?php echo e($customer['first_name']); ?>" 
                                           required class="form-control">
                                </div>
                                
                                <div class="form-group">
                                    <label for="last_name">Apellido</label>
                                    <input type="text" id="last_name" name="last_name" 
                                           value="<?php echo e($customer['last_name']); ?>" 
                                           required class="form-control">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="email_display">Email</label>
                                <input type="email" id="email_display" 
                                       value="<?php echo e($customer['email']); ?>" 
                                       disabled class="form-control">
                                <small class="form-hint">El email no se puede modificar</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="phone">Teléfono</label>
                                <input type="tel" id="phone" name="phone" 
                                       value="<?php echo e($customer['phone'] ?? ''); ?>" 
                                       class="form-control" placeholder="+54 11 1234-5678">
                            </div>
                            
                            <div class="form-divider">
                                <h3>Dirección de Envío</h3>
                            </div>
                            
                            <div class="form-group">
                                <label for="address">Dirección</label>
                                <input type="text" id="address" name="address" 
                                       value="<?php echo e($customer['address'] ?? ''); ?>" 
                                       class="form-control" placeholder="Calle y número">
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="city">Ciudad</label>
                                    <input type="text" id="city" name="city" 
                                           value="<?php echo e($customer['city'] ?? ''); ?>" 
                                           class="form-control" placeholder="Buenos Aires">
                                </div>
                                
                                <div class="form-group">
                                    <label for="state">Provincia</label>
                                    <input type="text" id="state" name="state" 
                                           value="<?php echo e($customer['state'] ?? ''); ?>" 
                                           class="form-control" placeholder="CABA">
                                </div>
                                
                                <div class="form-group">
                                    <label for="zip_code">Código Postal</label>
                                    <input type="text" id="zip_code" name="zip_code" 
                                           value="<?php echo e($customer['zip_code'] ?? ''); ?>" 
                                           class="form-control" placeholder="1234">
                                </div>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">
                                    Guardar Cambios
                                </button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
                
                <!-- Tab: Mis Pedidos -->
                <?php if ($activeTab === 'orders'): ?>
                    <div class="account-section">
                        <div class="section-header">
                            <h2>Mis Pedidos</h2>
                            <p>Historial completo de tus compras</p>
                        </div>
                        
                        <?php if (empty($orders)): ?>
                            <div class="empty-state">
                                <svg width="64" height="64" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                </svg>
                                <h3>Aún no realizaste ningún pedido</h3>
                                <p>Explorá nuestro catálogo y hacé tu primera compra</p>
                                <a href="<?php echo SITE_URL; ?>" class="btn btn-primary">
                                    Ver Productos
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="orders-list">
                                <?php foreach ($orders as $order): ?>
                                    <div class="order-card">
                                        <div class="order-header">
                                            <div class="order-info">
                                                <h4>Pedido #<?php echo e($order['order_number']); ?></h4>
                                                <p class="order-date"><?php echo date('d/m/Y', strtotime($order['created_at'])); ?></p>
                                            </div>
                                            <div class="order-status">
                                                <span class="badge badge-<?php echo e($order['status']); ?>">
                                                    <?php 
                                                    $statuses = [
                                                        'pending' => 'Pendiente',
                                                        'processing' => 'Procesando',
                                                        'shipped' => 'Enviado',
                                                        'delivered' => 'Entregado',
                                                        'cancelled' => 'Cancelado'
                                                    ];
                                                    echo $statuses[$order['status']] ?? $order['status'];
                                                    ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="order-body">
                                            <div class="order-detail">
                                                <span class="label">Total:</span>
                                                <span class="value">$<?php echo number_format($order['total'], 2, ',', '.'); ?></span>
                                            </div>
                                            <div class="order-detail">
                                                <span class="label">Estado de pago:</span>
                                                <span class="value">
                                                    <?php 
                                                    $paymentStatuses = [
                                                        'pending' => 'Pendiente',
                                                        'paid' => 'Pagado',
                                                        'failed' => 'Fallido',
                                                        'refunded' => 'Reembolsado'
                                                    ];
                                                    echo $paymentStatuses[$order['payment_status']] ?? $order['payment_status'];
                                                    ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="order-footer">
                                            <a href="<?php echo SITE_URL; ?>/order-detail.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline">
                                                Ver Detalles
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Tab: Seguridad -->
                <?php if ($activeTab === 'security'): ?>
                    <div class="account-section">
                        <div class="section-header">
                            <h2>Seguridad</h2>
                            <p>Cambiar tu contraseña y configurar opciones de seguridad</p>
                        </div>
                        
                        <form method="POST" action="" class="account-form">
                            <input type="hidden" name="action" value="change_password">
                            
                            <div class="form-group">
                                <label for="current_password">Contraseña Actual</label>
                                <input type="password" id="current_password" name="current_password" 
                                       required class="form-control" placeholder="Tu contraseña actual">
                            </div>
                            
                            <div class="form-group">
                                <label for="new_password">Nueva Contraseña</label>
                                <input type="password" id="new_password" name="new_password" 
                                       required minlength="6" class="form-control" 
                                       placeholder="Mínimo 6 caracteres">
                                <small class="form-hint">Debe tener al menos 6 caracteres</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_new_password">Confirmar Nueva Contraseña</label>
                                <input type="password" id="confirm_new_password" name="confirm_new_password" 
                                       required minlength="6" class="form-control" 
                                       placeholder="Repetir nueva contraseña">
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">
                                    Cambiar Contraseña
                                </button>
                            </div>
                        </form>
                        
                        <div class="security-info">
                            <h3>Consejos de Seguridad</h3>
                            <ul>
                                <li>Usa una contraseña única que no uses en otros sitios</li>
                                <li>Combina letras, números y símbolos</li>
                                <li>No compartas tu contraseña con nadie</li>
                                <li>Cambia tu contraseña periódicamente</li>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>
                
            </main>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
