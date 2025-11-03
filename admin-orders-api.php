<?php
session_start();

// Headers para CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Manejar preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Solo permitir GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido'
    ]);
    exit();
}

// Función para obtener pedidos desde el archivo JSON
function get_orders_for_admin() {
    $orders_file = __DIR__ . '/orders.json';
    $orders = [];
    
    if (file_exists($orders_file)) {
        $content = file_get_contents($orders_file);
        if (!empty($content)) {
            $orders = json_decode($content, true) ?: [];
        }
    }
    
    // Transformar pedidos para el formato que espera el admin
    $formatted_orders = [];
    foreach ($orders as $order_id => $order) {
        $formatted_order = [
            'id' => $order_id,
            'order_number' => $order['order_id'] ?? $order_id,
            'customer_name' => ($order['customer']['first_name'] ?? '') . ' ' . ($order['customer']['last_name'] ?? ''),
            'customer_email' => $order['customer']['email'] ?? '',
            'customer_phone' => $order['customer']['phone'] ?? '',
            'total_amount' => floatval($order['total'] ?? 0),
            'status' => $order['status'] ?? 'confirmado',
            'payment_method' => $order['payment']['method'] ?? '',
            'created_at' => $order['created_at'] ?? date('Y-m-d H:i:s', filemtime($orders_file)),
            'items_count' => count($order['cart'] ?? []),
            'shipping_address' => [
                'address' => $order['shipping']['address'] ?? '',
                'city' => $order['shipping']['city'] ?? '',
                'province' => $order['shipping']['province'] ?? '',
                'postal_code' => $order['shipping']['postal_code'] ?? ''
            ],
            'items' => $order['cart'] ?? []
        ];
        
        $formatted_orders[] = $formatted_order;
    }
    
    // Ordenar por fecha de creación (más reciente primero)
    usort($formatted_orders, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
    
    return $formatted_orders;
}

// Función para generar estadísticas del dashboard
function get_order_statistics($orders) {
    $stats = [
        'total_orders' => count($orders),
        'pending_orders' => 0,
        'processing_orders' => 0,
        'shipped_orders' => 0,
        'delivered_orders' => 0,
        'cancelled_orders' => 0,
        'total_revenue' => 0
    ];
    
    foreach ($orders as $order) {
        // Contar por estado
        $status = strtolower($order['status'] ?? 'confirmado');
        switch ($status) {
            case 'pendiente':
                $stats['pending_orders']++;
                break;
            case 'procesando':
                $stats['processing_orders']++;
                break;
            case 'enviado':
                $stats['shipped_orders']++;
                break;
            case 'entregado':
                $stats['delivered_orders']++;
                break;
            case 'cancelado':
                $stats['cancelled_orders']++;
                break;
            default:
                // Cualquier estado que no esté definido, contarlo como pendiente
                $stats['pending_orders']++;
                break;
        }
        
        // Sumar revenue (solo pedidos no cancelados)
        if ($status !== 'cancelado') {
            $stats['total_revenue'] += floatval($order['total_amount'] ?? 0);
        }
    }
    
    return $stats;
}

// Obtener pedidos
$orders = get_orders_for_admin();
$stats = get_order_statistics($orders);

// Respuesta para el panel de admin
echo json_encode([
    'success' => true,
    'data' => [
        'orders' => $orders,
        'statistics' => $stats,
        'recent_orders' => array_slice($orders, 0, 5), // Últimos 5 pedidos
        'period_info' => [
            'start_date' => date('Y-m-d', strtotime('-30 days')),
            'end_date' => date('Y-m-d'),
            'total_days' => 30
        ]
    ],
    'message' => 'Datos obtenidos correctamente',
    'timestamp' => date('Y-m-d H:i:s')
]);
?>