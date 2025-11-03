<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demo - Agregar Productos al Carrito</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="cart-manager.js"></script>
    <style>
        .product-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        .product-image {
            height: 200px;
            background: linear-gradient(45deg, #f8f9fa, #e9ecef);
            border-radius: 15px 15px 0 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: #6c757d;
        }
        .add-to-cart-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 50px;
            padding: 10px 25px;
            color: white;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        .add-to-cart-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            color: white;
        }
        .cart-indicator {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #28a745;
            color: white;
            border-radius: 50px;
            padding: 10px 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            z-index: 1000;
            transition: all 0.3s ease;
        }
        .cart-indicator:hover {
            background: #218838;
            transform: scale(1.05);
        }
        .demo-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 0;
            margin-bottom: 40px;
        }
        .price-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: #dc3545;
            color: white;
            border-radius: 50px;
            padding: 5px 12px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        .original-price {
            text-decoration: line-through;
            color: #6c757d;
        }
        .discount-price {
            color: #28a745;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- Indicador del carrito -->
    <div class="cart-indicator" onclick="window.location.href='cart.php'">
        <i class="fas fa-shopping-cart me-2"></i>
        <span id="cart-count">0</span> productos
    </div>

    <!-- Header -->
    <div class="demo-header">
        <div class="container text-center">
            <h1><i class="fas fa-flask me-3"></i>Demo: Agregar Productos al Carrito</h1>
            <p class="lead mb-0">Prueba la funcionalidad del carrito de compras</p>
        </div>
    </div>

    <div class="container">
        <!-- Instrucciones -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="alert alert-info">
                    <h5><i class="fas fa-info-circle me-2"></i>Instrucciones</h5>
                    <p class="mb-2">Haz clic en "Agregar al Carrito" en cualquiera de los productos de abajo. Esto:</p>
                    <ul class="mb-0">
                        <li>Enviará una petición AJAX al servidor</li>
                        <li>Agregará el producto al carrito</li>
                        <li>Actualizará el contador del carrito</li>
                        <li>Mostrará una notificación de éxito</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Productos -->
        <div class="row">
            <!-- Producto 1 -->
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card product-card h-100">
                    <div class="product-image">
                        <i class="fas fa-palette"></i>
                        <div class="price-badge">OFERTA</div>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">Tintas Drakkar Colores</h5>
                        <p class="card-text">Tintas profesionales para tatuajes con colores vibrantes y duraderos.</p>
                        <div class="mt-auto">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <small class="original-price">$3.500,00</small>
                                    <br>
                                    <span class="discount-price">$2.000,00</span>
                                </div>
                            </div>
                            <button class="btn add-to-cart-btn w-100" 
                                    data-product-id="1" 
                                    data-product-name="Tintas Drakkar Colores" 
                                    data-product-price="2000">
                                <i class="fas fa-shopping-cart me-2"></i>Agregar al Carrito
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Producto 2 -->
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card product-card h-100">
                    <div class="product-image">
                        <i class="fas fa-syringe"></i>
                        <div class="price-badge">OFERTA</div>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">Cartuchos BRONC 0803rl</h5>
                        <p class="card-text">Cartuchos de alta calidad para máquinas de tatuaje. Punta 0803, configuración round liner.</p>
                        <div class="mt-auto">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <small class="original-price">$3.500,00</small>
                                    <br>
                                    <span class="discount-price">$2.000,00</span>
                                </div>
                            </div>
                            <button class="btn add-to-cart-btn w-100" 
                                    data-product-id="2" 
                                    data-product-name="Cartuchos BRONC 0803rl" 
                                    data-product-price="2000">
                                <i class="fas fa-shopping-cart me-2"></i>Agregar al Carrito
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Producto 3 -->
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card product-card h-100">
                    <div class="product-image">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">Cartuchos SPARK 1203rl</h5>
                        <p class="card-text">Cartuchos SPARK con tecnología avanzada. Punta 1203 para líneas finas y rellenos.</p>
                        <div class="mt-auto">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <span class="discount-price">$1.100,00</span>
                                </div>
                            </div>
                            <button class="btn add-to-cart-btn w-100" 
                                    data-product-id="3" 
                                    data-product-name="Cartuchos SPARK 1203rl" 
                                    data-product-price="1100">
                                <i class="fas fa-shopping-cart me-2"></i>Agregar al Carrito
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Producto 4 -->
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card product-card h-100">
                    <div class="product-image">
                        <i class="fas fa-hand-sparkles"></i>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">Guantes NITRILO Talle S</h5>
                        <p class="card-text">Guantes de nitrilo desechables para tatuadores. Resistente a pinchazos y químicos.</p>
                        <div class="mt-auto">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <span class="discount-price">$3.500,00</span>
                                </div>
                            </div>
                            <button class="btn add-to-cart-btn w-100" 
                                    data-product-id="4" 
                                    data-product-name="Guantes NITRILO Talle S" 
                                    data-product-price="3500">
                                <i class="fas fa-shopping-cart me-2"></i>Agregar al Carrito
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Producto 5 -->
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card product-card h-100">
                    <div class="product-image">
                        <i class="fas fa-paint-brush"></i>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">Agujas Liner 3rl</h5>
                        <p class="card-text">Agujas de configuración round liner para líneas precisas y definidas.</p>
                        <div class="mt-auto">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <span class="discount-price">$800,00</span>
                                </div>
                            </div>
                            <button class="btn add-to-cart-btn w-100" 
                                    data-product-id="5" 
                                    data-product-name="Agujas Liner 3rl" 
                                    data-product-price="800">
                                <i class="fas fa-shopping-cart me-2"></i>Agregar al Carrito
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Producto 6 -->
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card product-card h-100">
                    <div class="product-image">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">Film Plástico Protectivo</h5>
                        <p class="card-text">Film plástico para proteger tatuajes durante la cicatrización. 20x30 cm.</p>
                        <div class="mt-auto">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <span class="discount-price">$600,00</span>
                                </div>
                            </div>
                            <button class="btn add-to-cart-btn w-100" 
                                    data-product-id="6" 
                                    data-product-name="Film Plástico Protectivo" 
                                    data-product-price="600">
                                <i class="fas fa-shopping-cart me-2"></i>Agregar al Carrito
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botones de navegación -->
        <div class="row mt-5">
            <div class="col-12 text-center">
                <a href="cart.php" class="btn btn-outline-primary btn-lg me-3">
                    <i class="fas fa-shopping-cart me-2"></i>Ver Carrito
                </a>
                <a href="checkout.php" class="btn btn-outline-success btn-lg me-3">
                    <i class="fas fa-credit-card me-2"></i>Finalizar Compra
                </a>
                <a href="index.php" class="btn btn-outline-secondary btn-lg">
                    <i class="fas fa-home me-2"></i>Volver al Inicio
                </a>
            </div>
        </div>

        <!-- Información técnica -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="card bg-light">
                    <div class="card-header">
                        <h5><i class="fas fa-code me-2"></i>Información Técnica</h5>
                    </div>
                    <div class="card-body">
                        <h6>Funcionalidades implementadas:</h6>
                        <ul>
                            <li><strong>AJAX Cart System:</strong> Comunicación asíncrona con el servidor</li>
                            <li><strong>Session Management:</strong> Manejo de carrito mediante sesiones PHP</li>
                            <li><strong>Real-time Updates:</strong> Actualización del contador en tiempo real</li>
                            <li><strong>Notifications:</strong> Sistema de notificaciones para feedback del usuario</li>
                            <li><strong>Error Handling:</strong> Manejo de errores y validaciones</li>
                            <li><strong>Responsive Design:</strong> Compatible con dispositivos móviles</li>
                        </ul>
                        
                        <h6 class="mt-3">Endpoints utilizados:</h6>
                        <ul>
                            <li><code>GET /cart-ajax.php?action=get_count</code> - Obtener cantidad de productos</li>
                            <li><code>POST /cart-ajax.php?action=add</code> - Agregar producto</li>
                            <li><code>POST /cart-ajax.php?action=update</code> - Actualizar cantidad</li>
                            <li><code>POST /cart-ajax.php?action=remove</code> - Eliminar producto</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Inicializar contador del carrito
        document.addEventListener('DOMContentLoaded', function() {
            // Actualizar contador inicial
            if (window.cartManager) {
                window.cartManager.updateCartCount();
            }
        });
    </script>
</body>
</html>