<?php
session_start();

// Headers para CORS y JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Manejar preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Función para sanitizar datos
function sanitize_input($data) {
    if (is_array($data)) {
        return array_map('sanitize_input', $data);
    }
    return trim(stripslashes(htmlspecialchars($data, ENT_QUOTES, 'UTF-8')));
}

// Función para verificar si la sesión del carrito existe
function ensure_cart_session() {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
}

// Función para calcular total del carrito
function calculate_cart_total() {
    $total = 0;
    foreach ($_SESSION['cart'] as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    return $total;
}

// Función para obtener la acción solicitada
$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Obtener datos del POST para acciones que lo requieren
$input_data = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content_type = $_SERVER['CONTENT_TYPE'] ?? '';
    if (strpos($content_type, 'application/json') !== false) {
        $input_data = json_decode(file_get_contents('php://input'), true) ?: [];
    } else {
        $input_data = $_POST ?: [];
    }
}

// Inicializar respuesta estándar
$response = [
    'success' => false,
    'message' => 'Acción no válida',
    'data' => null,
    'timestamp' => date('Y-m-d H:i:s')
];

try {
    switch ($action) {
        case 'get_count':
            ensure_cart_session();
            $count = 0;
            foreach ($_SESSION['cart'] as $item) {
                $count += $item['quantity'];
            }
            
            $response['success'] = true;
            $response['data']['count'] = $count;
            $response['message'] = 'Cantidad obtenida correctamente';
            break;

        case 'get_cart':
            ensure_cart_session();
            $cart_items = array_values($_SESSION['cart']);
            $total = calculate_cart_total();
            
            $response['success'] = true;
            $response['data'] = [
                'items' => $cart_items,
                'total' => $total,
                'count' => count($cart_items)
            ];
            $response['message'] = 'Carrito obtenido correctamente';
            break;

        case 'add':
            // Verificar que se recibió data
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Método no permitido para esta acción');
            }
            
            ensure_cart_session();
            
            // Obtener datos del producto
            $product_id = $input_data['product_id'] ?? '';
            $quantity = intval($input_data['quantity'] ?? 1);
            $name = $input_data['name'] ?? 'Producto sin nombre';
            $price = floatval($input_data['price'] ?? 0);
            
            // Validar datos requeridos
            if (empty($product_id)) {
                throw new Exception('ID de producto requerido');
            }
            
            if ($quantity <= 0) {
                throw new Exception('Cantidad debe ser mayor a 0');
            }
            
            if ($price <= 0) {
                throw new Exception('Precio debe ser mayor a 0');
            }
            
            // Sanitizar datos
            $product_id = sanitize_input($product_id);
            $name = sanitize_input($name);
            
            // Si el producto ya existe, actualizar cantidad
            if (isset($_SESSION['cart'][$product_id])) {
                $_SESSION['cart'][$product_id]['quantity'] += $quantity;
                $response['message'] = 'Cantidad actualizada en el carrito';
            } else {
                // Agregar nuevo producto
                $_SESSION['cart'][$product_id] = [
                    'id' => $product_id,
                    'name' => $name,
                    'price' => $price,
                    'quantity' => $quantity,
                    'added_at' => date('Y-m-d H:i:s')
                ];
                $response['message'] = 'Producto agregado al carrito';
            }
            
            $response['success'] = true;
            $response['data'] = [
                'product_id' => $product_id,
                'quantity' => $_SESSION['cart'][$product_id]['quantity'],
                'cart_total' => calculate_cart_total(),
                'cart_count' => array_sum(array_column($_SESSION['cart'], 'quantity'))
            ];
            break;

        case 'update':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Método no permitido para esta acción');
            }
            
            ensure_cart_session();
            
            $product_id = $input_data['product_id'] ?? '';
            $quantity = intval($input_data['quantity'] ?? 1);
            
            if (empty($product_id)) {
                throw new Exception('ID de producto requerido');
            }
            
            if (!isset($_SESSION['cart'][$product_id])) {
                throw new Exception('Producto no encontrado en el carrito');
            }
            
            $product_id = sanitize_input($product_id);
            
            if ($quantity <= 0) {
                // Eliminar producto si cantidad es 0 o negativa
                unset($_SESSION['cart'][$product_id]);
                $response['message'] = 'Producto eliminado del carrito';
            } else {
                // Actualizar cantidad
                $_SESSION['cart'][$product_id]['quantity'] = $quantity;
                $response['message'] = 'Cantidad actualizada';
            }
            
            $response['success'] = true;
            $response['data'] = [
                'product_id' => $product_id,
                'quantity' => $quantity,
                'cart_total' => calculate_cart_total(),
                'cart_count' => array_sum(array_column($_SESSION['cart'], 'quantity'))
            ];
            break;

        case 'remove':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Método no permitido para esta acción');
            }
            
            ensure_cart_session();
            
            $product_id = $input_data['product_id'] ?? '';
            
            if (empty($product_id)) {
                throw new Exception('ID de producto requerido');
            }
            
            $product_id = sanitize_input($product_id);
            
            if (isset($_SESSION['cart'][$product_id])) {
                unset($_SESSION['cart'][$product_id]);
                $response['success'] = true;
                $response['message'] = 'Producto eliminado del carrito';
                $response['data'] = [
                    'cart_total' => calculate_cart_total(),
                    'cart_count' => array_sum(array_column($_SESSION['cart'], 'quantity'))
                ];
            } else {
                throw new Exception('Producto no encontrado en el carrito');
            }
            break;

        case 'clear':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Método no permitido para esta acción');
            }
            
            ensure_cart_session();
            $_SESSION['cart'] = [];
            
            $response['success'] = true;
            $response['message'] = 'Carrito limpiado';
            $response['data'] = [
                'cart_total' => 0,
                'cart_count' => 0
            ];
            break;

        case 'get_total':
            ensure_cart_session();
            $total = calculate_cart_total();
            
            $response['success'] = true;
            $response['data']['total'] = $total;
            $response['message'] = 'Total calculado correctamente';
            break;

        // Mantener compatibilidad con acciones del sistema anterior
        case 'add_to_cart':
            // Convertir a la nueva acción
            $_POST['action'] = 'add';
            $_GET['action'] = 'add';
            // Usar los mismos parámetros que add
            $response = [];
            // Llamar recursivamente a la misma función
            $action = 'add';
            // Continuar con el switch
            // Nota: Podríamos usar goto, pero mejor reescribir
            
        case 'update_quantity':
            // Convertir a la nueva acción
            $product_id = $input_data['cart_key'] ?? $input_data['product_id'] ?? '';
            $quantity = intval($input_data['quantity'] ?? 1);
            
            $_POST['product_id'] = $product_id;
            $_POST['quantity'] = $quantity;
            $_GET['action'] = 'update';
            $_POST['action'] = 'update';
            
            $action = 'update';
            // Continuar con el switch case update
            
        case 'remove_from_cart':
            // Convertir a la nueva acción
            $product_id = $input_data['cart_key'] ?? $input_data['product_id'] ?? '';
            
            $_POST['product_id'] = $product_id;
            $_GET['action'] = 'remove';
            $_POST['action'] = 'remove';
            
            $action = 'remove';
            // Continuar con el switch case remove
            
            // Para add_to_cart, update_quantity y remove_from_cart:
            // Necesitamos procesar estos de manera especial
            if (in_array($action, ['add_to_cart', 'update_quantity', 'remove_from_cart'])) {
                // Procesar según el action original
                if ($action === 'add_to_cart') {
                    $product_id = $input_data['product_id'] ?? '';
                    $quantity = intval($input_data['quantity'] ?? 1);
                    $variant_id = $input_data['variant_id'] ?? null;
                    
                    // Por ahora usar datos de ejemplo si no están disponibles
                    $name = $input_data['name'] ?? 'Producto';
                    $price = floatval($input_data['price'] ?? 1000);
                    
                    $_POST['product_id'] = $product_id;
                    $_POST['quantity'] = $quantity;
                    $_POST['name'] = $name;
                    $_POST['price'] = $price;
                    
                    $action = 'add';
                } elseif ($action === 'update_quantity') {
                    $product_id = $input_data['cart_key'] ?? '';
                    $quantity = intval($input_data['quantity'] ?? 1);
                    
                    $_POST['product_id'] = $product_id;
                    $_POST['quantity'] = $quantity;
                    
                    $action = 'update';
                } elseif ($action === 'remove_from_cart') {
                    $product_id = $input_data['cart_key'] ?? '';
                    
                    $_POST['product_id'] = $product_id;
                    
                    $action = 'remove';
                }
            }
            break;

        default:
            $response['message'] = "Acción no reconocida: $action. Acciones disponibles: get_count, get_cart, add, update, remove, clear, get_total";
            $response['success'] = false;
            break;
    }
    
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
    
    // Log del error para debugging
    error_log("Cart AJAX Error: " . $e->getMessage() . " | Action: " . $action . " | Data: " . json_encode($input_data));
}

// Devolver respuesta JSON
echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>