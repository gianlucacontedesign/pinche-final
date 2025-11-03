<?php
// Configuración de base de datos y sistema
// Configuración básica para Pinche Supplies

// Configuración de la base de datos (si la tienes)
define('DB_HOST', 'localhost');
define('DB_NAME', 'pinche_supplies');
define('DB_USER', 'root');
define('DB_PASS', '');

// Configuración del sitio
define('SITE_NAME', 'Pinche Supplies');
define('SITE_URL', 'https://pinchesupplies.com.ar');
define('ADMIN_EMAIL', 'contacto@pinchesupplies.com');

// Configuración de correo
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'contacto@pinchesupplies.com');
define('SMTP_PASS', 'tu_password_smtp');

// Configuración de envío
define('SHIPPING_FREE_MIN', 5000); // Envío gratis para compras superiores a $5000
define('SHIPPING_COST', 800); // Costo de envío estándar

// Configuración de pago
define('CURRENCY', '$');
define('CURRENCY_CODE', 'ARS');

// Configuración de sesión
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1); // Solo para HTTPS

// Zona horaria
date_default_timezone_set('America/Argentina/Buenos Aires');

// Configuración de error reporting (deshabilitado en producción)
error_reporting(E_ALL);
ini_set('display_errors', 0); // Cambiar a 1 para debug
ini_set('log_errors', 1);

// Función para sanitizar entradas
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Función para validar email
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Función para generar ID único de pedido
function generate_order_id() {
    return 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
}

// Función para formatear precios
function format_price($price) {
    return CURRENCY . number_format($price, 2, ',', '.');
}

// Función para redirigir
function redirect($url) {
    header("Location: $url");
    exit;
}

// Función para mostrar mensajes
function show_message($message, $type = 'info') {
    $types = [
        'success' => 'alert-success',
        'error' => 'alert-danger',
        'warning' => 'alert-warning',
        'info' => 'alert-info'
    ];
    
    $class = isset($types[$type]) ? $types[$type] : $types['info'];
    
    return "<div class='alert $class alert-dismissible fade show' role='alert'>
                $message
                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
            </div>";
}

// Función para logging (opcional)
function log_activity($message, $type = 'INFO') {
    $log_file = 'logs/app.log';
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[$timestamp] [$type] $message" . PHP_EOL;
    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
}

// Configuración de CORS para AJAX (si es necesario)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Configuración de headers de seguridad
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Configurar carrito de compras si no existe
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Función para agregar producto al carrito
function add_to_cart($product_id, $quantity = 1) {
    // Esta función debería conectar con tu base de datos de productos
    // Por ahora es un ejemplo básico
    if (!isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] = [
            'id' => $product_id,
            'name' => 'Producto Ejemplo', // Obtener de la base de datos
            'price' => 1000, // Obtener de la base de datos
            'quantity' => $quantity
        ];
    } else {
        $_SESSION['cart'][$product_id]['quantity'] += $quantity;
    }
}

// Función para actualizar cantidad en el carrito
function update_cart_quantity($product_id, $quantity) {
    if (isset($_SESSION['cart'][$product_id])) {
        if ($quantity <= 0) {
            unset($_SESSION['cart'][$product_id]);
        } else {
            $_SESSION['cart'][$product_id]['quantity'] = $quantity;
        }
    }
}

// Función para eliminar producto del carrito
function remove_from_cart($product_id) {
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
    }
}

// Función para obtener total del carrito
function get_cart_total() {
    $total = 0;
    foreach ($_SESSION['cart'] as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    return $total;
}

// Función para obtener cantidad total del carrito
function get_cart_count() {
    $count = 0;
    foreach ($_SESSION['cart'] as $item) {
        $count += $item['quantity'];
    }
    return $count;
}

// Función para limpiar carrito
function clear_cart() {
    $_SESSION['cart'] = [];
}

// Conexión a base de datos (opcional)
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Log del error (no mostrar en producción)
    log_activity("Database connection failed: " . $e->getMessage(), 'ERROR');
    $pdo = null;
}
?>