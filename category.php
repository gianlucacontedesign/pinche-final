<?php
require_once __DIR__ . '/config/config.php';

$categoryModel = new Category();
$productModel = new Product();

// Obtener categoría si se especifica
$category = null;
if (isset($_GET['slug'])) {
    $category = $categoryModel->getBySlug($_GET['slug']);
}

// Parámetros de búsqueda y filtros
$params = [
    'active_only' => true,
    'order_by' => $_GET['order'] ?? 'p.created_at',
    'order_dir' => 'DESC'
];

if ($category) {
    $params['category_id'] = $category['id'];
}

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $params['search'] = $_GET['search'];
}

// Paginación
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$itemsPerPage = PRODUCTS_PER_PAGE;
$params['limit'] = $itemsPerPage;
$params['offset'] = ($currentPage - 1) * $itemsPerPage;

// Obtener productos
$products = $productModel->getAll($params);
$totalProducts = $productModel->getCount($params);
$totalPages = ceil($totalProducts / $itemsPerPage);

// Obtener todas las categorías para el sidebar
$allCategories = $categoryModel->getAll(true);

$pageTitle = $category ? $category['name'] . ' | ' . SITE_NAME : 'Productos | ' . SITE_NAME;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($pageTitle); ?></title>
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <section class="section">
        <div class="container">
            <?php if ($category): ?>
            <h1 class="section-title"><?php echo e($category['name']); ?></h1>
            <?php if ($category['description']): ?>
            <p style="color: #737373; font-size: 1.125rem; margin-bottom: 2rem;">
                <?php echo e($category['description']); ?>
            </p>
            <?php endif; ?>
            <?php else: ?>
            <h1 class="section-title">Todos los Productos</h1>
            <?php endif; ?>
            
            <div style="display: grid; grid-template-columns: 250px 1fr; gap: 2rem;">
                <!-- Sidebar con categorías -->
                <aside>
                    <div style="background: white; border: 1px solid #d4d4d4; border-radius: 0.75rem; padding: 1.5rem;">
                        <h3 style="font-weight: 700; margin-bottom: 1rem;">Categorías</h3>
                        <ul style="list-style: none;">
                            <li style="margin-bottom: 0.5rem;">
                                <a href="<?php echo SITE_URL; ?>/category.php" 
                                   style="display: block; padding: 0.5rem; border-radius: 0.375rem; color: <?php echo !$category ? '#6b46c1' : '#404040'; ?>; font-weight: <?php echo !$category ? '600' : '400'; ?>;">
                                    Todas
                                </a>
                            </li>
                            <?php foreach ($allCategories as $cat): ?>
                            <li style="margin-bottom: 0.5rem;">
                                <a href="<?php echo SITE_URL; ?>/category.php?slug=<?php echo e($cat['slug']); ?>" 
                                   style="display: block; padding: 0.5rem; border-radius: 0.375rem; color: <?php echo $category && $category['id'] == $cat['id'] ? '#6b46c1' : '#404040'; ?>; font-weight: <?php echo $category && $category['id'] == $cat['id'] ? '600' : '400'; ?>;">
                                    <?php echo e($cat['name']); ?>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    
                    <!-- Filtros -->
                    <div style="background: white; border: 1px solid #d4d4d4; border-radius: 0.75rem; padding: 1.5rem; margin-top: 1rem;">
                        <h3 style="font-weight: 700; margin-bottom: 1rem;">Ordenar por</h3>
                        <form method="GET" action="">
                            <?php if (isset($_GET['slug'])): ?>
                            <input type="hidden" name="slug" value="<?php echo e($_GET['slug']); ?>">
                            <?php endif; ?>
                            
                            <select name="order" onchange="this.form.submit()" style="width: 100%; padding: 0.75rem; border: 1px solid #d4d4d4; border-radius: 0.5rem;">
                                <option value="p.created_at" <?php echo !isset($_GET['order']) || $_GET['order'] == 'p.created_at' ? 'selected' : ''; ?>>Más recientes</option>
                                <option value="p.name" <?php echo isset($_GET['order']) && $_GET['order'] == 'p.name' ? 'selected' : ''; ?>>Nombre A-Z</option>
                                <option value="p.price" <?php echo isset($_GET['order']) && $_GET['order'] == 'p.price' ? 'selected' : ''; ?>>Precio: Menor a Mayor</option>
                                <option value="p.views" <?php echo isset($_GET['order']) && $_GET['order'] == 'p.views' ? 'selected' : ''; ?>>Más populares</option>
                            </select>
                        </form>
                    </div>
                </aside>
                
                <!-- Productos -->
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                        <p style="color: #737373;">
                            <?php echo $totalProducts; ?> producto<?php echo $totalProducts != 1 ? 's' : ''; ?> encontrado<?php echo $totalProducts != 1 ? 's' : ''; ?>
                        </p>
                    </div>
                    
                    <?php if (empty($products)): ?>
                        <div style="text-align: center; padding: 4rem; background: white; border: 1px solid #d4d4d4; border-radius: 0.75rem;">
                            <p style="font-size: 1.125rem; color: #737373;">
                                No se encontraron productos
                            </p>
                        </div>
                    <?php else: ?>
                        <div class="products-grid">
                            <?php foreach ($products as $product): ?>
                                <?php include 'includes/product-card.php'; ?>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- Paginación -->
                        <?php if ($totalPages > 1): ?>
                        <div style="margin-top: 3rem; display: flex; justify-content: center; gap: 0.5rem;">
                            <?php if ($currentPage > 1): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $currentPage - 1])); ?>" 
                               class="btn btn-outline">
                                ← Anterior
                            </a>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <?php if ($i == $currentPage): ?>
                                <span style="padding: 0.75rem 1rem; background: #6b46c1; color: white; border-radius: 0.5rem; font-weight: 600;">
                                    <?php echo $i; ?>
                                </span>
                                <?php elseif ($i == 1 || $i == $totalPages || abs($i - $currentPage) <= 2): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" 
                                   style="padding: 0.75rem 1rem; background: white; border: 1px solid #d4d4d4; border-radius: 0.5rem; color: #404040;">
                                    <?php echo $i; ?>
                                </a>
                                <?php elseif (abs($i - $currentPage) == 3): ?>
                                <span style="padding: 0.75rem;">...</span>
                                <?php endif; ?>
                            <?php endfor; ?>
                            
                            <?php if ($currentPage < $totalPages): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $currentPage + 1])); ?>" 
                               class="btn btn-outline">
                                Siguiente →
                            </a>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="<?php echo ASSETS_URL; ?>/js/main.js"></script>
</body>
</html>
