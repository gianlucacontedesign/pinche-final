<?php
/**
 * Funciones de Ayuda - Panel de Administración
 * Pinche Supplies - Admin Panel
 * Actualizado: 03 Nov 2025
 */

/**
 * Función para registrar actividad del sistema
 */
function logActivity($action, $details = '') {
    if (!defined('LOG_ERRORS') || !LOG_ERRORS) return;
    
    $timestamp = date('Y-m-d H:i:s');
    $user = $_SESSION['admin_name'] ?? 'Unknown';
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    
    $logEntry = "[$timestamp] User: $user | Action: $action | Details: $details | IP: $ip" . PHP_EOL;
    
    $logFile = defined('LOG_FILE') ? LOG_FILE : '../logs/app.log';
    
    // Crear directorio si no existe
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

/**
 * Función para establecer mensaje flash
 */
function setFlashMessage($type, $message) {
    if (!isset($_SESSION)) {
        session_start();
    }
    
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Función para obtener mensaje flash
 */
function getFlashMessage($type = null) {
    if (!isset($_SESSION)) {
        session_start();
    }
    
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        
        if ($type === null || $message['type'] === $type) {
            return $message['message'];
        }
    }
    
    return null;
}

/**
 * Función para sanitizar entrada
 */
function sanitizeInput($input) {
    if (is_array($input)) {
        return array_map('sanitizeInput', $input);
    }
    
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Función para validar email
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Función para generar número de pedido único
 */
function generateOrderNumber() {
    $prefix = 'ORD';
    $timestamp = time();
    $random = mt_rand(1000, 9999);
    
    return $prefix . $timestamp . $random;
}

/**
 * Función para formatear fecha
 */
function formatDate($date, $format = 'd/m/Y H:i') {
    if (empty($date)) return '';
    
    $timestamp = strtotime($date);
    if ($timestamp === false) return '';
    
    return date($format, $timestamp);
}

/**
 * Función para formatear moneda
 */
function formatCurrency($amount, $currency = 'ARS') {
    $symbols = [
        'ARS' => '$',
        'USD' => 'US$',
        'EUR' => '€'
    ];
    
    $symbol = $symbols[$currency] ?? '$';
    
    return $symbol . number_format($amount, 2, ',', '.');
}

/**
 * Función para calcular porcentaje
 */
function calculatePercentage($part, $total) {
    if ($total == 0) return 0;
    
    return round(($part / $total) * 100, 2);
}

/**
 * Función para obtener estado badge HTML
 */
function getStatusBadge($status, $type = 'order') {
    $badges = [
        'order' => [
            'pendiente' => '<span class="status-badge status-pendiente">Pendiente</span>',
            'procesado' => '<span class="status-badge status-procesado">Procesado</span>',
            'enviado' => '<span class="status-badge status-enviado">Enviado</span>',
            'entregado' => '<span class="status-badge status-entregado">Entregado</span>',
            'cancelado' => '<span class="status-badge status-cancelado">Cancelado</span>',
            'devuelto' => '<span class="status-badge status-devuelto">Devuelto</span>'
        ],
        'product' => [
            'active' => '<span class="status-badge status-active">Activo</span>',
            'inactive' => '<span class="status-badge status-inactive">Inactivo</span>',
            'discontinued' => '<span class="status-badge status-discontinued">Descontinuado</span>'
        ],
        'customer' => [
            'active' => '<span class="status-badge status-active">Activo</span>',
            'inactive' => '<span class="status-badge status-inactive">Inactivo</span>',
            'blocked' => '<span class="status-badge status-blocked">Bloqueado</span>'
        ]
    ];
    
    return $badges[$type][$status] ?? '<span class="status-badge">Desconocido</span>';
}

/**
 * Función para obtener colores de stock
 */
function getStockBadge($quantity, $critical = 2, $low = 5) {
    if ($quantity <= $critical) {
        return '<span class="stock-badge stock-low">Crítico (' . $quantity . ')</span>';
    } elseif ($quantity <= $low) {
        return '<span class="stock-badge stock-medium">Bajo (' . $quantity . ')</span>';
    } else {
        return '<span class="stock-badge stock-high">OK (' . $quantity . ')</span>';
    }
}

/**
 * Función para paginar resultados
 */
function paginate($currentPage, $totalPages, $baseUrl, $params = []) {
    $pagination = '';
    
    if ($totalPages <= 1) return $pagination;
    
    // Primera página
    if ($currentPage > 1) {
        $params['page'] = 1;
        $pagination .= '<a href="' . $baseUrl . '?' . http_build_query($params) . '"><i class="fas fa-angle-double-left"></i></a>';
        
        $params['page'] = $currentPage - 1;
        $pagination .= '<a href="' . $baseUrl . '?' . http_build_query($params) . '"><i class="fas fa-angle-left"></i></a>';
    }
    
    // Números de página
    $start = max(1, $currentPage - 2);
    $end = min($totalPages, $currentPage + 2);
    
    for ($i = $start; $i <= $end; $i++) {
        if ($i == $currentPage) {
            $pagination .= '<span class="current">' . $i . '</span>';
        } else {
            $params['page'] = $i;
            $pagination .= '<a href="' . $baseUrl . '?' . http_build_query($params) . '">' . $i . '</a>';
        }
    }
    
    // Última página
    if ($currentPage < $totalPages) {
        $params['page'] = $currentPage + 1;
        $pagination .= '<a href="' . $baseUrl . '?' . http_build_query($params) . '"><i class="fas fa-angle-right"></i></a>';
        
        $params['page'] = $totalPages;
        $pagination .= '<a href="' . $baseUrl . '?' . http_build_query($params) . '"><i class="fas fa-angle-double-right"></i></a>';
    }
    
    return '<div class="pagination">' . $pagination . '</div>';
}

/**
 * Función para manejar subida de archivos
 */
function handleFileUpload($file, $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'], $maxSize = 5242880) {
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'Error en la subida del archivo'];
    }
    
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'error' => 'El archivo es demasiado grande'];
    }
    
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($extension, $allowedTypes)) {
        return ['success' => false, 'error' => 'Tipo de archivo no permitido'];
    }
    
    $filename = uniqid() . '.' . $extension;
    $uploadPath = '../uploads/' . $filename;
    
    if (!is_dir('../uploads')) {
        mkdir('../uploads', 0755, true);
    }
    
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        return ['success' => true, 'filename' => $filename];
    } else {
        return ['success' => false, 'error' => 'Error al guardar el archivo'];
    }
}

/**
 * Función para enviar email
 */
function sendEmail($to, $subject, $body, $isHtml = true) {
    require_once '../includes/email-sender.php';
    
    return sendEmailInternal($to, $subject, $body, $isHtml);
}

/**
 * Función para generar token CSRF
 */
function generateCSRFToken() {
    if (!isset($_SESSION)) {
        session_start();
    }
    
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    return $_SESSION['csrf_token'];
}

/**
 * Función para validar token CSRF
 */
function validateCSRFToken($token) {
    if (!isset($_SESSION)) {
        session_start();
    }
    
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Función para limpiar sesión
 */
function clearSession() {
    if (!isset($_SESSION)) {
        session_start();
    }
    
    $_SESSION = [];
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
}

/**
 * Función para registrar error
 */
function logError($message, $context = []) {
    if (!defined('LOG_ERRORS') || !LOG_ERRORS) return;
    
    $timestamp = date('Y-m-d H:i:s');
    $contextStr = !empty($context) ? ' Context: ' . json_encode($context) : '';
    
    $logEntry = "[$timestamp] ERROR: $message$contextStr" . PHP_EOL;
    
    $logFile = defined('LOG_FILE') ? LOG_FILE : '../logs/error.log';
    
    // Crear directorio si no existe
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

/**
 * Función para debug
 */
function debug($data, $die = false) {
    if (!defined('DEBUG_MODE') || !DEBUG_MODE) return;
    
    echo '<pre style="background: #f0f0f0; padding: 20px; border: 1px solid #ccc; margin: 10px;">';
    print_r($data);
    echo '</pre>';
    
    if ($die) {
        die();
    }
}

/**
 * Función para redirigir
 */
function redirect($url, $statusCode = 302) {
    header("Location: $url", true, $statusCode);
    exit;
}

/**
 * Función para obtener URL actual
 */
function getCurrentUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    return $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

/**
 * Función para generar slug
 */
function generateSlug($text) {
    // Reemplazar caracteres especiales
    $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
    $text = preg_replace('/[^a-zA-Z0-9\s]/', '', $text);
    
    // Convertir a minúsculas y reemplazar espacios con guiones
    $text = strtolower(trim($text));
    $text = preg_replace('/\s+/', '-', $text);
    
    return $text;
}

/**
 * Función para truncar texto
 */
function truncateText($text, $length = 100, $suffix = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    
    return substr($text, 0, $length) . $suffix;
}

/**
 * Función para verificar permisos de administrador
 */
function requireAdmin($redirect = true) {
    if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_name'])) {
        if ($redirect) {
            redirect('login.php');
        }
        return false;
    }
    
    return true;
}

/**
 * Función para verificar timeout de sesión
 */
function checkSessionTimeout($timeout = 1800) { // 30 minutos por defecto
    if (!isset($_SESSION)) {
        session_start();
    }
    
    if (isset($_SESSION['admin_last_activity'])) {
        if (time() - $_SESSION['admin_last_activity'] > $timeout) {
            clearSession();
            redirect('login.php');
        }
    }
    
    $_SESSION['admin_last_activity'] = time();
}

/**
 * Función para obtener configuración de la aplicación
 */
function getAppConfig($key = null, $default = null) {
    $config = get_config();
    
    if ($key === null) {
        return $config;
    }
    
    $keys = explode('.', $key);
    $value = $config;
    
    foreach ($keys as $k) {
        if (!isset($value[$k])) {
            return $default;
        }
        $value = $value[$k];
    }
    
    return $value;
}

/**
 * Función para establecer configuración de la aplicación
 */
function setAppConfig($key, $value) {
    $config = get_config();
    $keys = explode('.', $key);
    
    $current = &$config;
    foreach ($keys as $k) {
        if (!isset($current[$k])) {
            $current[$k] = [];
        }
        $current = &$current[$k];
    }
    
    $current = $value;
    
    // Aquí podrías guardar en archivo de configuración
    // file_put_contents('../config/app-config.json', json_encode($config));
}

/**
 * Función para generar código QR
 */
function generateQRCode($text) {
    // Requiere library como endroid/qr-code
    // Placeholder function
    return "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($text);
}

/**
 * Función para convertir array a CSV
 */
function arrayToCSV($data, $filename = 'export.csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    
    if (!empty($data)) {
        // Headers
        fputcsv($output, array_keys($data[0]));
        
        // Data
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
    }
    
    fclose($output);
    exit;
}

/**
 * Función para convertir array a JSON
 */
function arrayToJSON($data, $filename = 'export.json') {
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Función para obtener estadísticas del sistema
 */
function getSystemStats() {
    try {
        $db = Database::getInstance();
        
        return [
            'orders' => [
                'total' => $db->count('orders'),
                'pending' => $db->count('orders', 'status = ?', ['pendiente']),
                'processing' => $db->count('orders', 'status = ?', ['procesado']),
                'shipped' => $db->count('orders', 'status = ?', ['enviado']),
                'delivered' => $db->count('orders', 'status = ?', ['entregado'])
            ],
            'products' => [
                'total' => $db->count('products', 'is_deleted = 0'),
                'active' => $db->count('products', 'is_deleted = 0 AND is_active = 1'),
                'low_stock' => $db->count('products', 'stock_quantity <= ? AND is_deleted = 0', [5])
            ],
            'customers' => [
                'total' => $db->count('customers'),
                'active' => $db->count('customers', 'status = ?', ['active'])
            ],
            'categories' => [
                'total' => $db->count('categories'),
                'active' => $db->count('categories', 'is_active = 1')
            ]
        ];
    } catch (Exception $e) {
        logError('Error getting system stats: ' . $e->getMessage());
        return [];
    }
}

/**
 * Función para limpiar datos antiguos
 */
function cleanupOldData($days = 90) {
    try {
        $db = Database::getInstance();
        
        // Limpiar logs antiguos
        $cutoffDate = date('Y-m-d H:i:s', strtotime("-$days days"));
        
        // Aquí puedes agregar más operaciones de limpieza
        // $db->execute("DELETE FROM user_sessions WHERE last_activity < ?", [$cutoffDate]);
        
        logActivity('cleanup', "Cleaned data older than $days days");
        
        return true;
    } catch (Exception $e) {
        logError('Error during cleanup: ' . $e->getMessage());
        return false;
    }
}
