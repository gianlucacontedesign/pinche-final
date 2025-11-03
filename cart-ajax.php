<?php
// =============================================================================
// ARCHIVO CART-AJAX.MEJORADO.PHP
// Versión mejorada con diagnóstico completo
// =============================================================================

// Configuración de errores para debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// Headers para CORS y JSON
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

// Manejar preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Función para respuestas JSON seguras
function jsonResponse($data, $success = true, $code = 200) {
    http_response_code($code);
    $response = [
        'success' => $success,
        'data' => $data,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    echo json_encode($response, JSON_PRETTY_PRINT);
    exit;
}

// Log de errores
function logError($message, $context = []) {
    $logData = [
        'time' => date('Y-m-d H:i:s'),
        'message' => $message,
        'context' => $context,
        'server' => $_SERVER['REQUEST_METHOD'] . ' ' . ($_SERVER['REQUEST_URI'] ?? '/')
    ];
    file_put_contents(__DIR__ . '/cart-debug.log', json_encode($logData) . "\n", FILE_APPEND | LOCK_EX);
}

logError('Iniciando request', [
    'method' => $_SERVER['REQUEST_METHOD'],
    'action' => $_GET['action'] ?? $_POST['action'] ?? 'none',
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
]);

try {
    // Verificar si existe la configuración
    $configFile = __DIR__ . '/config/config.php';
    if (!file_exists($configFile)) {
        logError('Archivo config.php no encontrado', ['path' => $configFile]);
        jsonResponse([
            'error' => 'Archivo de configuración no encontrado',
            'debug_info' => [
                'config_path' => $configFile,
                'current_dir' => __DIR__,
                'files_in_dir' => scandir(__DIR__)
            ]
        ], false, 500);
    }
    
    require_once $configFile;
    logError('Config cargado exitosamente');
    
    // Verificar que la clase Cart existe
    if (!class_exists('Cart')) {
        logError('Clase Cart no existe en config.php');
        jsonResponse([
            'error' => 'Clase Cart no encontrada',
            'debug_info' => [
                'defined_classes' => get_declared_classes(),
                'config_file_exists' => file_exists($configFile)
            ]
        ], false, 500);
    }
    
    // Inicializar carrito
    $cart = new Cart();
    logError('Carrito inicializado');
    
    // Obtener acción
    $action = '';
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $action = $_GET['action'] ?? '';
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
    }
    
    if (empty($action)) {
        logError('Acción no especificada');
        jsonResponse(['error' => 'Acción requerida'], false, 400);
    }
    
    logError('Procesando acción', ['action' => $action]);
    
    // Ejecutar acción solicitada
    switch ($action) {
        case 'get_count':
            $count = $cart->getCount();
            logError('get_count exitoso', ['count' => $count]);
            jsonResponse(['count' => $count]);
            break;
            
        case 'add_to_cart':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                jsonResponse(['error' => 'Requiere método POST'], false, 405);
            }
            
            $productId = (int)($_POST['product_id'] ?? 0);
            $quantity = (int)($_POST['quantity'] ?? 1);
            $variantId = isset($_POST['variant_id']) && !empty($_POST['variant_id']) ? (int)$_POST['variant_id'] : null;
            
            if (!$productId || $productId <= 0) {
                jsonResponse(['error' => 'ID de producto inválido'], false, 400);
            }
            
            $result = $cart->add($productId, $quantity, $variantId);
            logError('add_to_cart exitoso', ['productId' => $productId, 'quantity' => $quantity]);
            jsonResponse($result);
            break;
            
        case 'update_quantity':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                jsonResponse(['error' => 'Requiere método POST'], false, 405);
            }
            
            $cartKey = $_POST['cart_key'] ?? '';
            $quantity = (int)($_POST['quantity'] ?? 1);
            
            if (empty($cartKey)) {
                jsonResponse(['error' => 'Clave del carrito requerida'], false, 400);
            }
            
            $result = $cart->update($cartKey, $quantity);
            logError('update_quantity exitoso', ['cartKey' => $cartKey, 'quantity' => $quantity]);
            jsonResponse($result);
            break;
            
        case 'remove_from_cart':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                jsonResponse(['error' => 'Requiere método POST'], false, 405);
            }
            
            $cartKey = $_POST['cart_key'] ?? '';
            if (empty($cartKey)) {
                jsonResponse(['error' => 'Clave del carrito requerida'], false, 400);
            }
            
            $result = $cart->remove($cartKey);
            logError('remove_from_cart exitoso', ['cartKey' => $cartKey]);
            jsonResponse($result);
            break;
            
        case 'get_cart':
            $cartData = [
                'items' => $cart->getItems(),
                'count' => $cart->getCount(),
                'totals' => $cart->getTotal()
            ];
            logError('get_cart exitoso', ['item_count' => count($cartData['items'])]);
            jsonResponse($cartData);
            break;
            
        case 'clear_cart':
            $cart->clear();
            logError('clear_cart exitoso');
            jsonResponse(['success' => true]);
            break;
            
        default:
            logError('Acción no válida', ['action' => $action]);
            jsonResponse(['error' => 'Acción no válida: ' . $action], false, 400);
            break;
    }
    
} catch (Exception $e) {
    logError('Error en cart-ajax', [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
    
    jsonResponse([
        'error' => 'Error interno del servidor',
        'debug_message' => $e->getMessage()
    ], false, 500);
}

// Si llegamos aquí, algo salió mal
logError('Punto de salida inesperado');
jsonResponse(['error' => 'Error inesperado'], false, 500);
?>