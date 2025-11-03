<?php
/**
 * Ejemplo de Integración del Carrito
 * Pinche Supplies - Actualizado: 03 Nov 2025 - 21:44
 * 
 * Este archivo muestra cómo integrar el carrito en diferentes páginas
 */

// Incluir archivos necesarios
require_once 'config/config.php';
require_once 'classes/Cart.php';
require_once 'classes/Product.php';
require_once 'includes/functions.php';

// Inicializar carrito y productos
$cart = new Cart();
$product = new Product();

// Ejemplo 1: Página de producto individual
function mostrarProductoIndividual($productId) {
    global $product, $cart;
    
    $productData = $product->getById($productId);
    
    if (!$productData) {
        echo "<p>Producto no encontrado</p>";
        return;
    }
    
    $cartSummary = $cart->getSummary();
    ?>
    <div class="product-detail">
        <h1><?php echo e($productData['name']); ?></h1>
        
        <div class="product-images">
            <?php if (!empty($productData['primary_image'])): ?>
                <img src="<?php echo e($productData['image_url']); ?>" 
                     alt="<?php echo e($productData['name']); ?>">
            <?php endif; ?>
        </div>
        
        <div class="product-info">
            <div class="product-price">
                <?php if ($productData['has_discount']): ?>
                    <span class="original-price"><?php echo formatPrice($productData['original_price']); ?></span>
                <?php endif; ?>
                <span class="current-price"><?php echo formatPrice($productData['price']); ?></span>
            </div>
            
            <div class="product-stock">
                <span class="stock-status <?php echo $productData['stock_status']; ?>">
                    <?php echo getStockStatusText($productData['stock']); ?>
                </span>
            </div>
            
            <div class="add-to-cart-section">
                <?php if ($productData['in_stock']): ?>
                    <div class="quantity-selector">
                        <label>Cantidad:</label>
                        <input type="number" 
                               id="product-quantity" 
                               value="1" 
                               min="1" 
                               max="<?php echo $productData['stock']; ?>"
                               class="quantity-input">
                    </div>
                    
                    <button class="add-to-cart-btn btn-primary" 
                            data-product-id="<?php echo $productData['id']; ?>"
                            data-quantity="1">
                        Agregar al Carrito
                        <?php if ($cartSummary['count'] > 0): ?>
                            <span class="cart-count"><?php echo $cartSummary['count']; ?></span>
                        <?php endif; ?>
                    </button>
                <?php else: ?>
                    <button class="btn-disabled" disabled>Sin Stock</button>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="product-description">
            <h3>Descripción</h3>
            <p><?php echo e($productData['description']); ?></p>
        </div>
    </div>
    <?php
}

// Ejemplo 2: Lista de productos con botones "Agregar al Carrito"
function mostrarListaProductos($categoriaId = null) {
    global $product, $cart;
    
    $params = [
        'active_only' => true,
        'in_stock' => true,
        'limit' => 12
    ];
    
    if ($categoriaId) {
        $params['category_id'] = $categoriaId;
    }
    
    $productos = $product->getAll($params);
    $cartSummary = $cart->getSummary();
    ?>
    <div class="products-grid">
        <?php foreach ($productos as $producto): ?>
            <div class="product-card">
                <div class="product-image">
                    <?php if (!empty($producto['image_url'])): ?>
                        <img src="<?php echo e($producto['image_url']); ?>" 
                             alt="<?php echo e($producto['name']); ?>"
                             onerror="this.src='<?php echo assets_url(); ?>/images/no-image.png'">
                    <?php endif; ?>
                    
                    <?php if ($producto['is_new']): ?>
                        <span class="badge badge-new">Nuevo</span>
                    <?php endif; ?>
                    
                    <?php if ($producto['has_discount']): ?>
                        <span class="badge badge-sale">-<?php echo $producto['discount_percentage']; ?>%</span>
                    <?php endif; ?>
                </div>
                
                <div class="product-info">
                    <h3 class="product-title">
                        <a href="product.php?slug=<?php echo e($producto['slug']); ?>">
                            <?php echo e($producto['name']); ?>
                        </a>
                    </h3>
                    
                    <div class="product-price">
                        <?php if ($producto['has_discount']): ?>
                            <span class="original-price"><?php echo formatPrice($producto['original_price']); ?></span>
                        <?php endif; ?>
                        <span class="current-price"><?php echo formatPrice($producto['price']); ?></span>
                    </div>
                    
                    <div class="product-stock">
                        <?php if ($producto['stock_status'] === 'ok'): ?>
                            <span class="stock-available">En stock</span>
                        <?php elseif ($producto['stock_status'] === 'low'): ?>
                            <span class="stock-low">¡Últimas unidades!</span>
                        <?php elseif ($producto['stock_status'] === 'critical'): ?>
                            <span class="stock-critical">Solo <?php echo $producto['stock']; ?> disponibles</span>
                        <?php else: ?>
                            <span class="stock-out">Sin stock</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="product-actions">
                        <?php if ($producto['in_stock']): ?>
                            <button class="add-to-cart-btn" 
                                    data-product-id="<?php echo $producto['id']; ?>"
                                    data-quantity="1">
                                🛒 Agregar
                                <?php if ($producto['stock'] <= LOW_STOCK_THRESHOLD): ?>
                                    <span class="stock-warning">⚠️ <?php echo $producto['stock']; ?> disponibles</span>
                                <?php endif; ?>
                            </button>
                            
                            <a href="product.php?slug=<?php echo e($producto['slug']); ?>" 
                               class="view-details-btn">
                                Ver Detalles
                            </a>
                        <?php else: ?>
                            <button class="btn-disabled" disabled>Sin Stock</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Mini carrito flotante -->
    <div class="floating-cart" id="floating-cart">
        <a href="cart.php" class="cart-link">
            🛒 Carrito
            <?php if ($cartSummary['count'] > 0): ?>
                <span class="cart-count"><?php echo $cartSummary['count']; ?></span>
                <span class="cart-total"><?php echo formatPrice($cartSummary['subtotal']); ?></span>
            <?php endif; ?>
        </a>
    </div>
    <?php
}

// Ejemplo 3: Widget del carrito para sidebar
function mostrarWidgetCarrito() {
    global $cart;
    
    $cartItems = $cart->getItems();
    $cartSummary = $cart->getSummary();
    ?>
    <div class="cart-widget">
        <h3>Tu Carrito</h3>
        
        <?php if (empty($cartItems)): ?>
            <p class="empty-cart">Tu carrito está vacío</p>
            <a href="products.php" class="btn btn-primary btn-full">
                Ver Productos
            </a>
        <?php else: ?>
            <div class="cart-items-preview">
                <?php foreach (array_slice($cartItems, 0, 3) as $item): ?>
                    <div class="cart-item-preview">
                        <img src="<?php echo e($item['image'] ?: assets_url() . '/images/no-image.png'); ?>" 
                             alt="<?php echo e($item['name']); ?>"
                             class="item-image">
                        <div class="item-info">
                            <div class="item-name"><?php echo e($item['name']); ?></div>
                            <div class="item-qty">Cant: <?php echo $item['quantity']; ?></div>
                            <div class="item-price"><?php echo formatPrice($item['subtotal']); ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <?php if (count($cartItems) > 3): ?>
                    <p class="more-items">y <?php echo count($cartItems) - 3; ?> productos más...</p>
                <?php endif; ?>
            </div>
            
            <div class="cart-summary-preview">
                <div class="total-line">
                    <span>Subtotal:</span>
                    <span><?php echo formatPrice($cartSummary['subtotal']); ?></span>
                </div>
            </div>
            
            <div class="cart-actions">
                <a href="cart.php" class="btn btn-outline btn-full">
                    Ver Carrito Completo
                </a>
                <a href="checkout.php" class="btn btn-primary btn-full">
                    Finalizar Compra
                </a>
            </div>
        <?php endif; ?>
    </div>
    <?php
}

// Ejemplo 4: Página de checkout con validación del carrito
function validarCarritoParaCheckout() {
    global $cart;
    
    $validation = validateCartForCheckout();
    
    if (!$validation['valid']) {
        echo '<div class="cart-validation-error">';
        echo '<h3>⚠️ Error en el Carrito</h3>';
        echo '<p>' . e($validation['message']) . '</p>';
        
        if (!empty($validation['errors'])) {
            echo '<ul>';
            foreach ($validation['errors'] as $error) {
                echo '<li>' . e($error) . '</li>';
            }
            echo '</ul>';
        }
        
        echo '<a href="cart.php" class="btn btn-primary">Revisar Carrito</a>';
        echo '</div>';
        return false;
    }
    
    return true;
}

// Ejemplo 5: Funciones utilitarias para JavaScript
function generarScriptCarrito() {
    ?>
    <script>
    // Configuración global del carrito
    window.CartConfig = {
        ajaxUrl: '<?php echo base_url(); ?>/cart-ajax.php',
        cartUrl: '<?php echo base_url(); ?>/cart.php',
        checkoutUrl: '<?php echo base_url(); ?>/checkout.php'
    };
    
    // Función para actualizar botones de agregar al carrito
    function updateAddToCartButtons() {
        const buttons = document.querySelectorAll('.add-to-cart-btn');
        buttons.forEach(button => {
            const productId = button.dataset.productId;
            
            // Obtener stock máximo del input relacionado
            const quantityInput = document.querySelector(`[name="quantity"][data-product-id="${productId}"]`) || 
                                 document.querySelector(`#product-quantity`);
            
            if (quantityInput) {
                button.dataset.maxQuantity = quantityInput.max;
                quantityInput.addEventListener('change', function() {
                    button.dataset.quantity = this.value;
                });
            }
        });
    }
    
    // Función para mostrar notificaciones del carrito
    function showCartNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `cart-notification ${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <span class="notification-message">${message}</span>
                <button class="notification-close">&times;</button>
            </div>
        `;
        
        // Agregar al DOM
        document.body.appendChild(notification);
        
        // Mostrar con animación
        setTimeout(() => notification.classList.add('show'), 100);
        
        // Auto-remover después de 5 segundos
        setTimeout(() => removeCartNotification(notification), 5000);
        
        // Event listener para cerrar manualmente
        notification.querySelector('.notification-close').addEventListener('click', () => {
            removeCartNotification(notification);
        });
    }
    
    function removeCartNotification(notification) {
        notification.classList.remove('show');
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }
    
    // Event listeners para la página
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar botones
        updateAddToCartButtons();
        
        // Event listener para botones de cantidad
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('quantity-btn')) {
                const action = e.target.dataset.action;
                const input = e.target.parentElement.querySelector('.quantity-input');
                const button = document.querySelector('.add-to-cart-btn');
                
                let currentValue = parseInt(input.value);
                let newValue;
                
                if (action === 'increase') {
                    newValue = Math.min(currentValue + 1, parseInt(input.max));
                } else if (action === 'decrease') {
                    newValue = Math.max(currentValue - 1, parseInt(input.min));
                }
                
                input.value = newValue;
                if (button) {
                    button.dataset.quantity = newValue;
                }
            }
        });
        
        // Event listener para cambio manual de cantidad
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('quantity-input')) {
                const button = document.querySelector('.add-to-cart-btn');
                if (button) {
                    button.dataset.quantity = e.target.value;
                }
            }
        });
        
        // Event listener para botones "Agregar al Carrito"
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('add-to-cart-btn') || 
                e.target.closest('.add-to-cart-btn')) {
                
                const button = e.target.classList.contains('add-to-cart-btn') ? 
                              e.target : e.target.closest('.add-to-cart-btn');
                
                if (button.disabled) return;
                
                e.preventDefault();
                agregarAlCarrito(button);
            }
        });
    });
    
    // Función principal para agregar al carrito
    function agregarAlCarrito(button) {
        const productId = button.dataset.productId;
        const quantity = parseInt(button.dataset.quantity) || 1;
        const variantId = button.dataset.variantId || null;
        
        if (!productId) {
            showCartNotification('Error: Producto no válido', 'error');
            return;
        }
        
        // Estado de carga
        const originalText = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '⏳ Agregando...';
        button.classList.add('loading');
        
        // Preparar datos
        const formData = new FormData();
        formData.append('action', 'add_item');
        formData.append('product_id', productId);
        formData.append('quantity', quantity);
        if (variantId) {
            formData.append('variant_id', variantId);
        }
        
        // Enviar petición
        fetch(window.CartConfig.ajaxUrl, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showCartNotification(data.message, 'success');
                
                // Actualizar contador del carrito
                updateCartBadge(data.summary.count);
                
                // Actualizar botón si existe contador
                const countBadge = button.querySelector('.cart-count');
                if (countBadge) {
                    countBadge.textContent = data.summary.count;
                } else {
                    const badge = document.createElement('span');
                    badge.className = 'cart-count';
                    badge.textContent = data.summary.count;
                    button.appendChild(badge);
                }
                
                // Mostrar total si existe
                if (data.totals) {
                    showCartTotal(data.totals);
                }
            } else {
                showCartNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showCartNotification('Error agregando producto al carrito', 'error');
        })
        .finally(() => {
            // Restaurar botón
            button.disabled = false;
            button.innerHTML = originalText;
            button.classList.remove('loading');
        });
    }
    
    // Función para actualizar badge del carrito
    function updateCartBadge(count) {
        const cartLinks = document.querySelectorAll('a[href*="cart"], a[href*="carrito"]');
        cartLinks.forEach(link => {
            const existingBadge = link.querySelector('.cart-badge, .cart-count');
            
            if (count > 0) {
                if (existingBadge) {
                    existingBadge.textContent = count;
                } else {
                    const badge = document.createElement('span');
                    badge.className = 'cart-badge';
                    badge.textContent = count;
                    link.appendChild(badge);
                }
            } else {
                if (existingBadge) {
                    existingBadge.remove();
                }
            }
        });
    }
    
    // Función para mostrar total del carrito
    function showCartTotal(totals) {
        const cartTotalElement = document.getElementById('cart-total');
        if (cartTotalElement) {
            cartTotalElement.textContent = formatPrice(totals.total);
        }
    }
    
    // Función para formatear precios
    function formatPrice(price) {
        return '$' + new Intl.NumberFormat('es-AR', { 
            minimumFractionDigits: 2 
        }).format(price);
    }
    </script>
    <?php
}

// Ejemplo de uso en página
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? 'list';
    
    switch ($action) {
        case 'producto':
            $productId = (int)($_GET['id'] ?? 0);
            if ($productId > 0) {
                ?>
                <!DOCTYPE html>
                <html lang="es">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>Producto | <?php echo APP_NAME; ?></title>
                    <link rel="stylesheet" href="assets/css/cart.css">
                </head>
                <body>
                    <div class="container">
                        <?php mostrarProductoIndividual($productId); ?>
                    </div>
                    <script src="assets/js/cart.js"></script>
                    <?php generarScriptCarrito(); ?>
                </body>
                </html>
                <?php
            }
            break;
            
        case 'categoria':
            $categoriaId = (int)($_GET['id'] ?? 0);
            ?>
            <!DOCTYPE html>
            <html lang="es">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Productos | <?php echo APP_NAME; ?></title>
                <link rel="stylesheet" href="assets/css/cart.css">
            </head>
            <body>
                <div class="container">
                    <h1>Productos</h1>
                    <?php mostrarListaProductos($categoriaId); ?>
                </div>
                <script src="assets/js/cart.js"></script>
                <?php generarScriptCarrito(); ?>
            </body>
            </html>
            <?php
            break;
            
        default:
            echo "<p>Acción no válida. Usa ?action=producto&id=123 o ?action=categoria&id=456</p>";
    }
}
?>