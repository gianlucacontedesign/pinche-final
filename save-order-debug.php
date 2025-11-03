<?php
session_start();

// CONFIGURACIÓN DE DEBUG
define('DEBUG_MODE', true);
define('DEBUG_LOG_FILE', 'save-order-debug.log');

function debug_log($message, $data = null) {
    if (!DEBUG_MODE) return;
    
    $log_entry = date('Y-m-d H:i:s') . " - " . $message;
    if ($data !== null) {
        $log_entry .= " - Data: " . json_encode($data);
    }
    $log_entry .= PHP_EOL;
    
    file_put_contents(DEBUG_LOG_FILE, $log_entry, FILE_APPEND | LOCK_EX);
}

// Incluir funciones del carrito
require_once 'cart-ajax-FINAL-SOLUCION.php';

debug_log("=== SAVE-ORDER DEBUG START ===");
debug_log("Session ID: " . session_id());
debug_log("Session data keys: " . json_encode(array_keys($_SESSION)));

// Verificar si hay datos POST
if (empty($_POST)) {
    debug_log("ERROR: No POST data received");
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'No se recibieron datos',
        'debug' => 'No POST data'
    ]);
    exit;
}

debug_log("POST data received: " . json_encode($_POST));

try {
    // Obtener datos del cliente
    $customer_data = [
        'first_name' => $_POST['first_name'] ?? '',
        'last_name' => $_POST['last_name'] ?? '',
        'email' => $_POST['email'] ?? '',
        'phone' => $_POST['phone'] ?? ''
    ];
    
    debug_log("Customer data: " . json_encode($customer_data));
    
    // Obtener datos de envío
    $shipping_data = [
        'address' => $_POST['shipping_address'] ?? '',
        'city' => $_POST['shipping_city'] ?? '',
        'province' => $_POST['shipping_province'] ?? '',
        'postal_code' => $_POST['shipping_postal_code'] ?? ''
    ];
    
    debug_log("Shipping data: " . json_encode($shipping_data));
    
    // Obtener datos de pago
    $payment_data = [
        'method' => $_POST['payment_method'] ?? 'cash',
        'notes' => $_POST['payment_notes'] ?? ''
    ];
    
    debug_log("Payment data: " . json_encode($payment_data));
    
    // Obtener carrito desde POST o sesión
    $cart_items = [];
    if (isset($_POST['cart_items'])) {
        $cart_items = json_decode($_POST['cart_items'], true);
        debug_log("Cart from POST: " . json_encode($cart_items));
    } else {
        // Fallback: obtener desde sesión
        $cart_session = get_cart();
        $cart_items = $cart_session['items'];
        debug_log("Cart from session: " . json_encode($cart_items));
    }
    
    if (empty($cart_items)) {
        throw new Exception('El carrito está vacío');
    }
    
    // Calcular total
    $cart_total = 0;
    foreach ($cart_items as $item) {
        $cart_total += $item['price'] * $item['quantity'];
    }
    
    debug_log("Cart total calculated: " . $cart_total);
    debug_log("Cart items count: " . count($cart_items));
    
    // Validar datos obligatorios
    if (empty($customer_data['first_name'])) {
        throw new Exception('Nombre del cliente es obligatorio');
    }
    
    if (empty($customer_data['email'])) {
        throw new Exception('Email del cliente es obligatorio');
    }
    
    if (!filter_var($customer_data['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Email del cliente no válido');
    }
    
    debug_log("All validations passed");
    
    // Función para generar ID único de pedido
    function generate_order_id() {
        return 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    }
    
    // Función para guardar pedido en archivo JSON
    function save_order_to_file($order_data) {
        $orders_file = __DIR__ . '/orders.json';
        debug_log("Saving to orders file: " . $orders_file);
        
        $orders = [];
        
        // Cargar pedidos existentes
        if (file_exists($orders_file)) {
            $content = file_get_contents($orders_file);
            debug_log("Orders file content length: " . strlen($content));
            
            if (!empty($content)) {
                $orders = json_decode($content, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    debug_log("JSON decode error: " . json_last_error_msg());
                    $orders = [];
                } else {
                    debug_log("Loaded existing orders: " . count($orders));
                }
            }
        } else {
            debug_log("Orders file does not exist, creating new");
        }
        
        // Agregar nuevo pedido
        $order_id = generate_order_id();
        $order_data['order_id'] = $order_id;
        $order_data['status'] = 'confirmado';
        $order_data['created_at'] = date('Y-m-d H:i:s');
        
        $orders[$order_id] = $order_data;
        
        // Guardar archivo
        $json_content = json_encode($orders, JSON_PRETTY_PRINT);
        if ($json_content === false) {
            debug_log("JSON encode error: " . json_last_error_msg());
            throw new Exception('Error codificando pedido');
        }
        
        $result = file_put_contents($orders_file, $json_content);
        if ($result === false) {
            debug_log("Failed to write orders file");
            throw new Exception('Error guardando archivo de pedidos');
        }
        
        debug_log("Order saved successfully to file: " . $order_id);
        debug_log("File size after write: " . filesize($orders_file));
        
        return $order_id;
    }
    
    // Preparar datos del pedido
    $order_data = [
        'customer' => $customer_data,
        'shipping' => $shipping_data,
        'payment' => $payment_data,
        'cart' => $cart_items,
        'total' => $cart_total
    ];
    
    debug_log("Order data prepared: " . json_encode($order_data));
    
    // Guardar pedido
    $order_id = save_order_to_file($order_data);
    
    if (!$order_id) {
        debug_log("Order save failed");
        throw new Exception('Error guardando el pedido');
    }
    
    debug_log("Order save successful: " . $order_id);
    
    // Limpiar carrito después del pedido exitoso
    $_SESSION['cart'] = [];
    debug_log("Cart cleared from session");
    
    // Guardar datos del pedido en sesión para order-confirmation.php
    $_SESSION['last_order'] = $order_data;
    debug_log("Order data saved to session");
    
    // Verificar que se guardó en sesión
    if (isset($_SESSION['last_order'])) {
        debug_log("Session last_order verification: SUCCESS");
        debug_log("Session last_order cart items: " . count($_SESSION['last_order']['cart']));
    } else {
        debug_log("Session last_order verification: FAILED");
    }
    
    // Forzar escritura de sesión
    session_write_close();
    debug_log("Session write_close called");
    
    // Respuesta exitosa
    $response = [
        'success' => true,
        'message' => 'Pedido guardado exitosamente',
        'data' => [
            'order_id' => $order_id,
            'total' => $cart_total,
            'items_count' => count($cart_items)
        ],
        'timestamp' => date('Y-m-d H:i:s'),
        'debug' => [
            'session_id' => session_id(),
            'session_last_order_exists' => isset($_SESSION['last_order']),
            'cart_items_in_session' => isset($_SESSION['last_order']['cart']) ? count($_SESSION['last_order']['cart']) : 0
        ]
    ];
    
    debug_log("Response prepared: " . json_encode($response));
    echo json_encode($response);
    
} catch (Exception $e) {
    debug_log("Exception caught: " . $e->getMessage());
    debug_log("Exception trace: " . $e->getTraceAsString());
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s'),
        'debug' => [
            'exception' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ]);
}

debug_log("=== SAVE-ORDER DEBUG END ===");
?>