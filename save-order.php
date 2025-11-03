<?php
session_start();

// CONFIGURACIÓN PARA COMPATIBILIDAD CON cURL JSON
header('Content-Type: application/json');

// Función para generar ID único de pedido
function generate_order_id() {
    return 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
}

// Función para guardar pedido en archivo JSON
function save_order_to_file($order_data) {
    $orders_file = __DIR__ . '/orders.json';
    $orders = [];
    
    // Cargar pedidos existentes
    if (file_exists($orders_file)) {
        $content = file_get_contents($orders_file);
        if (!empty($content)) {
            $orders = json_decode($content, true) ?: [];
        }
    }
    
    // Agregar nuevo pedido
    $order_id = generate_order_id();
    $order_data['order_id'] = $order_id;
    $order_data['status'] = 'confirmado';
    $order_data['created_at'] = date('Y-m-d H:i:s');
    
    $orders[$order_id] = $order_data;
    
    // Crear archivo si no existe
    if (!file_exists($orders_file)) {
        touch($orders_file);
        chmod($orders_file, 0666);
    }
    
    // Guardar archivo
    $result = file_put_contents($orders_file, json_encode($orders, JSON_PRETTY_PRINT));
    if ($result === false) {
        throw new Exception('Error guardando archivo de pedidos');
    }
    
    return $order_id;
}

try {
    // Obtener datos de la petición JSON
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    // Verificar que se recibieron datos
    if (!$data) {
        // Fallback: intentar con $_POST si no hay JSON
        if (!empty($_POST)) {
            $data = $_POST;
        } else {
            throw new Exception('No se recibieron datos');
        }
    }
    
    // Extraer datos del cliente
    $customer_data = $data['customer'] ?? [
        'first_name' => '',
        'last_name' => '',
        'email' => '',
        'phone' => ''
    ];
    
    // Extraer datos de envío
    $shipping_data = $data['shipping'] ?? [
        'address' => '',
        'city' => '',
        'province' => '',
        'postal_code' => ''
    ];
    
    // Extraer datos de pago
    $payment_data = $data['payment'] ?? [
        'method' => 'cash',
        'notes' => ''
    ];
    
    // Obtener carrito
    $cart_items = $data['cart'] ?? [];
    
    // Validar carrito
    if (empty($cart_items)) {
        throw new Exception('El carrito está vacío');
    }
    
    // Calcular total
    $cart_total = 0;
    foreach ($cart_items as $item) {
        $cart_total += $item['price'] * $item['quantity'];
    }
    
    // Validaciones obligatorias
    if (empty($customer_data['first_name'])) {
        throw new Exception('Nombre del cliente es obligatorio');
    }
    
    if (empty($customer_data['email'])) {
        throw new Exception('Email del cliente es obligatorio');
    }
    
    if (!filter_var($customer_data['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Email del cliente no válido');
    }
    
    // Preparar datos del pedido
    $order_data = [
        'customer' => $customer_data,
        'shipping' => $shipping_data,
        'payment' => $payment_data,
        'cart' => $cart_items,
        'total' => $cart_total,
        'order_date' => $data['order_date'] ?? date('Y-m-d H:i:s')
    ];
    
    // Guardar pedido
    $order_id = save_order_to_file($order_data);
    
    if (!$order_id) {
        throw new Exception('Error guardando el pedido');
    }
    
    // ✅ CLAVE: Guardar datos del pedido en sesión para order-confirmation.php
    $_SESSION['last_order'] = $order_data;
    
    // Limpiar carrito después del pedido exitoso
    $_SESSION['cart'] = [];
    
    // Forzar escritura de sesión
    session_write_close();
    
    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'message' => 'Pedido guardado exitosamente',
        'data' => [
            'order_id' => $order_id,
            'total' => $cart_total,
            'items_count' => count($cart_items)
        ],
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>