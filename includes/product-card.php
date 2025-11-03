<!-- Product Card Component - Estilo Rosa Monkey con Hover Effect -->
<div class="product-card">
    <div class="product-card-image-wrapper">
        <a href="<?php echo SITE_URL; ?>/product.php?slug=<?php echo e($product['slug']); ?>" class="product-image-link">
            <?php if (!empty($product['primary_image'])): ?>
                <img src="<?php echo SITE_URL; ?>/<?php echo e($product['primary_image']); ?>" 
                     alt="<?php echo e($product['name']); ?>"
                     class="product-image primary"
                     loading="lazy">
                <!-- Segunda imagen para hover effect -->
                <?php if (!empty($product['secondary_image'])): ?>
                    <img src="<?php echo SITE_URL; ?>/<?php echo e($product['secondary_image']); ?>" 
                         alt="<?php echo e($product['name']); ?>"
                         class="product-image secondary"
                         loading="lazy">
                <?php endif; ?>
            <?php else: ?>
                <img src="<?php echo ASSETS_URL; ?>/images/placeholder.jpg" 
                     alt="<?php echo e($product['name']); ?>"
                     class="product-image primary"
                     loading="lazy">
            <?php endif; ?>
        </a>
        
        <!-- Badges -->
        <div class="product-badges">
            <?php if ($product['is_new']): ?>
                <span class="product-badge badge-new">NUEVO</span>
            <?php endif; ?>
            
            <?php if (!empty($product['compare_price']) && $product['compare_price'] > $product['price']): 
                $discount = round((($product['compare_price'] - $product['price']) / $product['compare_price']) * 100);
            ?>
                <span class="product-badge badge-sale">-<?php echo $discount; ?>%</span>
            <?php endif; ?>
            
            <?php if ($product['stock'] > 0 && $product['stock'] <= $product['min_stock']): ?>
                <span class="product-badge badge-stock">¡Últimas unidades!</span>
            <?php endif; ?>
        </div>
        
        <!-- Quick View (opcional) -->
        <button class="product-quick-view" title="Vista rápida">
            👁️
        </button>
    </div>
    
    <div class="product-card-content">
        <p class="product-category">
            <?php echo e($product['category_name']); ?>
        </p>
        
        <h3 class="product-title">
            <a href="<?php echo SITE_URL; ?>/product.php?slug=<?php echo e($product['slug']); ?>">
                <?php echo e($product['name']); ?>
            </a>
        </h3>
        
        <div class="product-price-wrapper">
            <?php if (!empty($product['compare_price']) && $product['compare_price'] > $product['price']): ?>
                <div class="product-price-group">
                    <span class="price-current sale"><?php echo formatPrice($product['price']); ?></span>
                    <span class="price-compare"><?php echo formatPrice($product['compare_price']); ?></span>
                </div>
                <p class="product-savings">
                    Ahorrás <?php echo formatPrice($product['compare_price'] - $product['price']); ?>
                </p>
            <?php else: ?>
                <span class="price-current"><?php echo formatPrice($product['price']); ?></span>
            <?php endif; ?>
        </div>
        
        <?php if ($product['stock'] > 0): ?>
            <button class="btn btn-primary btn-full btn-add-to-cart add-to-cart" 
                    data-product-id="<?php echo $product['id']; ?>"
                    data-quantity="1">
                🛒 Agregar al Carrito
            </button>
        <?php else: ?>
            <button class="btn btn-outline btn-full" disabled>
                Sin Stock
            </button>
        <?php endif; ?>
    </div>
</div>

<style>
/* Product Card Animations */
.product-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.product-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

/* Image Hover Effect */
.product-image-link {
    position: relative;
    display: block;
    overflow: hidden;
}

.product-image.secondary {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    opacity: 0;
    transition: opacity 0.4s ease;
}

.product-card:hover .product-image.secondary {
    opacity: 1;
}

/* Quick View Button */
.product-quick-view {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) scale(0);
    background: white;
    color: var(--color-black);
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: var(--radius-full);
    font-weight: 600;
    cursor: pointer;
    z-index: 5;
    box-shadow: var(--shadow-lg);
    transition: transform 0.3s ease;
}

.product-card:hover .product-quick-view {
    transform: translate(-50%, -50%) scale(1);
}

/* Button Animation */
.btn-add-to-cart {
    transition: all 0.3s ease;
}

.btn-add-to-cart:hover {
    transform: scale(1.02);
}

.btn-success-animation {
    background-color: #16a34a !important;
    animation: successPulse 0.4s ease-in-out;
}

@keyframes successPulse {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.05);
    }
}

/* Savings highlight */
.product-savings {
    color: var(--color-success);
    font-size: var(--font-size-sm);
    font-weight: 600;
    margin-top: 0.25rem;
}
</style>
