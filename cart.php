<?php
require_once __DIR__ . '/config/config.php';

$cart = new Cart();
$cartItems = $cart->getItems();
$totals = $cart->getTotal();

$pageTitle = 'Carrito de Compras | ' . SITE_NAME;
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
            <h1 class="section-title">Carrito de Compras</h1>
            
            <?php if (empty($cartItems)): ?>
                <!-- Carrito vacío -->
                <div style="text-align: center; padding: 4rem 0;">
                    <div style="font-size: 4rem; margin-bottom: 1rem;">🛒</div>
                    <h2 style="font-size: 1.5rem; font-weight: 700; color: #404040; margin-bottom: 1rem;">
                        Tu carrito está vacío
                    </h2>
                    <p style="color: #737373; margin-bottom: 2rem;">
                        Agrega productos para comenzar a comprar
                    </p>
                    <a href="<?php echo SITE_URL; ?>/category.php" class="btn btn-primary btn-lg">
                        Ver Productos
                    </a>
                </div>
            <?php else: ?>
                <!-- Tabla de productos -->
                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
                    <div>
                        <?php foreach ($cartItems as $item): ?>
                        <div style="background: white; border: 1px solid #d4d4d4; border-radius: 0.75rem; padding: 1.5rem; margin-bottom: 1rem; display: grid; grid-template-columns: 100px 1fr auto; gap: 1.5rem; align-items: center;">
                            <!-- Imagen -->
                            <div style="aspect-ratio: 1/1; overflow: hidden; border-radius: 0.5rem; background: #f5f5f5;">
                                <?php if ($item['image']): ?>
                                <img src="<?php echo SITE_URL . '/' . e($item['image']); ?>" 
                                     alt="<?php echo e($item['name']); ?>"
                                     style="width: 100%; height: 100%; object-fit: cover;">
                                <?php else: ?>
                                <img src="<?php echo ASSETS_URL; ?>/images/placeholder.jpg" 
                                     alt="<?php echo e($item['name']); ?>"
                                     style="width: 100%; height: 100%; object-fit: cover;">
                                <?php endif; ?>
                            </div>
                            
                            <!-- Información -->
                            <div>
                                <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 0.5rem;">
                                    <a href="<?php echo SITE_URL; ?>/product.php?slug=<?php echo e($item['slug']); ?>" style="color: #000;">
                                        <?php echo e($item['name']); ?>
                                    </a>
                                </h3>
                                
                                <?php if ($item['variant']): ?>
                                <p style="color: #737373; font-size: 0.875rem; margin-bottom: 0.5rem;">
                                    <?php echo e($item['variant']['name'] . ': ' . $item['variant']['value']); ?>
                                </p>
                                <?php endif; ?>
                                
                                <p style="font-size: 1.25rem; font-weight: 700; color: #6b46c1; margin-bottom: 1rem;">
                                    <?php echo formatPrice($item['price']); ?>
                                </p>
                                
                                <!-- Controles de cantidad -->
                                <div style="display: flex; align-items: center; gap: 1rem;">
                                    <div class="quantity-control" style="display: flex; align-items: center; gap: 0.25rem;">
                                        <button class="quantity-minus" style="padding: 0.5rem 0.75rem; border: 1px solid #d4d4d4; background: white; border-radius: 0.375rem; font-weight: 700;">−</button>
                                        <input type="number" 
                                               class="quantity-input" 
                                               value="<?php echo $item['quantity']; ?>" 
                                               min="1" 
                                               max="<?php echo $item['stock']; ?>"
                                               data-cart-key="<?php echo e($item['cart_key']); ?>"
                                               style="width: 60px; padding: 0.5rem; border: 1px solid #d4d4d4; border-radius: 0.375rem; text-align: center;">
                                        <button class="quantity-plus" style="padding: 0.5rem 0.75rem; border: 1px solid #d4d4d4; background: white; border-radius: 0.375rem; font-weight: 700;">+</button>
                                    </div>
                                    
                                    <button class="remove-from-cart" 
                                            data-cart-key="<?php echo e($item['cart_key']); ?>"
                                            style="color: #dc2626; font-size: 0.875rem; font-weight: 600; padding: 0.5rem; cursor: pointer;">
                                        🗑️ Eliminar
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Subtotal -->
                            <div style="text-align: right;">
                                <p style="font-size: 1.5rem; font-weight: 700; color: #000;">
                                    <?php echo formatPrice($item['subtotal']); ?>
                                </p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Resumen del pedido -->
                    <div>
                        <div style="background: #f5f5f5; border-radius: 0.75rem; padding: 2rem; position: sticky; top: 100px;">
                            <h2 style="font-size: 1.5rem; font-weight: 800; margin-bottom: 1.5rem;">Resumen del Pedido</h2>
                            
                            <div style="border-bottom: 1px solid #d4d4d4; padding-bottom: 1rem; margin-bottom: 1rem;">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                    <span>Subtotal:</span>
                                    <span style="font-weight: 600;"><?php echo formatPrice($totals['subtotal']); ?></span>
                                </div>
                                
                                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                    <span>Envío:</span>
                                    <span style="font-weight: 600;">
                                        <?php echo $totals['shipping'] > 0 ? formatPrice($totals['shipping']) : 'Gratis'; ?>
                                    </span>
                                </div>
                                
                                <?php if ($totals['tax'] > 0): ?>
                                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                    <span>IVA (<?php echo getSetting('tax_rate', 21); ?>%):</span>
                                    <span style="font-weight: 600;"><?php echo formatPrice($totals['tax']); ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div style="display: flex; justify-content: space-between; margin-bottom: 2rem;">
                                <span style="font-size: 1.25rem; font-weight: 700;">Total:</span>
                                <span style="font-size: 1.5rem; font-weight: 800; color: #6b46c1;">
                                    <?php echo formatPrice($totals['total']); ?>
                                </span>
                            </div>
                            
                            <?php
                            $freeShippingThreshold = (float)getSetting('free_shipping_threshold', 15000);
                            if ($totals['subtotal'] < $freeShippingThreshold && $totals['shipping'] > 0):
                                $remaining = $freeShippingThreshold - $totals['subtotal'];
                            ?>
                            <div style="background: #fef3c7; color: #92400e; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; font-size: 0.875rem;">
                                💡 Agregá <?php echo formatPrice($remaining); ?> más para envío gratis
                            </div>
                            <?php endif; ?>
                            
                            <a href="<?php echo SITE_URL; ?>/checkout.php" class="btn btn-primary btn-full btn-lg" style="margin-bottom: 1rem;">
                                Finalizar Compra
                            </a>
                            
                            <a href="<?php echo SITE_URL; ?>/category.php" class="btn btn-outline btn-full">
                                Seguir Comprando
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="<?php echo ASSETS_URL; ?>/js/main.js"></script>
</body>
</html>
