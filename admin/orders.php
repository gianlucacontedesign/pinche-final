<?php
/**
 * Gestión de Pedidos
 * Listado, filtros, búsqueda y gestión de estados de pedidos
 */

require_once __DIR__ . '/../config/config.php';
$auth = new Auth();
$auth->requireLogin();

$orderModel = new Order();
$db = Database::getInstance();

// Parámetros de filtros
$status = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';

// Construir query con filtros
$sql = "SELECT o.*, 
        (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as items_count,
        CASE 
            WHEN o.customer_id IS NOT NULL THEN CONCAT(c.first_name, ' ', c.last_name)
            ELSE o.customer_name
        END as customer_display_name,
        CASE 
            WHEN o.customer_id IS NOT NULL THEN c.email
            ELSE o.customer_email
        END as customer_display_email
        FROM orders o
        LEFT JOIN customers c ON o.customer_id = c.id
        WHERE 1=1";

$params = [];

if (!empty($status)) {
    $sql .= " AND o.status = ?";
    $params[] = $status;
}

if (!empty($search)) {
    $sql .= " AND (o.order_number LIKE ? OR o.customer_name LIKE ? OR o.customer_email LIKE ? 
              OR CONCAT(c.first_name, ' ', c.last_name) LIKE ?)";
    $searchTerm = '%' . $search . '%';
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if (!empty($dateFrom)) {
    $sql .= " AND DATE(o.created_at) >= ?";
    $params[] = $dateFrom;
}

if (!empty($dateTo)) {
    $sql .= " AND DATE(o.created_at) <= ?";
    $params[] = $dateTo;
}

$sql .= " ORDER BY o.created_at DESC LIMIT 100";

$orders = $db->fetchAll($sql, $params);

// Estadísticas generales
$stats = [
    'total' => $db->fetchOne("SELECT COUNT(*) as count FROM orders")['count'],
    'pending' => $db->fetchOne("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'")['count'],
    'processing' => $db->fetchOne("SELECT COUNT(*) as count FROM orders WHERE status = 'processing'")['count'],
    'shipped' => $db->fetchOne("SELECT COUNT(*) as count FROM orders WHERE status = 'shipped'")['count'],
    'delivered' => $db->fetchOne("SELECT COUNT(*) as count FROM orders WHERE status = 'delivered'")['count'],
    'cancelled' => $db->fetchOne("SELECT COUNT(*) as count FROM orders WHERE status = 'cancelled'")['count']
];

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

$pageTitle = 'Gestión de Pedidos';
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
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }
        .stat-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            border: 1px solid #e5e7eb;
            transition: all 0.2s;
        }
        .stat-card:hover {
            border-color: #6b46c1;
            box-shadow: 0 4px 12px rgba(107, 70, 193, 0.1);
        }
        .stat-card h3 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 4px;
            color: #111827;
        }
        .stat-card p {
            font-size: 13px;
            color: #6b7280;
            margin: 0;
        }
        .filter-bar {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 24px;
            border: 1px solid #e5e7eb;
        }
        .filter-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 200px 200px auto;
            gap: 12px;
            align-items: end;
        }
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-info { background: #dbeafe; color: #1e40af; }
        .badge-primary { background: #e0e7ff; color: #3730a3; }
        .badge-success { background: #d1fae5; color: #065f46; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        
        @media (max-width: 1024px) {
            .filter-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/admin-header.php'; ?>
    
    <div class="admin-layout">
        <?php include 'includes/admin-sidebar.php'; ?>
        
        <main class="admin-content">
            <div class="admin-header-page">
                <div>
                    <h1>Gestión de Pedidos</h1>
                    <p style="color: #737373;">Administra todos los pedidos de tu tienda</p>
                </div>
            </div>
            
            <!-- Estadísticas -->
            <div class="stats-grid">
                <div class="stat-card">
                    <h3><?php echo $stats['total']; ?></h3>
                    <p>Total Pedidos</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $stats['pending']; ?></h3>
                    <p>Pendientes</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $stats['processing']; ?></h3>
                    <p>Procesando</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $stats['shipped']; ?></h3>
                    <p>Enviados</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $stats['delivered']; ?></h3>
                    <p>Entregados</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $stats['cancelled']; ?></h3>
                    <p>Cancelados</p>
                </div>
            </div>
            
            <!-- Filtros -->
            <div class="filter-bar">
                <form method="GET" class="filter-grid">
                    <div>
                        <label class="form-label" style="margin-bottom: 8px;">Buscar</label>
                        <input type="text" name="search" class="form-control" 
                               placeholder="Número de orden, cliente, email..." 
                               value="<?php echo e($search); ?>">
                    </div>
                    
                    <div>
                        <label class="form-label" style="margin-bottom: 8px;">Estado</label>
                        <select name="status" class="form-control">
                            <option value="">Todos los estados</option>
                            <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pendiente</option>
                            <option value="processing" <?php echo $status === 'processing' ? 'selected' : ''; ?>>Procesando</option>
                            <option value="shipped" <?php echo $status === 'shipped' ? 'selected' : ''; ?>>Enviado</option>
                            <option value="delivered" <?php echo $status === 'delivered' ? 'selected' : ''; ?>>Entregado</option>
                            <option value="cancelled" <?php echo $status === 'cancelled' ? 'selected' : ''; ?>>Cancelado</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="form-label" style="margin-bottom: 8px;">Desde</label>
                        <input type="date" name="date_from" class="form-control" 
                               value="<?php echo e($dateFrom); ?>">
                    </div>
                    
                    <div>
                        <label class="form-label" style="margin-bottom: 8px;">Hasta</label>
                        <input type="date" name="date_to" class="form-control" 
                               value="<?php echo e($dateTo); ?>">
                    </div>
                    
                    <div style="display: flex; gap: 8px;">
                        <button type="submit" class="btn-admin btn-admin-primary">Filtrar</button>
                        <a href="orders.php" class="btn-admin btn-admin-secondary">Limpiar</a>
                    </div>
                </form>
            </div>
            
            <!-- Tabla de pedidos -->
            <div class="admin-card">
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Nº Orden</th>
                                <th>Cliente</th>
                                <th>Fecha</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($orders)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 60px 20px; color: #9ca3af;">
                                    <div style="font-size: 48px; margin-bottom: 12px;">📦</div>
                                    <p style="font-weight: 600; margin-bottom: 4px;">No hay pedidos</p>
                                    <p style="font-size: 14px;">Los pedidos aparecerán aquí cuando los clientes compren en tu tienda</p>
                                </td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td>
                                        <a href="order-details.php?id=<?php echo $order['id']; ?>" 
                                           style="font-weight: 600; color: #6b46c1; text-decoration: none;">
                                            #<?php echo e($order['order_number']); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <div>
                                            <p style="font-weight: 600; margin-bottom: 2px;">
                                                <?php echo e($order['customer_display_name']); ?>
                                            </p>
                                            <p style="font-size: 13px; color: #737373; margin: 0;">
                                                <?php echo e($order['customer_display_email']); ?>
                                            </p>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <p style="margin-bottom: 2px;">
                                                <?php echo date('d/m/Y', strtotime($order['created_at'])); ?>
                                            </p>
                                            <p style="font-size: 13px; color: #737373; margin: 0;">
                                                <?php echo date('H:i', strtotime($order['created_at'])); ?>
                                            </p>
                                        </div>
                                    </td>
                                    <td>
                                        <span style="color: #737373;">
                                            <?php echo $order['items_count']; ?> 
                                            <?php echo $order['items_count'] == 1 ? 'item' : 'items'; ?>
                                        </span>
                                    </td>
                                    <td style="font-weight: 600;">
                                        <?php echo formatPrice($order['total']); ?>
                                    </td>
                                    <td>
                                        <?php echo getStatusBadge($order['status']); ?>
                                    </td>
                                    <td>
                                        <a href="order-details.php?id=<?php echo $order['id']; ?>" 
                                           class="btn-admin btn-admin-small btn-admin-primary">
                                            Ver Detalles
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
