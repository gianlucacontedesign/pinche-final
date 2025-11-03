<?php
/**
 * AJAX Handler para Estadísticas del Dashboard
 * Pinche Supplies - Actualizado: 03 Nov 2025
 */

// Incluir configuración y funciones
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../classes/Database.php';

// Verificar que sea una petición AJAX
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    http_response_code(403);
    exit('Acceso denegado');
}

// Verificar autenticación de administrador
session_start();
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    exit('No autorizado');
}

// Headers para JSON
header('Content-Type: application/json; charset=utf-8');

// Función para enviar respuesta JSON
function sendResponse($data) {
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// Función para enviar error
function sendError($message, $code = 500) {
    http_response_code($code);
    sendResponse(['error' => $message]);
}

try {
    $db = Database::getInstance();
    $action = $_GET['action'] ?? 'stats';
    
    switch ($action) {
        case 'stats':
            // Obtener estadísticas básicas
            $stats = [];
            
            // Total productos
            $result = $db->fetchOne("SELECT COUNT(*) as total FROM products WHERE is_active = 1");
            $stats['products'] = $result ? (int)$result['total'] : 0;
            
            // Total categorías
            $result = $db->fetchOne("SELECT COUNT(*) as total FROM categories WHERE is_active = 1");
            $stats['categories'] = $result ? (int)$result['total'] : 0;
            
            // Total clientes
            $result = $db->fetchOne("SELECT COUNT(*) as total FROM customers WHERE is_active = 1");
            $stats['customers'] = $result ? (int)$result['total'] : 0;
            
            // Total órdenes
            $result = $db->fetchOne("SELECT COUNT(*) as total FROM orders");
            $stats['orders'] = $result ? (int)$result['total'] : 0;
            
            // Órdenes pendientes
            $result = $db->fetchOne("SELECT COUNT(*) as total FROM orders WHERE order_status = 'pending'");
            $stats['pending_orders'] = $result ? (int)$result['total'] : 0;
            
            // Stock bajo
            $low_stock_threshold = getSetting('low_stock_threshold', 5);
            $result = $db->fetchOne("SELECT COUNT(*) as total FROM products WHERE stock <= ? AND is_active = 1", [$low_stock_threshold]);
            $stats['lowStock'] = $result ? (int)$result['total'] : 0;
            
            // Stock crítico
            $critical_stock_threshold = getSetting('critical_stock_threshold', 2);
            $result = $db->fetchOne("SELECT COUNT(*) as total FROM products WHERE stock <= ? AND is_active = 1", [$critical_stock_threshold]);
            $stats['criticalStock'] = $result ? (int)$result['total'] : 0;
            
            sendResponse($stats);
            break;
            
        case 'sales':
            // Datos para gráfico de ventas (últimos 12 meses)
            $sales_data = $db->fetchAll("
                SELECT 
                    DATE_FORMAT(created_at, '%Y-%m') as month,
                    SUM(total_amount) as total,
                    COUNT(*) as orders_count
                FROM orders 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                AND payment_status = 'paid'
                GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                ORDER BY month ASC
            ");
            
            sendResponse($sales_data ?: []);
            break;
            
        case 'top_products':
            // Top productos vendidos
            $top_products = $db->fetchAll("
                SELECT p.name, p.sales_count, p.price, c.name as category_name 
                FROM products p 
                JOIN categories c ON p.category_id = c.id 
                WHERE p.is_active = 1 
                ORDER BY p.sales_count DESC 
                LIMIT 10
            ");
            
            sendResponse($top_products ?: []);
            break;
            
        case 'low_stock':
            // Productos con stock bajo
            $low_stock_threshold = getSetting('low_stock_threshold', 5);
            $low_stock_products = $db->fetchAll("
                SELECT p.*, c.name as category_name 
                FROM products p 
                JOIN categories c ON p.category_id = c.id 
                WHERE p.stock <= ? AND p.is_active = 1 
                ORDER BY p.stock ASC 
                LIMIT 20
            ", [$low_stock_threshold]);
            
            sendResponse($low_stock_products ?: []);
            break;
            
        case 'recent_orders':
            // Órdenes recientes
            $recent_orders = $db->fetchAll("
                SELECT o.*, c.first_name, c.last_name 
                FROM orders o 
                LEFT JOIN customers c ON o.customer_id = c.id 
                ORDER BY o.created_at DESC 
                LIMIT 15
            ");
            
            sendResponse($recent_orders ?: []);
            break;
            
        case 'category_stats':
            // Estadísticas por categoría
            $category_stats = $db->fetchAll("
                SELECT 
                    c.name as category_name,
                    COUNT(p.id) as product_count,
                    SUM(p.stock) as total_stock,
                    AVG(p.price) as avg_price,
                    SUM(p.sales_count) as total_sales
                FROM categories c
                LEFT JOIN products p ON c.id = p.category_id AND p.is_active = 1
                WHERE c.is_active = 1
                GROUP BY c.id, c.name
                ORDER BY product_count DESC
            ");
            
            sendResponse($category_stats ?: []);
            break;
            
        case 'sales_summary':
            // Resumen de ventas
            $summary = [];
            
            // Ventas del día
            $result = $db->fetchOne("SELECT COALESCE(SUM(total_amount), 0) as total FROM orders WHERE DATE(created_at) = CURDATE() AND payment_status = 'paid'");
            $summary['today_sales'] = $result ? (float)$result['total'] : 0;
            
            // Ventas de la semana
            $result = $db->fetchOne("SELECT COALESCE(SUM(total_amount), 0) as total FROM orders WHERE YEARWEEK(created_at) = YEARWEEK(NOW()) AND payment_status = 'paid'");
            $summary['week_sales'] = $result ? (float)$result['total'] : 0;
            
            // Ventas del mes
            $result = $db->fetchOne("SELECT COALESCE(SUM(total_amount), 0) as total FROM orders WHERE MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW()) AND payment_status = 'paid'");
            $summary['month_sales'] = $result ? (float)$result['total'] : 0;
            
            // Órdenes del día
            $result = $db->fetchOne("SELECT COUNT(*) as total FROM orders WHERE DATE(created_at) = CURDATE()");
            $summary['today_orders'] = $result ? (int)$result['total'] : 0;
            
            sendResponse($summary);
            break;
            
        case 'refresh_all':
            // Actualizar todas las estadísticas
            $all_stats = [];
            
            // Estadísticas básicas
            $result = $db->fetchOne("SELECT COUNT(*) as total FROM products WHERE is_active = 1");
            $all_stats['products'] = $result ? (int)$result['total'] : 0;
            
            $result = $db->fetchOne("SELECT COUNT(*) as total FROM categories WHERE is_active = 1");
            $all_stats['categories'] = $result ? (int)$result['total'] : 0;
            
            $result = $db->fetchOne("SELECT COUNT(*) as total FROM orders");
            $all_stats['orders'] = $result ? (int)$result['total'] : 0;
            
            $result = $db->fetchOne("SELECT COUNT(*) as total FROM orders WHERE order_status = 'pending'");
            $all_stats['pending_orders'] = $result ? (int)$result['total'] : 0;
            
            // Stock bajo
            $low_stock_threshold = getSetting('low_stock_threshold', 5);
            $result = $db->fetchOne("SELECT COUNT(*) as total FROM products WHERE stock <= ? AND is_active = 1", [$low_stock_threshold]);
            $all_stats['low_stock'] = $result ? (int)$result['total'] : 0;
            
            // Stock crítico
            $critical_stock_threshold = getSetting('critical_stock_threshold', 2);
            $result = $db->fetchOne("SELECT COUNT(*) as total FROM products WHERE stock <= ? AND is_active = 1", [$critical_stock_threshold]);
            $all_stats['critical_stock'] = $result ? (int)$result['total'] : 0;
            
            // Ventas del mes
            $result = $db->fetchOne("SELECT COALESCE(SUM(total_amount), 0) as total FROM orders WHERE MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW()) AND payment_status = 'paid'");
            $all_stats['monthly_sales'] = $result ? (float)$result['total'] : 0;
            
            // Ventas del mes anterior
            $result = $db->fetchOne("SELECT COALESCE(SUM(total_amount), 0) as total FROM orders WHERE MONTH(created_at) = MONTH(NOW()) - 1 AND YEAR(created_at) = YEAR(NOW()) AND payment_status = 'paid'");
            $all_stats['last_month_sales'] = $result ? (float)$result['total'] : 0;
            
            // Calcular crecimiento
            $all_stats['sales_growth'] = 0;
            if ($all_stats['last_month_sales'] > 0) {
                $all_stats['sales_growth'] = (($all_stats['monthly_sales'] - $all_stats['last_month_sales']) / $all_stats['last_month_sales']) * 100;
            }
            
            sendResponse($all_stats);
            break;
            
        default:
            sendError('Acción no válida', 400);
    }
    
} catch (Exception $e) {
    logActivity('AJAX Stats Error', 'Error en ajax-stats.php: ' . $e->getMessage());
    sendError('Error interno del servidor', 500);
}
?>
