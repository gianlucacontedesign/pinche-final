<?php
require_once __DIR__ . '/../config/config.php';
$auth = new Auth();
$auth->requireLogin();

$productModel = new Product();

// Eliminar producto
if (isset($_GET['delete'])) {
    $result = $productModel->delete($_GET['delete']);
    setFlashMessage($result['message'], $result['success'] ? 'success' : 'error');
    header('Location: products.php');
    exit;
}

$products = $productModel->getAll(['limit' => 50]);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos - Panel de Administración</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?php echo ADMIN_URL; ?>/assets/css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'includes/admin-header.php'; ?>
    <div class="admin-layout">
        <?php include 'includes/admin-sidebar.php'; ?>
        <main class="admin-content">
            <?php if (hasFlashMessage()): $flash = getFlashMessage(); ?>
            <div class="alert alert-<?php echo $flash['type']; ?>" style="margin-bottom: 1.5rem;">
                <?php echo e($flash['message']); ?>
            </div>
            <?php endif; ?>
            
            <div class="admin-header-page" style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h1>Productos</h1>
                    <p style="color: #737373;">Gestiona el catálogo de productos</p>
                </div>
                <a href="products-edit.php" class="btn-admin btn-admin-primary">+ Nuevo Producto</a>
            </div>
            
            <div class="admin-card">
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Imagen</th>
                                <th>Producto</th>
                                <th>SKU</th>
                                <th>Categoría</th>
                                <th>Precio</th>
                                <th>Stock</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                            <tr>
                                <td>
                                    <?php if ($product['primary_image']): ?>
                                    <img src="<?php echo SITE_URL . '/' . e($product['primary_image']); ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 0.375rem;">
                                    <?php else: ?>
                                    <div style="width: 50px; height: 50px; background: #f5f5f5; border-radius: 0.375rem;"></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="max-width: 300px;">
                                        <p style="font-weight: 600;"><?php echo e($product['name']); ?></p>
                                        <?php if ($product['short_description']): ?>
                                        <p style="font-size: 0.875rem; color: #737373;"><?php echo e(substr($product['short_description'], 0, 50)); ?>...</p>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td><?php echo e($product['sku']); ?></td>
                                <td><?php echo e($product['category_name']); ?></td>
                                <td><?php echo formatPrice($product['price']); ?></td>
                                <td>
                                    <?php if ($product['stock'] <= $product['min_stock']): ?>
                                    <span class="badge badge-warning"><?php echo $product['stock']; ?></span>
                                    <?php else: ?>
                                    <?php echo $product['stock']; ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge <?php echo $product['is_active'] ? 'badge-success' : 'badge-danger'; ?>">
                                        <?php echo $product['is_active'] ? 'Activo' : 'Inactivo'; ?>
                                    </span>
                                </td>
                                <td class="table-actions">
                                    <a href="products-edit.php?id=<?php echo $product['id']; ?>" class="action-btn action-btn-edit">Editar</a>
                                    <a href="?delete=<?php echo $product['id']; ?>" class="action-btn action-btn-delete" onclick="return confirm('¿Eliminar este producto? Esta acción no se puede deshacer.')">Eliminar</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
