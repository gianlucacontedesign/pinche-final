<?php
/**
 * AJAX Stats - Endpoint para estadísticas dinámicas
 */

require_once 'config/config.php';
require_once 'includes/class.database.php';
require_once 'includes/class.auth.php';

// Headers CORS para permitir peticiones AJAX
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Verificar que el usuario esté logueado como admin para estadísticas detalladas
$auth = new Auth();
$isAdmin = $auth->isLoggedIn();

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Determinar el tipo de estadística solicitada
    $type = $_GET['type'] ?? 'overview';
    
    switch ($type) {
        case 'overview':
            getOverviewStats($db, $isAdmin);
            break;
            
        case 'products':
            getProductsStats($db, $isAdmin);
            break;
            
        case 'orders':
            getOrdersStats($db, $isAdmin);
            break;
            
        case 'customers':
            getCustomersStats($db, $isAdmin);
            break;
            
        case 'sales':
            getSalesStats($db, $isAdmin);
            break;
            
        case 'recent':
            getRecentActivity($db, $isAdmin);
            break;
            
        case 'chart':
            getChartData($db, $isAdmin);
            break;
            
        default:
            throw new Exception('Tipo de estadística no válido');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
    exit;
}

/**
 * Obtener estadísticas generales del sistema
 */
function getOverviewStats($db, $isAdmin) {
    $stats = [];
    
    // Productos totales
    $stmt = $db->query("SELECT COUNT(*) FROM products");
    $stats['total_products'] = $stmt->fetchColumn();
    
    // Productos activos
    $stmt = $db->query("SELECT COUNT(*) FROM products WHERE status = 'active'");
    $stats['active_products'] = $stmt->fetchColumn();
    
    // Categorías totales
    $stmt = $db->query("SELECT COUNT(*) FROM categories");
    $stats['total_categories'] = $stmt->fetchColumn();
    
    // Pedidos totales
    $stmt = $db->query("SELECT COUNT(*) FROM orders");
    $stats['total_orders'] = $stmt->fetchColumn();
    
    // Usuarios totales
    $stmt = $db->query("SELECT COUNT(*) FROM customers");
    $stats['total_customers'] = $stmt->fetchColumn();
    
    if ($isAdmin) {
        // Ventas totales (solo para administradores)
        $stmt = $db->query("SELECT SUM(total_amount) FROM orders WHERE status != 'cancelled'");
        $stats['total_sales'] = $stmt->fetchColumn() ?: 0;
        
        // Pedidos pendientes
        $stmt = $db->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'");
        $stats['pending_orders'] = $stmt->fetchColumn();
        
        // Productos sin stock
        $stmt = $db->query("SELECT COUNT(*) FROM products WHERE stock_quantity = 0");
        $stats['out_of_stock'] = $stmt->fetchColumn();
    }
    
    echo json_encode([
        'success' => true,
        'data' => $stats,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}

/**
 * Obtener estadísticas específicas de productos
 */
function getProductsStats($db, $isAdmin) {
    $stats = [];
    
    // Productos por categoría
    $stmt = $db->query("
        SELECT c.name, COUNT(p.id) as product_count 
        FROM categories c 
        LEFT JOIN products p ON c.id = p.category_id 
        GROUP BY c.id, c.name 
        ORDER BY product_count DESC
    ");
    $stats['by_category'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Productos con bajo stock (menos de 10 unidades)
    $stmt = $db->query("
        SELECT id, name, stock_quantity 
        FROM products 
        WHERE stock_quantity < 10 AND stock_quantity > 0 
        ORDER BY stock_quantity ASC
    ");
    $stats['low_stock'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Productos sin stock
    $stmt = $db->query("
        SELECT id, name 
        FROM products 
        WHERE stock_quantity = 0 
        ORDER BY name ASC
    ");
    $stats['out_of_stock'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Productos más vendidos (si el admin tiene acceso)
    if ($isAdmin) {
        $stmt = $db->query("
            SELECT p.id, p.name, p.price, SUM(oi.quantity) as total_sold
            FROM products p
            JOIN order_items oi ON p.id = oi.product_id
            JOIN orders o ON oi.order_id = o.id
            WHERE o.status != 'cancelled'
            GROUP BY p.id, p.name, p.price
            ORDER BY total_sold DESC
            LIMIT 10
        ");
        $stats['top_selling'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    echo json_encode([
        'success' => true,
        'data' => $stats,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}

/**
 * Obtener estadísticas de pedidos
 */
function getOrdersStats($db, $isAdmin) {
    if (!$isAdmin) {
        throw new Exception('No autorizado para ver estadísticas de pedidos');
    }
    
    $stats = [];
    
    // Pedidos por estado
    $stmt = $db->query("
        SELECT status, COUNT(*) as count 
        FROM orders 
        GROUP BY status 
        ORDER BY count DESC
    ");
    $stats['by_status'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Pedidos por día (últimos 30 días)
    $stmt = $db->query("
        SELECT DATE(created_at) as date, COUNT(*) as count
        FROM orders 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY DATE(created_at)
        ORDER BY date DESC
    ");
    $stats['daily_orders'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Valor promedio de pedidos
    $stmt = $db->query("
        SELECT AVG(total_amount) as average_order_value
        FROM orders 
        WHERE status != 'cancelled'
    ");
    $stats['average_order_value'] = $stmt->fetchColumn();
    
    echo json_encode([
        'success' => true,
        'data' => $stats,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}

/**
 * Obtener estadísticas de clientes
 */
function getCustomersStats($db, $isAdmin) {
    $stats = [];
    
    // Total de clientes registrados
    $stmt = $db->query("SELECT COUNT(*) FROM customers");
    $stats['total_customers'] = $stmt->fetchColumn();
    
    // Clientes registrados por mes (últimos 12 meses)
    $stmt = $db->query("
        SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count
        FROM customers 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY month DESC
    ");
    $stats['monthly_registrations'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($isAdmin) {
        // Clientes más activos (con más pedidos)
        $stmt = $db->query("
            SELECT c.id, c.first_name, c.last_name, c.email, COUNT(o.id) as order_count
            FROM customers c
            LEFT JOIN orders o ON c.id = o.customer_id
            GROUP BY c.id, c.first_name, c.last_name, c.email
            HAVING order_count > 0
            ORDER BY order_count DESC
            LIMIT 10
        ");
        $stats['most_active'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    echo json_encode([
        'success' => true,
        'data' => $stats,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}

/**
 * Obtener estadísticas de ventas
 */
function getSalesStats($db, $isAdmin) {
    if (!$isAdmin) {
        throw new Exception('No autorizado para ver estadísticas de ventas');
    }
    
    $stats = [];
    
    // Ventas por día (últimos 30 días)
    $stmt = $db->query("
        SELECT DATE(created_at) as date, SUM(total_amount) as daily_sales
        FROM orders 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) 
        AND status != 'cancelled'
        GROUP BY DATE(created_at)
        ORDER BY date DESC
    ");
    $stats['daily_sales'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Ventas por mes (últimos 12 meses)
    $stmt = $db->query("
        SELECT DATE_FORMAT(created_at, '%Y-%m') as month, SUM(total_amount) as monthly_sales
        FROM orders 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH) 
        AND status != 'cancelled'
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY month DESC
    ");
    $stats['monthly_sales'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Productos más vendidos
    $stmt = $db->query("
        SELECT p.name, SUM(oi.quantity) as total_sold, SUM(oi.quantity * oi.price) as total_revenue
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        JOIN orders o ON oi.order_id = o.id
        WHERE o.status != 'cancelled'
        GROUP BY p.id, p.name
        ORDER BY total_sold DESC
        LIMIT 10
    ");
    $stats['top_products'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $stats,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}

/**
 * Obtener actividad reciente del sistema
 */
function getRecentActivity($db, $isAdmin) {
    $activities = [];
    
    // Pedidos recientes
    $stmt = $db->query("
        SELECT o.id, o.created_at, CONCAT(c.first_name, ' ', c.last_name) as customer_name, o.total_amount, o.status
        FROM orders o
        JOIN customers c ON o.customer_id = c.id
        ORDER BY o.created_at DESC
        LIMIT 10
    ");
    $activities['recent_orders'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Productos agregados recientemente
    $stmt = $db->query("
        SELECT id, name, created_at, price, stock_quantity
        FROM products
        ORDER BY created_at DESC
        LIMIT 10
    ");
    $activities['recent_products'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Nuevos clientes
    $stmt = $db->query("
        SELECT id, first_name, last_name, email, created_at
        FROM customers
        ORDER BY created_at DESC
        LIMIT 10
    ");
    $activities['recent_customers'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $activities,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}

/**
 * Obtener datos para gráficos
 */
function getChartData($db, $isAdmin) {
    if (!$isAdmin) {
        throw new Exception('No autorizado para ver datos de gráficos');
    }
    
    $period = $_GET['period'] ?? '30'; // días
    
    // Ventas diarias para gráfico
    $stmt = $db->query("
        SELECT 
            DATE(created_at) as date,
            COUNT(*) as orders_count,
            SUM(total_amount) as daily_revenue
        FROM orders 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY) 
        AND status != 'cancelled'
        GROUP BY DATE(created_at)
        ORDER BY date ASC
    ", [$period]);
    
    $salesChart = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Productos por categoría para gráfico
    $stmt = $db->query("
        SELECT 
            c.name as category,
            COUNT(p.id) as product_count
        FROM categories c 
        LEFT JOIN products p ON c.id = p.category_id 
        GROUP BY c.id, c.name 
        ORDER BY product_count DESC
    ");
    
    $categoryChart = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Estados de pedidos para gráfico
    $stmt = $db->query("
        SELECT status, COUNT(*) as count 
        FROM orders 
        GROUP BY status
    ");
    
    $statusChart = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => [
            'sales_chart' => $salesChart,
            'category_chart' => $categoryChart,
            'status_chart' => $statusChart
        ],
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}

/**
 * Función auxiliar para formatear números
 */
function formatNumber($number) {
    return number_format($number, 0, ',', '.');
}

/**
 * Función auxiliar para formatear moneda
 */
function formatCurrency($amount) {
    return '$' . number_format($amount, 2, ',', '.');
}
?>