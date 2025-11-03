<?php
require_once __DIR__ . '/config/config.php';

if (!isset($_GET['slug'])) {
    header('Location: ' . SITE_URL);
    exit;
}

$productModel = new Product();
$cart = new Cart();

$product = $productModel->getBySlug($_GET['slug']);

if (!$product) {
    header('Location: ' . SITE_URL);
    exit;
}

// Incrementar vistas
$productModel->incrementViews($product['id']);

// Obtener imágenes
$images = $productModel->getImages($product['id']);

// Obtener variantes
$variants = $productModel->getVariants($product['id']);

// Productos relacionados
$relatedProducts = $productModel->getAll([
    'category_id' => $product['category_id'],
    'active_only' => true,
    'limit' => 4
]);

$pageTitle = $product['name'] . ' | ' . SITE_NAME;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($pageTitle); ?></title>
    <meta name="description" content="<?php echo e($product['short_description'] ?: $product['name']); ?>">
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <!-- Breadcrumbs -->
    <div class="container" style="padding-top: 2rem;">
        <?php echo generateBreadcrumbs([
            ['name' => $product['category_name'], 'url' => SITE_URL . '/category.php?slug=' . $product['category_slug']],
            ['name' => $product['name']]
        ]); ?>
    </div>
    
    <!-- Detalle del Producto -->
    <section class="section">
        <div class="container">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 4rem; margin-bottom: 4rem;">
                
                <!-- Galería de imágenes -->
                <div>
                    <div style="margin-bottom: 1rem; overflow: hidden; border-radius: 0.75rem; border: 1px solid #d4d4d4; aspect-ratio: 1/1; background: #f5f5f5;">
                        <?php 
                        $mainImage = !empty($images) ? SITE_URL . '/' . $images[0]['image_path'] : ASSETS_URL . '/images/placeholder.jpg';
                        ?>
                        <img src="<?php echo $mainImage; ?>" 
                             alt="<?php echo e($product['name']); ?>" 
                             class="product-main-image"
                             style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s;">
                    </div>
                    
                    <?php if (count($images) > 1): ?>
                    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.5rem;">
                        <?php foreach ($images as $index => $image): ?>
                        <div class="product-thumbnail <?php echo $index === 0 ? 'active' : ''; ?>" 
                             data-image="<?php echo SITE_URL . '/' . e($image['image_path']); ?>"
                             style="border-radius: 0.5rem; overflow: hidden; cursor: pointer; border: 2px solid <?php echo $index === 0 ? '#6b46c1' : '#d4d4d4'; ?>; transition: border-color 0.2s;">
                            <img src="<?php echo SITE_URL . '/' . e($image['image_path']); ?>" 
                                 alt="<?php echo e($product['name']); ?>"
                                 style="width: 100%; aspect-ratio: 1/1; object-fit: cover;">
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Información del producto -->
                <div>
                    <p style="color: #6b46c1; font-weight: 600; margin-bottom: 0.5rem;">
                        <?php echo e($product['category_name']); ?>
                    </p>
                    
                    <h1 style="font-size: 2.25rem; font-weight: 800; color: #000; margin-bottom: 1rem;">
                        <?php echo e($product['name']); ?>
                    </h1>
                    
                    <?php if ($product['sku']): ?>
                    <p style="color: #737373; font-size: 0.875rem; margin-bottom: 1.5rem;">
                        SKU: <?php echo e($product['sku']); ?>
                    </p>
                    <?php endif; ?>
                    
                    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 2rem;">
                        <span style="font-size: 2.5rem; font-weight: 700; color: #6b46c1;">
                            <?php echo formatPrice($product['price']); ?>
                        </span>
                        <?php if (!empty($product['compare_price']) && $product['compare_price'] > $product['price']): ?>
                        <span style="font-size: 1.5rem; color: #737373; text-decoration: line-through;">
                            <?php echo formatPrice($product['compare_price']); ?>
                        </span>
                        <span style="background: #dc2626; color: white; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.875rem; font-weight: 700;">
                            <?php echo round((($product['compare_price'] - $product['price']) / $product['compare_price']) * 100); ?>% OFF
                        </span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($product['short_description']): ?>
                    <p style="font-size: 1.125rem; color: #404040; margin-bottom: 2rem; line-height: 1.6;">
                        <?php echo e($product['short_description']); ?>
                    </p>
                    <?php endif; ?>
                    
                    <!-- Stock -->
                    <?php if ($product['stock'] > 0): ?>
                        <?php if ($product['stock'] <= $product['min_stock']): ?>
                        <div style="background: #fef3c7; color: #92400e; padding: 0.75rem 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem;">
                            ⚠️ ¡Solo quedan <?php echo $product['stock']; ?> unidades!
                        </div>
                        <?php else: ?>
                        <div style="color: #16a34a; font-weight: 600; margin-bottom: 1.5rem;">
                            ✓ En stock (<?php echo $product['stock']; ?> disponibles)
                        </div>
                        <?php endif; ?>
                    <?php else: ?>
                    <div style="background: #fee2e2; color: #991b1b; padding: 0.75rem 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem;">
                        ✗ Sin stock
                    </div>
                    <?php endif; ?>
                    
                    <!-- Variantes -->
                    <?php if (!empty($variants)): ?>
                    <div style="margin-bottom: 2rem;">
                        <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Seleccionar variante:</label>
                        <select id="variant-select" style="width: 100%; padding: 0.75rem; border: 2px solid #d4d4d4; border-radius: 0.5rem; font-size: 1rem;">
                            <option value="">Seleccionar</option>
                            <?php foreach ($variants as $variant): ?>
                            <option value="<?php echo $variant['id']; ?>" 
                                    data-price="<?php echo $variant['price_modifier']; ?>"
                                    <?php echo $variant['stock'] <= 0 ? 'disabled' : ''; ?>>
                                <?php echo e($variant['name'] . ': ' . $variant['value']); ?>
                                <?php if ($variant['price_modifier'] != 0): ?>
                                    (<?php echo $variant['price_modifier'] > 0 ? '+' : ''; ?><?php echo formatPrice($variant['price_modifier']); ?>)
                                <?php endif; ?>
                                <?php if ($variant['stock'] <= 0): ?>
                                    - Sin stock
                                <?php endif; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Cantidad -->
                    <div style="margin-bottom: 2rem;">
                        <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Cantidad:</label>
                        <div class="quantity-control" style="display: flex; align-items: center; gap: 0.5rem;">
                            <button class="quantity-minus" style="padding: 0.75rem 1rem; border: 2px solid #d4d4d4; background: white; border-radius: 0.5rem; font-weight: 700; font-size: 1.25rem;">−</button>
                            <input type="number" 
                                   class="quantity-input" 
                                   value="1" 
                                   min="1" 
                                   max="<?php echo $product['stock']; ?>"
                                   style="width: 80px; padding: 0.75rem; border: 2px solid #d4d4d4; border-radius: 0.5rem; text-align: center; font-size: 1rem; font-weight: 600;">
                            <button class="quantity-plus" style="padding: 0.75rem 1rem; border: 2px solid #d4d4d4; background: white; border-radius: 0.5rem; font-weight: 700; font-size: 1.25rem;">+</button>
                        </div>
                    </div>
                    
                    <!-- Botones de acción -->
                    <?php if ($product['stock'] > 0): ?>
                    <button class="btn btn-primary btn-full btn-lg add-to-cart" 
                            data-product-id="<?php echo $product['id']; ?>"
                            id="add-to-cart-btn">
                        🛒 Agregar al Carrito
                    </button>
                    <?php else: ?>
                    <button class="btn btn-outline btn-full btn-lg" disabled>
                        No disponible
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Descripción completa -->
            <?php if ($product['description']): ?>
            <div style="background: white; border: 1px solid #d4d4d4; border-radius: 0.75rem; padding: 3rem; margin-bottom: 4rem;">
                <h2 style="font-size: 1.875rem; font-weight: 800; margin-bottom: 1.5rem;">Descripción</h2>
                <div style="line-height: 1.8; color: #404040;">
                    <?php echo nl2br(e($product['description'])); ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Productos relacionados -->
            <?php if (!empty($relatedProducts)): ?>
            <div>
                <h2 style="font-size: 1.875rem; font-weight: 800; margin-bottom: 2rem;">Productos Relacionados</h2>
                <div class="products-grid">
                    <?php foreach ($relatedProducts as $relatedProduct): 
                        if ($relatedProduct['id'] != $product['id']): ?>
                        <?php $product = $relatedProduct; include 'includes/product-card.php'; ?>
                    <?php endif; endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="<?php echo ASSETS_URL; ?>/js/main.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const quantityInput = document.querySelector('.quantity-input');
        const addToCartBtn = document.getElementById('add-to-cart-btn');
        const variantSelect = document.getElementById('variant-select');
        
        if (addToCartBtn) {
            addToCartBtn.addEventListener('click', function() {
                const quantity = quantityInput ? quantityInput.value : 1;
                const variantId = variantSelect ? variantSelect.value : null;
                
                this.dataset.quantity = quantity;
                if (variantId) {
                    this.dataset.variantId = variantId;
                }
            });
        }
        
        // Actualizar thumbnails activos
        document.querySelectorAll('.product-thumbnail').forEach(thumb => {
            thumb.addEventListener('click', function() {
                document.querySelectorAll('.product-thumbnail').forEach(t => {
                    t.style.borderColor = '#d4d4d4';
                    t.classList.remove('active');
                });
                this.style.borderColor = '#6b46c1';
                this.classList.add('active');
            });
        });
    });
    </script>
</body>
</html>
