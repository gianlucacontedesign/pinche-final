<?php
/**
 * Guardar Pedido en Base de Datos
 * Este archivo recibe los datos del checkout y los guarda en MySQL
 */

session_start();
header('Content-Type: application/json');

// Cargar configuración y clases necesarias
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/class.database.php';
require_once __DIR__ . '/includes/class.order.php';
require_once __DIR__ . '/includes/class.product.php';

/**
 * Función para generar número de orden único
 */
function generateOrderNumber() {
    return 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
}

/**
 * Función para responder con JSON
 */
function jsonResponse($success, $message, $data = []) {
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit;
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
            throw new Exception('No se recibieron datos del pedido');
        }
    }
    
    // Validar datos del cliente
    if (empty($data['customer']['first_name'])) {
        throw new Exception('El nombre del cliente es obligatorio');
    }
    
    if (empty($data['customer']['email'])) {
        throw new Exception('El email del cliente es obligatorio');
    }
    
    if (!filter_var($data['customer']['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('El email del cliente no es válido');
    }
    
    // Validar carrito
    if (empty($data['cart']) || !is_array($data['cart'])) {
        throw new Exception('El carrito está vacío');
    }
    
    // Calcular totales
    $subtotal = 0;
    foreach ($data['cart'] as $item) {
        if (!isset($item['price']) || !isset($item['quantity'])) {
            throw new Exception('Datos de productos incompletos en el carrito');
        }
        $subtotal += floatval($item['price']) * intval($item['quantity']);
    }
    
    // Calcular envío (puedes personalizar esta lógica)
    $shippingAmount = 0;
    if ($subtotal < 5000) {
        $shippingAmount = 800; // Costo de envío estándar
    }
    
    // Calcular total
    $totalAmount = $subtotal + $shippingAmount;
    
    // Preparar datos de la orden
    $orderData = [
        'order_number' => generateOrderNumber(),
        'customer_id' => null, // NULL para clientes invitados
        'customer_email' => $data['customer']['email'],
        'customer_name' => $data['customer']['first_name'] . ' ' . $data['customer']['last_name'],
        'customer_phone' => $data['customer']['phone'] ?? '',
        'subtotal' => $subtotal,
        'tax_amount' => 0, // Puedes calcular IVA si es necesario
        'shipping_amount' => $shippingAmount,
        'discount_amount' => 0,
        'total_amount' => $totalAmount,
        'payment_method' => $data['payment']['method'] ?? 'cash',
        'payment_status' => 'pending',
        'order_status' => 'pending',
        'fulfillment_status' => 'unfulfilled',
        'shipping_address' => json_encode([
            'address' => $data['shipping']['address'] ?? '',
            'city' => $data['shipping']['city'] ?? '',
            'province' => $data['shipping']['province'] ?? '',
            'postal_code' => $data['shipping']['postal_code'] ?? ''
        ]),
        'billing_address' => json_encode([
            'address' => $data['shipping']['address'] ?? '',
            'city' => $data['shipping']['city'] ?? '',
            'province' => $data['shipping']['province'] ?? '',
            'postal_code' => $data['shipping']['postal_code'] ?? ''
        ]),
        'notes' => $data['payment']['notes'] ?? '',
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
    ];
    
    // Preparar items del pedido
    $orderItems = [];
    foreach ($data['cart'] as $item) {
        $orderItems[] = [
            'product_id' => $item['id'] ?? 0,
            'name' => $item['name'] ?? 'Producto sin nombre',
            'quantity' => intval($item['quantity']),
            'price' => floatval($item['price']),
            'subtotal' => floatval($item['price']) * intval($item['quantity']),
            'variant' => null // Puedes agregar variantes si las tienes
        ];
    }
    
    // Obtener instancia de la base de datos
    $db = Database::getInstance();
    
    // Iniciar transacción
    $db->beginTransaction();
    
    try {
        // Insertar orden en la base de datos
        $orderId = $db->insert('orders', $orderData);
        
        if (!$orderId) {
            throw new Exception('Error al crear el pedido en la base de datos');
        }
        
        // Insertar items del pedido
        foreach ($orderItems as $item) {
            $itemData = [
                'order_id' => $orderId,
                'product_id' => $item['product_id'],
                'product_name' => $item['name'],
                'variant_info' => $item['variant'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'subtotal' => $item['subtotal']
            ];
            
            $db->insert('order_items', $itemData);
            
            // Actualizar stock del producto (si tienes gestión de stock)
            // Descomenta las siguientes líneas si quieres actualizar el stock automáticamente
            /*
            if ($item['product_id'] > 0) {
                $db->execute(
                    "UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?",
                    [$item['quantity'], $item['product_id']]
                );
            }
            */
        }
        
        // Confirmar transacción
        $db->commit();
        
        // Guardar datos del pedido en sesión para la página de confirmación
        $_SESSION['last_order'] = [
            'order_id' => $orderId,
            'order_number' => $orderData['order_number'],
            'customer' => $data['customer'],
            'shipping' => $data['shipping'],
            'payment' => $data['payment'],
            'cart' => $data['cart'],
            'total' => $totalAmount,
            'order_date' => date('Y-m-d H:i:s')
        ];
        
        // Limpiar carrito después del pedido exitoso
        $_SESSION['cart'] = [];
        
        // Forzar escritura de sesión
        session_write_close();
        
        // Respuesta exitosa
        jsonResponse(true, 'Pedido guardado exitosamente', [
            'order_id' => $orderId,
            'order_number' => $orderData['order_number'],
            'total' => $totalAmount,
            'items_count' => count($orderItems)
        ]);
        
    } catch (Exception $e) {
        // Revertir transacción en caso de error
        $db->rollback();
        throw $e;
    }
    
} catch (Exception $e) {
    // Log del error
    error_log("Error en save-order-db.php: " . $e->getMessage());
    
    // Respuesta de error
    http_response_code(400);
    jsonResponse(false, $e->getMessage());
}
