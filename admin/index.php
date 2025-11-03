<?php
require_once __DIR__ . '/../config/config.php';

$auth = new Auth();
$auth->requireLogin();

$db = Database::getInstance();

// Obtener estadísticas
$totalProducts = $db->fetchOne("SELECT COUNT(*) as count FROM products WHERE is_active = 1")['count'];
$totalCategories = $db->fetchOne("SELECT COUNT(*) as count FROM categories WHERE is_active = 1")['count'];
$lowStockProducts = $db->fetchAll("SELECT * FROM low_stock_products LIMIT 10");

// Productos más vendidos (simulado - agregar lógica real cuando haya pedidos)
$topProducts = $db->fetchAll("
    SELECT p.*, c.name as category_name,
    (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
    FROM products p
    INNER JOIN categories c ON p.category_id = c.id
    WHERE p.is_active = 1
    ORDER BY p.views DESC
    LIMIT 5
");

// Ventas totales (simulado - agregar lógica real)
$totalSales = $db->fetchOne("SELECT COALESCE(SUM(total), 0) as total FROM orders WHERE status NOT IN ('cancelled')")['total'] ?? 0;
$totalOrders = $db->fetchOne("SELECT COUNT(*) as count FROM orders WHERE status NOT IN ('cancelled')")['count'] ?? 0;

// Valor total del inventario
$inventoryValue = $db->fetchOne("SELECT SUM(price * stock) as value FROM products WHERE is_active = 1")['value'] ?? 0;

$pageTitle = 'Dashboard - Panel de Administración';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($pageTitle); ?></title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?php echo ADMIN_URL; ?>/assets/css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'includes/admin-header.php'; ?>
    
    <div class="admin-layout">
        <?php include 'includes/admin-sidebar.php'; ?>
        
        <main class="admin-content">
            <div class="admin-header-page">
                <h1>Dashboard</h1>
                <p style="color: #737373;">Bienvenido, <?php echo e($auth->getCurrentUser()['full_name']); ?></p>
            </div>
            
            <!-- Métricas principales -->
            <div class="metrics-grid">
                <div class="metric-card">
                    <div class="metric-icon" style="background: #ede9fe;">📦</div>
                    <div>
                        <p class="metric-label">Total Productos</p>
                        <p class="metric-value"><?php echo number_format($totalProducts); ?></p>
                    </div>
                </div>
                
                <div class="metric-card">
                    <div class="metric-icon" style="background: #dbeafe;">📂</div>
                    <div>
                        <p class="metric-label">Categorías</p>
                        <p class="metric-value"><?php echo number_format($totalCategories); ?></p>
                    </div>
                </div>
                
                <div class="metric-card">
                    <div class="metric-icon" style="background: #dcfce7;">💰</div>
                    <div>
                        <p class="metric-label">Ventas Totales</p>
                        <p class="metric-value"><?php echo formatPrice($totalSales); ?></p>
                    </div>
                </div>
                
                <div class="metric-card">
                    <div class="metric-icon" style="background: #fef3c7;">📊</div>
                    <div>
                        <p class="metric-label">Valor Inventario</p>
                        <p class="metric-value"><?php echo formatPrice($inventoryValue); ?></p>
                    </div>
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; margin-top: 2rem;">
                <!-- Productos más vendidos -->
                <div class="admin-card">
                    <h2 class="card-title">Productos Más Visitados</h2>
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Categoría</th>
                                    <th>Precio</th>
                                    <th>Stock</th>
                                    <th>Vistas</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($topProducts as $product): ?>
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                                            <?php if ($product['primary_image']): ?>
                                            <img src="<?php echo SITE_URL . '/' . e($product['primary_image']); ?>" 
                                                 style="width: 40px; height: 40px; object-fit: cover; border-radius: 0.375rem;">
                                            <?php endif; ?>
                                            <span><?php echo e($product['name']); ?></span>
                                        </div>
                                    </td>
                                    <td><?php echo e($product['category_name']); ?></td>
                                    <td><?php echo formatPrice($product['price']); ?></td>
                                    <td><?php echo $product['stock']; ?></td>
                                    <td><?php echo $product['views']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Stock bajo -->
                <div class="admin-card">
                    <h2 class="card-title">Stock Bajo</h2>
                    <?php if (empty($lowStockProducts)): ?>
                        <p style="color: #737373; text-align: center; padding: 2rem;">
                            ✓ Todo el stock está bien
                        </p>
                    <?php else: ?>
                        <div style="max-height: 400px; overflow-y: auto;">
                            <?php foreach ($lowStockProducts as $product): ?>
                            <div style="padding: 1rem; border-bottom: 1px solid #d4d4d4;">
                                <p style="font-weight: 600; margin-bottom: 0.25rem;">
                                    <?php echo e($product['name']); ?>
                                </p>
                                <p style="font-size: 0.875rem; color: #737373; margin-bottom: 0.5rem;">
                                    <?php echo e($product['category_name']); ?>
                                </p>
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span style="background: #fee2e2; color: #991b1b; padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.75rem; font-weight: 600;">
                                        Stock: <?php echo $product['stock']; ?>
                                    </span>
                                    <a href="<?php echo ADMIN_URL; ?>/products-edit.php?id=<?php echo $product['id']; ?>" 
                                       style="color: #6b46c1; font-size: 0.875rem; font-weight: 600;">
                                        Editar →
                                    </a>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
