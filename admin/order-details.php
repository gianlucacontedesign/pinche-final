<?php
/**
 * Detalles del Pedido
 * Vista completa de un pedido con cambio de estado, impresión, etc.
 */

require_once __DIR__ . '/../config/config.php';
$auth = new Auth();
$auth->requireLogin();

if (!isset($_GET['id'])) {
    header('Location: orders.php');
    exit;
}

$orderId = (int)$_GET['id'];
$db = Database::getInstance();
$orderModel = new Order();

// Obtener orden
$order = $db->fetchOne("
    SELECT o.*, 
    CASE 
        WHEN o.customer_id IS NOT NULL THEN CONCAT(c.first_name, ' ', c.last_name)
        ELSE o.customer_name
    END as customer_display_name,
    CASE 
        WHEN o.customer_id IS NOT NULL THEN c.email
        ELSE o.customer_email
    END as customer_display_email,
    CASE 
        WHEN o.customer_id IS NOT NULL THEN c.phone
        ELSE o.customer_phone
    END as customer_display_phone
    FROM orders o
    LEFT JOIN customers c ON o.customer_id = c.id
    WHERE o.id = ?
", [$orderId]);

if (!$order) {
    setFlashMessage('Pedido no encontrado', 'error');
    header('Location: orders.php');
    exit;
}

// Obtener items del pedido
$items = $orderModel->getItems($orderId);

// Cambiar estado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_status'])) {
    $newStatus = $_POST['status'];
    $result = $orderModel->updateStatus($orderId, $newStatus);
    
    if ($result) {
        setFlashMessage('Estado actualizado correctamente', 'success');
        header('Location: order-details.php?id=' . $orderId);
        exit;
    } else {
        setFlashMessage('Error al actualizar el estado', 'error');
    }
}

// Función para obtener badge de estado
function getStatusBadge($status) {
    $badges = [
        'pending' => '<span class="badge badge-warning">Pendiente</span>',
        'processing' => '<span class="badge badge-info">Procesando</span>',
        'shipped' => '<span class="badge badge-primary">Enviado</span>',
        'delivered' => '<span class="badge badge-success">Entregado</span>',
        'cancelled' => '<span class="badge badge-danger">Cancelado</span>'
    ];
    return $badges[$status] ?? '<span class="badge">' . ucfirst($status) . '</span>';
}

$pageTitle = 'Pedido #' . $order['order_number'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Panel de Administración</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?php echo ADMIN_URL; ?>/assets/css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .order-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 24px;
        }
        .badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 600;
        }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-info { background: #dbeafe; color: #1e40af; }
        .badge-primary { background: #e0e7ff; color: #3730a3; }
        .badge-success { background: #d1fae5; color: #065f46; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .timeline {
            position: relative;
            padding-left: 30px;
        }
        .timeline-item {
            position: relative;
            padding-bottom: 20px;
        }
        .timeline-item:last-child {
            padding-bottom: 0;
        }
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -22px;
            top: 8px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #e5e7eb;
            border: 3px solid white;
            box-shadow: 0 0 0 1px #e5e7eb;
        }
        .timeline-item.active::before {
            background: #6b46c1;
            box-shadow: 0 0 0 1px #6b46c1;
        }
        .timeline-item::after {
            content: '';
            position: absolute;
            left: -17px;
            top: 20px;
            bottom: 0;
            width: 2px;
            background: #e5e7eb;
        }
        .timeline-item:last-child::after {
            display: none;
        }
        @media (max-width: 1024px) {
            .order-grid {
                grid-template-columns: 1fr;
            }
        }
        @media print {
            .no-print { display: none !important; }
            .admin-sidebar, .admin-header { display: none !important; }
            .admin-content { padding: 0 !important; margin: 0 !important; }
        }
    </style>
</head>
<body>
    <?php include 'includes/admin-header.php'; ?>
    
    <div class="admin-layout">
        <?php include 'includes/admin-sidebar.php'; ?>
        
        <main class="admin-content">
            <div class="admin-header-page no-print">
                <div>
                    <h1>Pedido #<?php echo e($order['order_number']); ?></h1>
                    <p style="color: #737373;">
                        Realizado el <?php echo date('d/m/Y \a \l\a\s H:i', strtotime($order['created_at'])); ?>
                    </p>
                </div>
                <div style="display: flex; gap: 12px;">
                    <button onclick="window.print()" class="btn-admin btn-admin-secondary">🖨️ Imprimir</button>
                    <a href="orders.php" class="btn-admin btn-admin-secondary">← Volver a Pedidos</a>
                </div>
            </div>
            
            <?php if (getFlashMessage()): ?>
                <div class="alert alert-<?php echo getFlashMessage()['type']; ?>" style="margin-bottom: 20px;">
                    <?php echo getFlashMessage()['message']; ?>
                </div>
            <?php endif; ?>
            
            <div class="order-grid">
                <!-- Columna izquierda: Items del pedido -->
                <div>
                    <div class="admin-card">
                        <h2 style="font-size: 18px; font-weight: 600; margin-bottom: 20px;">Productos del Pedido</h2>
                        
                        <div class="table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Precio</th>
                                        <th>Cantidad</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td>
                                            <div>
                                                <p style="font-weight: 600; margin-bottom: 4px;">
                                                    <?php echo e($item['product_name']); ?>
                                                </p>
                                                <?php if ($item['variant_info']): ?>
                                                <p style="font-size: 13px; color: #737373; margin: 0;">
                                                    <?php 
                                                    $variant = json_decode($item['variant_info'], true);
                                                    echo e($variant['name'] ?? '');
                                                    ?>
                                                </p>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td><?php echo formatPrice($item['price']); ?></td>
                                        <td>×<?php echo $item['quantity']; ?></td>
                                        <td style="font-weight: 600;"><?php echo formatPrice($item['subtotal']); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" style="text-align: right; font-weight: 600;">Subtotal:</td>
                                        <td style="font-weight: 600;"><?php echo formatPrice($order['subtotal']); ?></td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" style="text-align: right; font-weight: 600;">Envío:</td>
                                        <td style="font-weight: 600;"><?php echo formatPrice($order['shipping_cost']); ?></td>
                                    </tr>
                                    <?php if ($order['discount'] > 0): ?>
                                    <tr>
                                        <td colspan="3" style="text-align: right; font-weight: 600; color: #059669;">Descuento:</td>
                                        <td style="font-weight: 600; color: #059669;">-<?php echo formatPrice($order['discount']); ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <tr style="background: #f9fafb;">
                                        <td colspan="3" style="text-align: right; font-weight: 700; font-size: 16px;">TOTAL:</td>
                                        <td style="font-weight: 700; font-size: 16px; color: #6b46c1;"><?php echo formatPrice($order['total']); ?></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Información de envío -->
                    <div class="admin-card" style="margin-top: 24px;">
                        <h2 style="font-size: 18px; font-weight: 600; margin-bottom: 20px;">Dirección de Envío</h2>
                        
                        <div style="padding: 16px; background: #f9fafb; border-radius: 8px;">
                            <p style="font-weight: 600; margin-bottom: 12px;">
                                <?php echo e($order['shipping_name']); ?>
                            </p>
                            <p style="margin-bottom: 8px; color: #4b5563;">
                                <?php echo e($order['shipping_address']); ?><br>
                                <?php echo e($order['shipping_city']); ?>, <?php echo e($order['shipping_state']); ?><br>
                                <?php echo e($order['shipping_zip']); ?> - <?php echo e($order['shipping_country']); ?>
                            </p>
                            <?php if ($order['shipping_phone']): ?>
                            <p style="margin: 0; color: #4b5563;">
                                📞 <?php echo e($order['shipping_phone']); ?>
                            </p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if ($order['notes']): ?>
                    <div class="admin-card" style="margin-top: 24px;">
                        <h2 style="font-size: 18px; font-weight: 600; margin-bottom: 20px;">Notas del Pedido</h2>
                        <div style="padding: 16px; background: #fffbeb; border-radius: 8px; border-left: 4px solid #f59e0b;">
                            <p style="margin: 0; color: #92400e;">
                                <?php echo nl2br(e($order['notes'])); ?>
                            </p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Columna derecha: Información y estado -->
                <div>
                    <!-- Información del cliente -->
                    <div class="admin-card">
                        <h2 style="font-size: 18px; font-weight: 600; margin-bottom: 20px;">Información del Cliente</h2>
                        
                        <div style="display: flex; flex-direction: column; gap: 12px;">
                            <div>
                                <p style="font-size: 13px; color: #737373; margin-bottom: 4px;">Nombre</p>
                                <p style="font-weight: 600; margin: 0;">
                                    <?php echo e($order['customer_display_name']); ?>
                                    <?php if ($order['customer_id']): ?>
                                    <a href="customers.php?id=<?php echo $order['customer_id']; ?>" 
                                       style="font-size: 12px; color: #6b46c1; margin-left: 8px;">(Ver perfil)</a>
                                    <?php endif; ?>
                                </p>
                            </div>
                            
                            <div>
                                <p style="font-size: 13px; color: #737373; margin-bottom: 4px;">Email</p>
                                <p style="margin: 0;">
                                    <a href="mailto:<?php echo e($order['customer_display_email']); ?>" 
                                       style="color: #111827; text-decoration: none;">
                                        <?php echo e($order['customer_display_email']); ?>
                                    </a>
                                </p>
                            </div>
                            
                            <?php if ($order['customer_display_phone']): ?>
                            <div>
                                <p style="font-size: 13px; color: #737373; margin-bottom: 4px;">Teléfono</p>
                                <p style="margin: 0;">
                                    <a href="tel:<?php echo e($order['customer_display_phone']); ?>" 
                                       style="color: #111827; text-decoration: none;">
                                        <?php echo e($order['customer_display_phone']); ?>
                                    </a>
                                </p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Cambiar estado -->
                    <div class="admin-card no-print" style="margin-top: 24px;">
                        <h2 style="font-size: 18px; font-weight: 600; margin-bottom: 20px;">Estado del Pedido</h2>
                        
                        <div style="margin-bottom: 20px;">
                            <?php echo getStatusBadge($order['status']); ?>
                        </div>
                        
                        <form method="POST">
                            <input type="hidden" name="change_status" value="1">
                            <div class="form-group">
                                <label class="form-label">Cambiar estado a:</label>
                                <select name="status" class="form-control" required>
                                    <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>
                                        Pendiente
                                    </option>
                                    <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>
                                        Procesando
                                    </option>
                                    <option value="shipped" <?php echo $order['status'] === 'shipped' ? 'selected' : ''; ?>>
                                        Enviado
                                    </option>
                                    <option value="delivered" <?php echo $order['status'] === 'delivered' ? 'selected' : ''; ?>>
                                        Entregado
                                    </option>
                                    <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>
                                        Cancelado
                                    </option>
                                </select>
                            </div>
                            <button type="submit" class="btn-admin btn-admin-primary" style="width: 100%;">
                                Actualizar Estado
                            </button>
                        </form>
                    </div>
                    
                    <!-- Timeline de estados -->
                    <div class="admin-card" style="margin-top: 24px;">
                        <h2 style="font-size: 18px; font-weight: 600; margin-bottom: 20px;">Historial</h2>
                        
                        <div class="timeline">
                            <div class="timeline-item active">
                                <p style="font-weight: 600; margin-bottom: 4px;">Pedido creado</p>
                                <p style="font-size: 13px; color: #737373; margin: 0;">
                                    <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?>
                                </p>
                            </div>
                            
                            <?php if (in_array($order['status'], ['processing', 'shipped', 'delivered'])): ?>
                            <div class="timeline-item active">
                                <p style="font-weight: 600; margin-bottom: 4px;">En procesamiento</p>
                                <p style="font-size: 13px; color: #737373; margin: 0;">
                                    Estado: Procesando
                                </p>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (in_array($order['status'], ['shipped', 'delivered'])): ?>
                            <div class="timeline-item active">
                                <p style="font-weight: 600; margin-bottom: 4px;">Pedido enviado</p>
                                <p style="font-size: 13px; color: #737373; margin: 0;">
                                    Estado: Enviado
                                </p>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($order['status'] === 'delivered'): ?>
                            <div class="timeline-item active">
                                <p style="font-weight: 600; margin-bottom: 4px;">Pedido entregado</p>
                                <p style="font-size: 13px; color: #737373; margin: 0;">
                                    Estado: Entregado
                                </p>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($order['status'] === 'cancelled'): ?>
                            <div class="timeline-item" style="color: #ef4444;">
                                <p style="font-weight: 600; margin-bottom: 4px;">Pedido cancelado</p>
                                <p style="font-size: 13px; margin: 0;">
                                    Estado: Cancelado
                                </p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Información de pago -->
                    <div class="admin-card" style="margin-top: 24px;">
                        <h2 style="font-size: 18px; font-weight: 600; margin-bottom: 20px;">Información de Pago</h2>
                        
                        <div style="display: flex; flex-direction: column; gap: 12px;">
                            <div>
                                <p style="font-size: 13px; color: #737373; margin-bottom: 4px;">Método de pago</p>
                                <p style="font-weight: 600; margin: 0;">
                                    <?php 
                                    $paymentMethods = [
                                        'transfer' => 'Transferencia Bancaria',
                                        'cash' => 'Efectivo',
                                        'card' => 'Tarjeta de Crédito/Débito'
                                    ];
                                    echo $paymentMethods[$order['payment_method']] ?? ucfirst($order['payment_method']);
                                    ?>
                                </p>
                            </div>
                            
                            <div>
                                <p style="font-size: 13px; color: #737373; margin-bottom: 4px;">Estado de pago</p>
                                <p style="margin: 0;">
                                    <?php if ($order['payment_status'] === 'paid'): ?>
                                        <span class="badge badge-success">Pagado</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Pendiente</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
