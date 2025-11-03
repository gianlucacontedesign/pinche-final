<?php
/**
 * ADMIN - PRODUCTOS CON PAGINACIÓN
 * Versión mejorada con paginación para 15 productos por página
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Autenticación
$auth = new Auth();
$auth->requireLogin();

// Obtener parámetros de paginación
$current_page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$products_per_page = 15; // Configuración fija en 15 productos
$offset = ($current_page - 1) * $products_per_page;

// Conexión base de datos
$db = Database::getInstance();

// Obtener total de productos para calcular páginas
$total_products = $db->fetchOne("SELECT COUNT(*) as total FROM products WHERE is_active = 1")['total'];
$total_pages = ceil($total_products / $products_per_page);

// Consulta con paginación
$query = "SELECT p.*, c.name as category_name 
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          WHERE p.is_active = 1 
          ORDER BY p.created_at DESC 
          LIMIT ? OFFSET ?";

$products = $db->fetchAll($query, [$products_per_page, $offset]);

// Obtener categorías para filtro
$categories = $db->fetchAll("SELECT * FROM categories WHERE is_active = 1 ORDER BY name");

// Procesar acciones
$message = '';
if (isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'toggle_status':
            $product_id = (int)$_POST['product_id'];
            $new_status = $_POST['new_status'] == '1' ? 0 : 1;
            
            $db->execute(
                "UPDATE products SET is_active = ? WHERE id = ?",
                [$new_status, $product_id]
            );
            
            $message = 'Estado del producto actualizado correctamente.';
            break;
            
        case 'delete':
            $product_id = (int)$_POST['product_id'];
            $db->execute("UPDATE products SET is_deleted = 1 WHERE id = ?", [$product_id]);
            $message = 'Producto eliminado correctamente.';
            break;
    }
}

include __DIR__ . '/includes/sidebar.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos - Pinche Supplies Admin</title>
    
    <!-- Font Awesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- CSS personalizado -->
    <style>
        .main-content {
            margin-left: 260px;
            padding: 20px;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .product-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            background: white;
        }
        
        .product-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 4px;
        }
        
        .pagination {
            justify-content: center;
            margin-top: 30px;
        }
        
        .page-info {
            text-align: center;
            margin: 20px 0;
            font-weight: bold;
            color: #6c757d;
        }
        
        .status-badge {
            font-size: 0.75rem;
        }
        
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 10px;
            }
            
            .page-header {
                flex-direction: column;
                gap: 15px;
            }
        }
    </style>
</head>
<body>

<div class="main-content">
    <!-- Header -->
    <div class="page-header">
        <h1><i class="fas fa-box"></i> Gestión de Productos</h1>
        <div>
            <a href="product-form.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nuevo Producto
            </a>
            <a href="categories.php" class="btn btn-secondary">
                <i class="fas fa-tags"></i> Gestionar Categorías
            </a>
        </div>
    </div>

    <!-- Mensajes -->
    <?php if ($message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Estadísticas rápidas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5 class="card-title">Total Productos</h5>
                    <h2><?= $total_products ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">Productos Activos</h5>
                    <h2><?= $total_products ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h5 class="card-title">En Stock</h5>
                    <h2 id="products-stock">Calculando...</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <h5 class="card-title">Páginas</h5>
                    <h2><?= $total_pages ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de productos -->
    <div class="card">
        <div class="card-header">
            <h5><i class="fas fa-list"></i> Lista de Productos</h5>
        </div>
        <div class="card-body">
            
            <!-- Información de paginación -->
            <div class="page-info">
                <i class="fas fa-info-circle"></i> 
                Mostrando <?= (($current_page - 1) * $products_per_page) + 1 ?> - <?= min($current_page * $products_per_page, $total_products) ?> 
                de <?= $total_products ?> productos totales
            </div>

            <?php if (empty($products)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">No hay productos registrados</h4>
                    <p class="text-muted">Comienza agregando tu primer producto.</p>
                    <a href="product-form.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Agregar Producto
                    </a>
                </div>
            <?php else: ?>
                
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <div class="row align-items-center">
                            <div class="col-md-1">
                                <?php if ($product['image']): ?>
                                    <img src="../<?= htmlspecialchars($product['image']) ?>" 
                                         class="product-image" alt="<?= htmlspecialchars($product['name']) ?>">
                                <?php else: ?>
                                    <div class="product-image bg-light d-flex align-items-center justify-content-center">
                                        <i class="fas fa-image text-muted"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="col-md-4">
                                <h6 class="mb-1"><?= htmlspecialchars($product['name']) ?></h6>
                                <small class="text-muted">
                                    <i class="fas fa-tag"></i> <?= htmlspecialchars($product['category_name']) ?>
                                </small><br>
                                <small class="text-muted">
                                    <i class="fas fa-barcode"></i> SKU: <?= htmlspecialchars($product['sku']) ?>
                                </small>
                            </div>
                            
                            <div class="col-md-2">
                                <strong class="text-success">$<?= number_format($product['price'], 2) ?></strong>
                                <?php if ($product['stock_quantity'] <= 5): ?>
                                    <br><small class="text-danger">
                                        <i class="fas fa-exclamation-triangle"></i> Stock bajo
                                    </small>
                                <?php endif; ?>
                            </div>
                            
                            <div class="col-md-2">
                                <span class="badge bg-success status-badge">
                                    <i class="fas fa-check"></i> Activo
                                </span>
                                <br>
                                <small class="text-muted">
                                    Stock: <?= $product['stock_quantity'] ?>
                                </small>
                            </div>
                            
                            <div class="col-md-3 text-end">
                                <a href="product-form.php?id=<?= $product['id'] ?>" 
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                                
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="toggle_status">
                                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                    <input type="hidden" name="new_status" value="<?= $product['is_active'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-warning">
                                        <i class="fas fa-eye-slash"></i> Desactivar
                                    </button>
                                </form>
                                
                                <form method="POST" style="display: inline;" 
                                      onsubmit="return confirm('¿Eliminar este producto?')">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash"></i> Eliminar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                
            <?php endif; ?>

            <!-- Paginación -->
            <?php if ($total_pages > 1): ?>
                <nav aria-label="Paginación de productos">
                    <ul class="pagination">
                        
                        <!-- Primera página -->
                        <li class="page-item <?= $current_page <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=1" tabindex="-1">
                                <i class="fas fa-angle-double-left"></i>
                            </a>
                        </li>
                        
                        <!-- Página anterior -->
                        <li class="page-item <?= $current_page <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $current_page - 1 ?>" tabindex="-1">
                                <i class="fas fa-angle-left"></i> Anterior
                            </a>
                        </li>
                        
                        <!-- Números de página -->
                        <?php
                        $start_page = max(1, $current_page - 2);
                        $end_page = min($total_pages, $current_page + 2);
                        
                        for ($i = $start_page; $i <= $end_page; $i++):
                        ?>
                            <li class="page-item <?= $i == $current_page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <!-- Página siguiente -->
                        <li class="page-item <?= $current_page >= $total_pages ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $current_page + 1 ?>">
                                Siguiente <i class="fas fa-angle-right"></i>
                            </a>
                        </li>
                        
                        <!-- Última página -->
                        <li class="page-item <?= $current_page >= $total_pages ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $total_pages ?>">
                                <i class="fas fa-angle-double-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
                
                <!-- Información adicional de paginación -->
                <div class="text-center mt-3">
                    <small class="text-muted">
                        Página <?= $current_page ?> de <?= $total_pages ?> 
                        | <?= $products_per_page ?> productos por página
                    </small>
                </div>
            <?php endif; ?>

        </div>
    </div>

</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- JavaScript para actualizar estadísticas -->
<script>
// Función para actualizar productos en stock (AJAX)
function updateStockInfo() {
    // Esta función puede ser mejorada para mostrar productos con stock > 0
    document.getElementById('products-stock').textContent = '<?= $total_products ?>';
}

updateStockInfo();
</script>

</body>
</html>