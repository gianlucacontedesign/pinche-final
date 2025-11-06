<?php
/**
 * Guardar Pedido en Base de Datos - Versión Simplificada
 * Sin dependencias de archivos externos para evitar errores 500
 */

session_start();
header('Content-Type: application/json');

// Activar reporte de errores para debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // No mostrar en producción
ini_set('log_errors', 1);

// Configuración de base de datos - CREDENCIALES CORRECTAS
define('DB_HOST', 'localhost');
define('DB_NAME', 'a0030995_pinche');
define('DB_USER', 'a0030995_pinche'); // ✅ CORREGIDO
define('DB_PASS', 'vawuDU97zu');
define('DB_CHARSET', 'utf8mb4');

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
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Función para log de errores
 */
function logError($message) {
    $logFile = __DIR__ . '/logs/errores.log';
    $logDir = dirname($logFile);
    
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] $message\n";
    @file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
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
    
    // Calcular envío
    $shippingAmount = 0;
    if ($subtotal < 5000) {
        $shippingAmount = 800;
    }
    
    // Calcular total
    $totalAmount = $subtotal + $shippingAmount;
    
    // Generar número de orden
    $orderNumber = generateOrderNumber();
    
    // Conectar a la base de datos
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
    } catch (PDOException $e) {
        logError("Error de conexión DB: " . $e->getMessage());
        throw new Exception('Error de conexión a la base de datos');
    }
    
    // Iniciar transacción
    $pdo->beginTransaction();
    
    try {
        // Preparar datos de la orden
        $shippingAddress = json_encode([
            'address' => $data['shipping']['address'] ?? '',
            'city' => $data['shipping']['city'] ?? '',
            'province' => $data['shipping']['province'] ?? '',
            'postal_code' => $data['shipping']['postal_code'] ?? ''
        ], JSON_UNESCAPED_UNICODE);
        
        // Insertar orden
        $sqlOrder = "INSERT INTO orders (
            order_number,
            customer_id,
            customer_email,
            customer_name,
            customer_phone,
            subtotal,
            tax_amount,
            shipping_amount,
            discount_amount,
            total_amount,
            payment_method,
            payment_status,
            order_status,
            fulfillment_status,
            shipping_address,
            billing_address,
            notes,
            ip_address,
            user_agent,
            created_at
        ) VALUES (
            ?, NULL, ?, ?, ?, ?, 0, ?, 0, ?, ?, 'pending', 'pending', 'unfulfilled', ?, ?, ?, ?, ?, NOW()
        )";
        
        $stmtOrder = $pdo->prepare($sqlOrder);
        $stmtOrder->execute([
            $orderNumber,
            $data['customer']['email'],
            $data['customer']['first_name'] . ' ' . $data['customer']['last_name'],
            $data['customer']['phone'] ?? '',
            $subtotal,
            $shippingAmount,
            $totalAmount,
            $data['payment']['method'] ?? 'cash',
            $shippingAddress,
            $shippingAddress,
            $data['payment']['notes'] ?? '',
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
        
        $orderId = $pdo->lastInsertId();
        
        if (!$orderId) {
            throw new Exception('Error al crear el pedido');
        }
        
        // Insertar items del pedido
        $sqlItem = "INSERT INTO order_items (
            order_id,
            product_id,
            product_name,
            variant_info,
            quantity,
            price,
            subtotal,
            created_at
        ) VALUES (?, ?, ?, NULL, ?, ?, ?, NOW())";
        
        $stmtItem = $pdo->prepare($sqlItem);
        
        foreach ($data['cart'] as $item) {
            $itemSubtotal = floatval($item['price']) * intval($item['quantity']);
            
            $stmtItem->execute([
                $orderId,
                $item['id'] ?? 0,
                $item['name'] ?? 'Producto',
                intval($item['quantity']),
                floatval($item['price']),
                $itemSubtotal
            ]);
        }
        
        // Confirmar transacción
        $pdo->commit();
        
        // Guardar en sesión para confirmación
        $_SESSION['last_order'] = [
            'order_id' => $orderId,
            'order_number' => $orderNumber,
            'customer' => $data['customer'],
            'shipping' => $data['shipping'],
            'payment' => $data['payment'],
            'cart' => $data['cart'],
            'total' => $totalAmount,
            'order_date' => date('Y-m-d H:i:s')
        ];
        
        // Limpiar carrito
        $_SESSION['cart'] = [];
        
        // Respuesta exitosa
        jsonResponse(true, 'Pedido guardado exitosamente', [
            'order_id' => $orderId,
            'order_number' => $orderNumber,
            'total' => $totalAmount,
            'items_count' => count($data['cart'])
        ]);
        
    } catch (Exception $e) {
        // Revertir transacción
        $pdo->rollback();
        logError("Error en transacción: " . $e->getMessage());
        throw $e;
    }
    
} catch (Exception $e) {
    // Log del error
    logError("Error en save-order-db: " . $e->getMessage());
    
    // Respuesta de error
    http_response_code(400);
    jsonResponse(false, $e->getMessage());
}
