<?php
session_start();
require_once 'config.php';

// Función para guardar orden via API
function save_order_via_api($order_data) {
    // Llamar al endpoint de guardado
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://pinchesupplies.com.ar/save-order-db-simple.php');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($order_data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($response === false || $http_code !== 200) {
        return [
            'success' => false,
            'message' => 'Error de comunicación con el servidor'
        ];
    }
    
    $result = json_decode($response, true);
    return $result ?: ['success' => false, 'message' => 'Respuesta inválida del servidor'];
}

// 🔧 SOLUCIÓN: Obtener carrito directamente desde la sesión
$cart_items = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$cart_total = 0;

if (!empty($cart_items)) {
    foreach ($cart_items as $item) {
        $cart_total += $item['price'] * $item['quantity'];
    }
}

// 🔍 DIAGNÓSTICO - Mostrar lo que tenemos
// Remover esto después de confirmar que funciona
// Descomenta las líneas siguientes para ver qué datos del carrito tenemos:
// echo "<pre>DEBUG CART: ";
// print_r($cart_items);
// echo "\nTotal: $" . $cart_total;
// echo "</pre>";
// exit;

// Procesar formulario de checkout
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $errors = [];
    
    // Validaciones
    if (empty($_POST['first_name'])) $errors[] = "El nombre es requerido";
    if (empty($_POST['last_name'])) $errors[] = "El apellido es requerido";
    if (empty($_POST['email'])) $errors[] = "El email es requerido";
    if (empty($_POST['phone'])) $errors[] = "El teléfono es requerido";
    if (empty($_POST['address'])) $errors[] = "La dirección es requerida";
    if (empty($_POST['city'])) $errors[] = "La ciudad es requerida";
    if (empty($_POST['postal_code'])) $errors[] = "El código postal es requerido";
    
    if (empty($errors)) {
        // Procesar orden aquí
        $order_data = [
            'customer' => [
                'first_name' => $_POST['first_name'],
                'last_name' => $_POST['last_name'],
                'email' => $_POST['email'],
                'phone' => $_POST['phone']
            ],
            'shipping' => [
                'address' => $_POST['address'],
                'city' => $_POST['city'],
                'postal_code' => $_POST['postal_code'],
                'province' => $_POST['province']
            ],
            'payment' => [
                'method' => $_POST['payment_method'],
                'notes' => $_POST['payment_notes']
            ],
            'cart' => $cart_items,
            'total' => $cart_total,
            'order_date' => date('Y-m-d H:i:s')
        ];
        
        // Guardar orden usando la API
        $order_api_response = save_order_via_api($order_data);
        
        if ($order_api_response['success']) {
            // Guardar orden en sesión para mostrar confirmación
            $_SESSION['last_order'] = $order_data;
            $_SESSION['last_order']['order_id'] = $order_api_response['data']['order_id'];
            
            header('Location: order-confirmation.php');
            exit;
        } else {
            $errors[] = "Error al procesar el pedido: " . $order_api_response['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finalizar Compra - Pinche Supplies</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .checkout-container {
            background: #f8f9fa;
            min-height: 100vh;
            padding: 20px 0;
        }
        .checkout-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .cart-summary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 30px;
        }
        .form-floating > label {
            color: #6c757d;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-checkout {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 15px 40px;
            border-radius: 50px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }
        .btn-checkout:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
            color: white;
        }
        .cart-item {
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 10px;
            backdrop-filter: blur(10px);
        }
        .empty-cart {
            text-align: center;
            padding: 40px;
            color: rgba(255,255,255,0.8);
        }
        .alert-custom {
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.3);
            color: white;
            backdrop-filter: blur(10px);
        }
        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
        }
        .step {
            display: flex;
            align-items: center;
            margin: 0 20px;
        }
        .step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #667eea;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 10px;
        }
        .step.active .step-number {
            background: #28a745;
        }
        .step.completed .step-number {
            background: #6c757d;
        }
        .payment-method {
            border: 2px solid #dee2e6;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .payment-method:hover {
            border-color: #667eea;
            background: #f8f9ff;
        }
        .payment-method.selected {
            border-color: #667eea;
            background: #f8f9ff;
        }
    </style>
</head>
<body>
    <div class="checkout-container">
        <div class="container">
            <!-- Indicador de pasos -->
            <div class="step-indicator">
                <div class="step completed">
                    <div class="step-number">1</div>
                    <span>Carrito</span>
                </div>
                <div class="step active">
                    <div class="step-number">2</div>
                    <span>Checkout</span>
                </div>
                <div class="step">
                    <div class="step-number">3</div>
                    <span>Confirmación</span>
                </div>
            </div>
            
            <div class="row">
                <div class="col-lg-8">
                    <div class="checkout-card card">
                        <div class="card-header bg-white">
                            <h2 class="mb-0"><i class="fas fa-shopping-bag me-2"></i>Finalizar Compra</h2>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($errors)): ?>
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Por favor corrige los siguientes errores:</strong>
                                    <ul class="mb-0 mt-2">
                                        <?php foreach ($errors as $error): ?>
                                            <li><?= htmlspecialchars($error) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST" id="checkoutForm">
                                <!-- Información personal -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h4 class="text-primary mb-3"><i class="fas fa-user me-2"></i>Información Personal</h4>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="first_name" name="first_name" placeholder="Juan" value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>" required>
                                            <label for="first_name">Nombre *</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="last_name" name="last_name" placeholder="Pérez" value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>" required>
                                            <label for="last_name">Apellido *</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="email" class="form-control" id="email" name="email" placeholder="juan@email.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                                            <label for="email">Email *</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="tel" class="form-control" id="phone" name="phone" placeholder="+54 11 1234-5678" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" required>
                                            <label for="phone">Teléfono *</label>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Dirección de envío -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h4 class="text-primary mb-3"><i class="fas fa-truck me-2"></i>Dirección de Envío</h4>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="address" name="address" placeholder="Calle 123 456" value="<?= htmlspecialchars($_POST['address'] ?? '') ?>" required>
                                            <label for="address">Dirección *</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="city" name="city" placeholder="Buenos Aires" value="<?= htmlspecialchars($_POST['city'] ?? '') ?>" required>
                                            <label for="city">Ciudad *</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="postal_code" name="postal_code" placeholder="1000" value="<?= htmlspecialchars($_POST['postal_code'] ?? '') ?>" required>
                                            <label for="postal_code">CP *</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-floating">
                                            <select class="form-select" id="province" name="province" required>
                                                <option value="">Seleccionar</option>
                                                <option value="CABA" <?= ($_POST['province'] ?? '') == 'CABA' ? 'selected' : '' ?>>CABA</option>
                                                <option value="Buenos Aires" <?= ($_POST['province'] ?? '') == 'Buenos Aires' ? 'selected' : '' ?>>Buenos Aires</option>
                                                <option value="Córdoba" <?= ($_POST['province'] ?? '') == 'Córdoba' ? 'selected' : '' ?>>Córdoba</option>
                                                <option value="Santa Fe" <?= ($_POST['province'] ?? '') == 'Santa Fe' ? 'selected' : '' ?>>Santa Fe</option>
                                                <option value="Mendoza" <?= ($_POST['province'] ?? '') == 'Mendoza' ? 'selected' : '' ?>>Mendoza</option>
                                                <option value="Tucumán" <?= ($_POST['province'] ?? '') == 'Tucumán' ? 'selected' : '' ?>>Tucumán</option>
                                                <option value="Entre Ríos" <?= ($_POST['province'] ?? '') == 'Entre Ríos' ? 'selected' : '' ?>>Entre Ríos</option>
                                                <option value="Salta" <?= ($_POST['province'] ?? '') == 'Salta' ? 'selected' : '' ?>>Salta</option>
                                                <option value="Chaco" <?= ($_POST['province'] ?? '') == 'Chaco' ? 'selected' : '' ?>>Chaco</option>
                                                <option value="Corrientes" <?= ($_POST['province'] ?? '') == 'Corrientes' ? 'selected' : '' ?>>Corrientes</option>
                                                <option value="Santiago del Estero" <?= ($_POST['province'] ?? '') == 'Santiago del Estero' ? 'selected' : '' ?>>Santiago del Estero</option>
                                                <option value="San Juan" <?= ($_POST['province'] ?? '') == 'San Juan' ? 'selected' : '' ?>>San Juan</option>
                                                <option value="Jujuy" <?= ($_POST['province'] ?? '') == 'Jujuy' ? 'selected' : '' ?>>Jujuy</option>
                                                <option value="Río Negro" <?= ($_POST['province'] ?? '') == 'Río Negro' ? 'selected' : '' ?>>Río Negro</option>
                                                <option value="Formosa" <?= ($_POST['province'] ?? '') == 'Formosa' ? 'selected' : '' ?>>Formosa</option>
                                                <option value="Neuquén" <?= ($_POST['province'] ?? '') == 'Neuquén' ? 'selected' : '' ?>>Neuquén</option>
                                                <option value="Chubut" <?= ($_POST['province'] ?? '') == 'Chubut' ? 'selected' : '' ?>>Chubut</option>
                                                <option value="San Luis" <?= ($_POST['province'] ?? '') == 'San Luis' ? 'selected' : '' ?>>San Luis</option>
                                                <option value="Catamarca" <?= ($_POST['province'] ?? '') == 'Catamarca' ? 'selected' : '' ?>>Catamarca</option>
                                                <option value="La Rioja" <?= ($_POST['province'] ?? '') == 'La Rioja' ? 'selected' : '' ?>>La Rioja</option>
                                                <option value="La Pampa" <?= ($_POST['province'] ?? '') == 'La Pampa' ? 'selected' : '' ?>>La Pampa</option>
                                                <option value="Santa Cruz" <?= ($_POST['province'] ?? '') == 'Santa Cruz' ? 'selected' : '' ?>>Santa Cruz</option>
                                                <option value="Tierra del Fuego" <?= ($_POST['province'] ?? '') == 'Tierra del Fuego' ? 'selected' : '' ?>>Tierra del Fuego</option>
                                                <option value="Misiones" <?= ($_POST['province'] ?? '') == 'Misiones' ? 'selected' : '' ?>>Misiones</option>
                                            </select>
                                            <label for="province">Provincia *</label>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Método de pago -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h4 class="text-primary mb-3"><i class="fas fa-credit-card me-2"></i>Método de Pago</h4>
                                    </div>
                                    <div class="col-12">
                                        <div class="payment-method <?= ($_POST['payment_method'] ?? '') == 'card' ? 'selected' : '' ?>" data-method="card">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="payment_method" id="payment_card" value="card" <?= ($_POST['payment_method'] ?? '') == 'card' ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="payment_card">
                                                    <i class="fas fa-credit-card me-2"></i>Tarjeta de Crédito/Débito
                                                </label>
                                            </div>
                                        </div>
                                        
                                        <div class="payment-method <?= ($_POST['payment_method'] ?? '') == 'transfer' ? 'selected' : '' ?>" data-method="transfer">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="payment_method" id="payment_transfer" value="transfer" <?= ($_POST['payment_method'] ?? '') == 'transfer' ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="payment_transfer">
                                                    <i class="fas fa-university me-2"></i>Transferencia Bancaria
                                                </label>
                                            </div>
                                        </div>
                                        
                                        <div class="payment-method <?= ($_POST['payment_method'] ?? '') == 'cash' ? 'selected' : '' ?>" data-method="cash">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="payment_method" id="payment_cash" value="cash" <?= ($_POST['payment_method'] ?? '') == 'cash' ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="payment_cash">
                                                    <i class="fas fa-money-bill-wave me-2"></i>Efectivo
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-12">
                                        <div class="form-floating">
                                            <textarea class="form-control" id="payment_notes" name="payment_notes" placeholder="Notas adicionales sobre el pago..." style="height: 100px;"><?= htmlspecialchars($_POST['payment_notes'] ?? '') ?></textarea>
                                            <label for="payment_notes">Notas adicionales sobre el pago (opcional)</label>
                                        </div>
                                    </div>
                                </div>
                                
                                <button type="submit" class="btn btn-checkout btn-lg w-100">
                                    <i class="fas fa-lock me-2"></i>
                                    Confirmar Pedido
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Resumen del carrito -->
                <div class="col-lg-4">
                    <div class="cart-summary">
                        <h3 class="mb-4"><i class="fas fa-shopping-cart me-2"></i>Resumen del Pedido</h3>
                        
                        <?php if (empty($cart_items)): ?>
                            <div class="empty-cart">
                                <i class="fas fa-cart-arrow-down fa-3x mb-3"></i>
                                <p>Tu carrito está vacío</p>
                                <a href="index.php" class="btn btn-light btn-sm">
                                    <i class="fas fa-arrow-left me-2"></i>Volver a comprar
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="cart-items">
                                <?php foreach ($cart_items as $item): ?>
                                    <div class="cart-item">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1"><?= htmlspecialchars($item['name']) ?></h6>
                                                <small>Cantidad: <?= $item['quantity'] ?></small>
                                            </div>
                                            <div class="text-end">
                                                <strong>$<?= number_format($item['price'] * $item['quantity'], 2) ?></strong>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <hr class="my-3" style="border-color: rgba(255,255,255,0.3);">
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <h5>Total:</h5>
                                <h5 class="mb-0">$<?= number_format($cart_total, 2) ?></h5>
                            </div>
                            
                            <div class="mt-3">
                                <small class="d-block mb-2">
                                    <i class="fas fa-truck me-1"></i>
                                    Envío a todo el país
                                </small>
                                <small class="d-block">
                                    <i class="fas fa-shield-alt me-1"></i>
                                    Compra segura y protegida
                                </small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Funcionalidad para selección de métodos de pago
        document.querySelectorAll('.payment-method').forEach(method => {
            method.addEventListener('click', function() {
                document.querySelectorAll('.payment-method').forEach(m => m.classList.remove('selected'));
                this.classList.add('selected');
                const radio = this.querySelector('input[type="radio"]');
                radio.checked = true;
            });
        });
        
        // Actualizar contador del carrito
        function updateCartCount() {
            fetch('/cart-ajax.php?action=get_count')
                .then(response => response.json())
                .then(data => {
                    const cartCountElement = document.getElementById('cart-count');
                    if (cartCountElement) {
                        cartCountElement.textContent = data.data.count;
                    }
                })
                .catch(error => console.error('Error updating cart count:', error));
        }
        
        // Validación del formulario
        document.getElementById('checkoutForm').addEventListener('submit', function(e) {
            const requiredFields = this.querySelectorAll('input[required], select[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Por favor completa todos los campos requeridos');
            }
        });
        
        // Actualizar contador al cargar la página
        updateCartCount();
    </script>
</body>
</html>