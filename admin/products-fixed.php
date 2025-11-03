<?php
/**
 * Gestión de Productos - Página Principal
 * Pinche Supplies - Panel de Administración
 * Fecha: 03 Nov 2025
 * VERSIÓN CORREGIDA - Error HTTP 500 + Paginación visible
 */

// Iniciar sesión y verificar autenticación
session_start();

// Verificar si el usuario está logueado como admin
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_name'])) {
    header('Location: login.php');
    exit;
}

// Definir constantes necesarias si no están definidas
if (!defined('APP_NAME')) {
    define('APP_NAME', 'Pinche Supplies');
}
if (!defined('CRITICAL_STOCK_THRESHOLD')) {
    define('CRITICAL_STOCK_THRESHOLD', 2);
}
if (!defined('LOW_STOCK_THRESHOLD')) {
    define('LOW_STOCK_THRESHOLD', 5);
}
if (!defined('MAX_FILE_SIZE')) {
    define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
}

// Incluir configuración y clases
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../includes/functions.php';

// Inicializar gestor de productos
require_once 'products-manager.php';
$productManager = new ProductManager();

// Variables para mensajes
$success_message = '';
$error_message = '';

// Manejar acciones AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_action'])) {
    header('Content-Type: application/json');
    
    try {
        switch ($_POST['ajax_action']) {
            case 'create_product':
                $result = handleCreateProduct();
                echo json_encode($result);
                exit;
                
            case 'update_product':
                $result = handleUpdateProduct();
                echo json_encode($result);
                exit;
                
            case 'toggle_status':
                $result = handleToggleStatus($_POST['product_id']);
                echo json_encode($result);
                exit;
                
            case 'delete_product':
                $result = handleDeleteProduct($_POST['product_id']);
                echo json_encode($result);
                exit;
                
            case 'get_stats':
                $result = handleGetStats();
                echo json_encode($result);
                exit;
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit;
    }
}

// Función para crear producto
function handleCreateProduct() {
    global $productManager;
    
    try {
        // Validar datos
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $sku = trim($_POST['sku'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        $stock_quantity = intval($_POST['stock_quantity'] ?? 0);
        $category_id = intval($_POST['category_id'] ?? 0);
        
        $errors = [];
        
        if (empty($name)) $errors[] = 'El nombre es requerido';
        if (empty($sku)) $errors[] = 'El SKU es requerido';
        if ($price <= 0) $errors[] = 'El precio debe ser mayor a 0';
        if ($stock_quantity < 0) $errors[] = 'El stock no puede ser negativo';
        if ($category_id <= 0) $errors[] = 'Selecciona una categoría válida';
        
        if (!empty($errors)) {
            return ['success' => false, 'error' => implode(', ', $errors)];
        }
        
        // Crear producto usando el ProductManager
        $result = $productManager->createProduct([
            'name' => $name,
            'description' => $description,
            'sku' => $sku,
            'price' => $price,
            'stock_quantity' => $stock_quantity,
            'category_id' => $category_id,
            'is_active' => 1
        ]);
        
        if ($result['success']) {
            return ['success' => true, 'message' => 'Producto creado exitosamente'];
        } else {
            return ['success' => false, 'error' => $result['error'] ?? 'Error al crear el producto'];
        }
        
    } catch (Exception $e) {
        return ['success' => false, 'error' => 'Error del sistema: ' . $e->getMessage()];
    }
}

// Función para actualizar producto
function handleUpdateProduct() {
    global $productManager;
    
    try {
        $id = intval($_POST['product_id'] ?? 0);
        if ($id <= 0) {
            return ['success' => false, 'error' => 'ID de producto inválido'];
        }
        
        // Validar datos
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $sku = trim($_POST['sku'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        $stock_quantity = intval($_POST['stock_quantity'] ?? 0);
        $category_id = intval($_POST['category_id'] ?? 0);
        
        $errors = [];
        
        if (empty($name)) $errors[] = 'El nombre es requerido';
        if (empty($sku)) $errors[] = 'El SKU es requerido';
        if ($price <= 0) $errors[] = 'El precio debe ser mayor a 0';
        if ($stock_quantity < 0) $errors[] = 'El stock no puede ser negativo';
        if ($category_id <= 0) $errors[] = 'Selecciona una categoría válida';
        
        if (!empty($errors)) {
            return ['success' => false, 'error' => implode(', ', $errors)];
        }
        
        // Actualizar producto
        $result = $productManager->updateProduct($id, [
            'name' => $name,
            'description' => $description,
            'sku' => $sku,
            'price' => $price,
            'stock_quantity' => $stock_quantity,
            'category_id' => $category_id
        ]);
        
        if ($result['success']) {
            return ['success' => true, 'message' => 'Producto actualizado exitosamente'];
        } else {
            return ['success' => false, 'error' => $result['error'] ?? 'Error al actualizar el producto'];
        }
        
    } catch (Exception $e) {
        return ['success' => false, 'error' => 'Error del sistema: ' . $e->getMessage()];
    }
}

// Función para cambiar estado
function handleToggleStatus($productId) {
    global $productManager;
    
    try {
        $productId = intval($productId);
        if ($productId <= 0) {
            return ['success' => false, 'error' => 'ID de producto inválido'];
        }
        
        $result = $productManager->toggleProductStatus($productId);
        
        if ($result['success']) {
            return ['success' => true, 'message' => $result['message']];
        } else {
            return ['success' => false, 'error' => $result['error'] ?? 'Error al cambiar el estado del producto'];
        }
        
    } catch (Exception $e) {
        return ['success' => false, 'error' => 'Error del sistema: ' . $e->getMessage()];
    }
}

// Función para eliminar producto
function handleDeleteProduct($productId) {
    global $productManager;
    
    try {
        $productId = intval($productId);
        if ($productId <= 0) {
            return ['success' => false, 'error' => 'ID de producto inválido'];
        }
        
        $result = $productManager->deleteProduct($productId);
        
        if ($result['success']) {
            return ['success' => true, 'message' => 'Producto eliminado exitosamente'];
        } else {
            return ['success' => false, 'error' => $result['error'] ?? 'Error al eliminar el producto'];
        }
        
    } catch (Exception $e) {
        return ['success' => false, 'error' => 'Error del sistema: ' . $e->getMessage()];
    }
}

// Función para obtener estadísticas
function handleGetStats() {
    global $productManager;
    
    try {
        $stats = $productManager->getProductStats();
        return ['success' => true, 'stats' => $stats];
    } catch (Exception $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

// Gestión de búsqueda y filtros
$search = trim($_GET['search'] ?? '');
$category_filter = trim($_GET['category'] ?? '');
$status_filter = trim($_GET['status'] ?? '');
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 15; // 15 productos por página
$offset = ($page - 1) * $per_page;

try {
    // Obtener productos con filtros usando ProductManager
    $where_conditions = ["p.is_deleted = 0"];
    $params = [];

    if (!empty($search)) {
        $where_conditions[] = "(p.name LIKE :search OR p.description LIKE :search OR p.sku LIKE :search)";
        $params['search'] = "%$search%";
    }

    if (!empty($category_filter) && is_numeric($category_filter)) {
        $where_conditions[] = "p.category_id = :category_id";
        $params['category_id'] = $category_filter;
    }

    if (!empty($status_filter)) {
        $where_conditions[] = "p.is_active = :is_active";
        $params['is_active'] = $status_filter === 'active' ? 1 : 0;
    }

    $where_clause = implode(' AND ', $where_conditions);

    // Query productos con paginación
    $db = Database::getInstance();
    
    $products_query = "
        SELECT p.*, c.name as category_name 
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE $where_clause
        ORDER BY p.created_at DESC
        LIMIT $per_page OFFSET $offset
    ";
    
    $products = $db->fetchAll($products_query, $params);

    // Query conteo para paginación
    $count_query = "
        SELECT COUNT(*) as total
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE $where_clause
    ";
    
    $total_result = $db->fetchOne($count_query, $params);
    $total = intval($total_result['total'] ?? 0);
    $total_pages = max(1, ceil($total / $per_page));

    // Asegurar que la página esté dentro del rango válido
    if ($page > $total_pages && $total_pages > 0) {
        $page = $total_pages;
        $offset = ($page - 1) * $per_page;
        
        // Re-ejecutar query con nueva página
        $products = $db->fetchAll($products_query, $params);
    }

    // Obtener categorías para filtro
    $categories = $productManager->getActiveCategories();

    // Estadísticas del ProductManager
    $stats = $productManager->getProductStats();

} catch (Exception $e) {
    error_log("Error al cargar productos: " . $e->getMessage());
    $products = [];
    $categories = [];
    $total = 0;
    $total_pages = 0;
    $stats = ['total' => 0, 'active' => 0, 'low_stock' => 0, 'total_value' => 0];
    $error_message = 'Error al cargar los datos: ' . $e->getMessage();
}

// Obtener producto para editar si se especifica
$editing_product = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $editing_id = intval($_GET['edit']);
    foreach ($products as $product) {
        if ($product['id'] == $editing_id) {
            $editing_product = $product;
            break;
        }
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Productos - <?= APP_NAME ?></title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- CSS personalizado -->
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #333;
        }

        .main-content {
            margin-left: 260px;
            padding: 20px;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        .content-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-bottom: 20px;
        }

        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }

        .page-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
        }

        .page-header p {
            font-size: 1.1rem;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }

        .content-body {
            padding: 30px;
        }

        /* Estadísticas */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(102, 126, 234, 0.4);
        }

        .stat-card i {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.9;
        }

        .stat-card h3 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .stat-card p {
            font-size: 1rem;
            opacity: 0.9;
        }

        /* Filtros y controles */
        .controls-section {
            background: #f8fafc;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 30px;
            border: 1px solid #e2e8f0;
        }

        .controls-grid {
            display: grid;
            grid-template-columns: 1fr auto auto auto;
            gap: 15px;
            align-items: end;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .form-group label {
            font-weight: 600;
            color: #374151;
            font-size: 0.9rem;
        }

        .form-control {
            padding: 12px 15px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            white-space: nowrap;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: #6b7280;
            color: white;
        }

        .btn-secondary:hover {
            background: #4b5563;
            transform: translateY(-2px);
        }

        .btn-danger {
            background: #ef4444;
            color: white;
        }

        .btn-danger:hover {
            background: #dc2626;
            transform: translateY(-2px);
        }

        .btn-sm {
            padding: 8px 15px;
            font-size: 0.9rem;
        }

        /* Tabla */
        .table-container {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .table-responsive {
            overflow-x: auto;
        }

        .products-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        .products-table th {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            color: #374151;
            padding: 20px 15px;
            text-align: left;
            font-weight: 700;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e5e7eb;
        }

        .products-table td {
            padding: 20px 15px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .products-table tr:hover {
            background: #f8fafc;
        }

        .products-table tr:last-child td {
            border-bottom: none;
        }

        .product-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .product-image {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            object-fit: cover;
            border: 2px solid #e5e7eb;
        }

        .product-details h4 {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 5px;
        }

        .product-sku {
            font-size: 0.8rem;
            color: #6b7280;
        }

        .category-badge {
            background: #e0e7ff;
            color: #3730a3;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .price {
            font-weight: 700;
            font-size: 1.1rem;
            color: #059669;
        }

        .stock-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .stock-high {
            background: #d1fae5;
            color: #065f46;
        }

        .stock-medium {
            background: #fef3c7;
            color: #92400e;
        }

        .stock-low {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-active {
            background: #d1fae5;
            color: #065f46;
        }

        .status-inactive {
            background: #fee2e2;
            color: #991b1b;
        }

        .actions {
            display: flex;
            gap: 8px;
        }

        /* Paginación - VERSIÓN MEJORADA Y MÁS VISIBLE */
        .pagination-container {
            margin: 30px 0;
            padding: 0 30px 30px;
            background: #f8fafc;
            border-radius: 15px;
            border: 1px solid #e2e8f0;
        }

        .pagination-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e5e7eb;
        }

        .pagination-info {
            color: #6b7280;
            font-size: 0.9rem;
        }

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .pagination a, .pagination span {
            padding: 12px 16px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            min-width: 45px;
            text-align: center;
        }

        .pagination a {
            background: white;
            color: #667eea;
            border-color: #e5e7eb;
        }

        .pagination a:hover {
            background: #667eea;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        .pagination .current {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-color: #667eea;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .pagination .disabled {
            background: #f3f4f6;
            color: #9ca3af;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .pagination .disabled:hover {
            background: #f3f4f6;
            color: #9ca3af;
            transform: none;
            box-shadow: none;
        }

        .pagination-ellipsis {
            color: #6b7280;
            padding: 12px 8px;
        }

        /* Mensajes */
        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border-left-color: #10b981;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border-left-color: #ef4444;
        }

        /* Modal para formulario de producto */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 2000;
            animation: fadeIn 0.3s ease;
        }

        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            border-radius: 15px;
            padding: 30px;
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            animation: slideUp 0.3s ease;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e5e7eb;
        }

        .modal-header h3 {
            margin: 0;
            color: #1f2937;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #6b7280;
            padding: 5px;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .modal-close:hover {
            background: #f3f4f6;
            color: #374151;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-row .form-group {
            margin-bottom: 20px;
        }

        .form-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideUp {
            from { transform: translateY(50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 10px;
            }

            .controls-grid {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .products-table {
                font-size: 0.9rem;
            }

            .products-table th,
            .products-table td {
                padding: 10px 8px;
            }

            .product-info {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }

            .actions {
                flex-direction: column;
            }

            .pagination-container {
                padding: 0 15px 20px;
            }

            .pagination-header {
                flex-direction: column;
                gap: 10px;
                align-items: center;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .modal-content {
                width: 95%;
                padding: 20px;
            }
        }

        .empty-state {
            text-align: center;
            padding: 60px 30px;
            color: #6b7280;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .empty-state h3 {
            font-size: 1.5rem;
            margin-bottom: 10px;
            color: #374151;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <?php include 'includes/sidebar.php'; ?>

    <!-- Toast para notificaciones -->
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 2000;">
        <div id="notificationToast" class="toast" role="alert">
            <div class="toast-header">
                <i class="fas fa-info-circle text-primary me-2"></i>
                <strong class="me-auto">Notificación</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body" id="toastMessage"></div>
        </div>
    </div>

    <!-- Contenido principal -->
    <div class="main-content">
        <div class="content-container">
            <!-- Header -->
            <div class="page-header">
                <h1><i class="fas fa-box"></i> Gestión de Productos</h1>
                <p>Administra el catálogo de productos de tu e-commerce</p>
            </div>

            <div class="content-body">
                <!-- Mensajes de feedback -->
                <?php if ($success_message): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <span><?= htmlspecialchars($success_message) ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($error_message): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span><?= htmlspecialchars($error_message) ?></span>
                    </div>
                <?php endif; ?>

                <!-- Estadísticas -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <i class="fas fa-boxes"></i>
                        <h3><?= $stats['total'] ?></h3>
                        <p>Productos Totales</p>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-check-circle"></i>
                        <h3><?= $stats['active'] ?></h3>
                        <p>Productos Activos</p>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-exclamation-triangle"></i>
                        <h3><?= $stats['low_stock'] ?></h3>
                        <p>Stock Bajo</p>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-dollar-sign"></i>
                        <h3>€<?= number_format($stats['total_value'], 0, ',', '.') ?></h3>
                        <p>Valor Inventario</p>
                    </div>
                </div>

                <!-- Controles y Filtros -->
                <div class="controls-section">
                    <form method="GET" id="filterForm">
                        <div class="controls-grid">
                            <div class="form-group">
                                <label for="search">Buscar productos</label>
                                <input type="text" id="search" name="search" class="form-control" 
                                       placeholder="Nombre, descripción o SKU..." 
                                       value="<?= htmlspecialchars($search) ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="category">Categoría</label>
                                <select id="category" name="category" class="form-control">
                                    <option value="">Todas las categorías</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?= $category['id'] ?>" 
                                                <?= $category_filter == $category['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($category['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="status">Estado</label>
                                <select id="status" name="status" class="form-control">
                                    <option value="">Todos los estados</option>
                                    <option value="active" <?= $status_filter === 'active' ? 'selected' : '' ?>>Activo</option>
                                    <option value="inactive" <?= $status_filter === 'inactive' ? 'selected' : '' ?>>Inactivo</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Filtrar
                                </button>
                            </div>
                        </div>
                        
                        <input type="hidden" name="page" value="1">
                    </form>
                </div>

                <!-- Acciones -->
                <div style="margin-bottom: 30px;">
                    <button type="button" class="btn btn-primary" onclick="openProductModal()">
                        <i class="fas fa-plus"></i> Agregar Nuevo Producto
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="clearFilters()">
                        <i class="fas fa-undo"></i> Limpiar Filtros
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="refreshStats()">
                        <i class="fas fa-sync-alt"></i> Actualizar Estadísticas
                    </button>
                </div>

                <!-- Tabla productos -->
                <div class="table-container">
                    <div class="table-responsive">
                        <?php if (empty($products)): ?>
                            <div class="empty-state">
                                <i class="fas fa-box-open"></i>
                                <h3>No se encontraron productos</h3>
                                <p>Comienza agregando tu primer producto</p>
                                <button type="button" class="btn btn-primary" onclick="openProductModal()" style="margin-top: 20px;">
                                    <i class="fas fa-plus"></i> Agregar Producto
                                </button>
                            </div>
                        <?php else: ?>
                            <table class="products-table">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Categoría</th>
                                        <th>Precio</th>
                                        <th>Stock</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($products as $product): ?>
                                        <tr>
                                            <td>
                                                <div class="product-info">
                                                    <?php if ($product['image']): ?>
                                                        <img src="../uploads/products/<?= htmlspecialchars($product['image']) ?>" 
                                                             alt="<?= htmlspecialchars($product['name']) ?>" 
                                                             class="product-image">
                                                    <?php else: ?>
                                                        <div class="product-image" style="background: #f3f4f6; display: flex; align-items: center; justify-content: center;">
                                                            <i class="fas fa-image" style="color: #9ca3af; font-size: 1.5rem;"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div class="product-details">
                                                        <h4><?= htmlspecialchars($product['name']) ?></h4>
                                                        <div class="product-sku">SKU: <?= htmlspecialchars($product['sku']) ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="category-badge">
                                                    <?= htmlspecialchars($product['category_name'] ?: 'Sin categoría') ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="price">€<?= number_format($product['price'], 2, ',', '.') ?></div>
                                            </td>
                                            <td>
                                                <?php 
                                                $stock_class = '';
                                                if ($product['stock_quantity'] <= CRITICAL_STOCK_THRESHOLD) {
                                                    $stock_class = 'stock-low';
                                                } elseif ($product['stock_quantity'] <= LOW_STOCK_THRESHOLD) {
                                                    $stock_class = 'stock-medium';
                                                } else {
                                                    $stock_class = 'stock-high';
                                                }
                                                ?>
                                                <span class="stock-badge <?= $stock_class ?>">
                                                    <?= $product['stock_quantity'] ?> unidades
                                                </span>
                                            </td>
                                            <td>
                                                <span class="status-badge status-<?= $product['is_active'] ? 'active' : 'inactive' ?>">
                                                    <?= $product['is_active'] ? 'Activo' : 'Inactivo' ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="actions">
                                                    <button type="button" class="btn btn-sm btn-primary" 
                                                            onclick="editProduct(<?= $product['id'] ?>)">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-secondary" 
                                                            onclick="toggleProductStatus(<?= $product['id'] ?>)">
                                                        <i class="fas <?= $product['is_active'] ? 'fa-eye-slash' : 'fa-eye' ?>"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-danger" 
                                                            onclick="deleteProduct(<?= $product['id'] ?>, '<?= htmlspecialchars($product['name']) ?>')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Paginación mejorada y visible -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination-container">
                        <div class="pagination-header">
                            <div class="pagination-info">
                                Mostrando <?= (($page - 1) * $per_page + 1) ?>-<?= min($page * $per_page, $total) ?> de <?= $total ?> productos
                            </div>
                            <div class="pagination-info">
                                Página <?= $page ?> de <?= $total_pages ?>
                            </div>
                        </div>
                        
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="?page=1&search=<?= urlencode($search) ?>&category=<?= urlencode($category_filter) ?>&status=<?= urlencode($status_filter) ?>" 
                                   title="Primera página">
                                    <i class="fas fa-angle-double-left"></i>
                                </a>
                                <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($category_filter) ?>&status=<?= urlencode($status_filter) ?>" 
                                   title="Página anterior">
                                    <i class="fas fa-angle-left"></i> Anterior
                                </a>
                            <?php endif; ?>

                            <?php 
                            $start_page = max(1, $page - 2);
                            $end_page = min($total_pages, $page + 2);
                            
                            // Mostrar primera página si hay páginas ocultas
                            if ($start_page > 1): ?>
                                <a href="?page=1&search=<?= urlencode($search) ?>&category=<?= urlencode($category_filter) ?>&status=<?= urlencode($status_filter) ?>">1</a>
                                <?php if ($start_page > 2): ?>
                                    <span class="pagination-ellipsis">...</span>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                                <?php if ($i == $page): ?>
                                    <span class="current"><?= $i ?></span>
                                <?php else: ?>
                                    <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($category_filter) ?>&status=<?= urlencode($status_filter) ?>">
                                        <?= $i ?>
                                    </a>
                                <?php endif; ?>
                            <?php endfor; ?>

                            <?php 
                            // Mostrar última página si hay páginas ocultas
                            if ($end_page < $total_pages): ?>
                                <?php if ($end_page < $total_pages - 1): ?>
                                    <span class="pagination-ellipsis">...</span>
                                <?php endif; ?>
                                <a href="?page=<?= $total_pages ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($category_filter) ?>&status=<?= urlencode($status_filter) ?>">
                                    <?= $total_pages ?>
                                </a>
                            <?php endif; ?>

                            <?php if ($page < $total_pages): ?>
                                <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($category_filter) ?>&status=<?= urlencode($status_filter) ?>" 
                                   title="Página siguiente">
                                    Siguiente <i class="fas fa-angle-right"></i>
                                </a>
                                <a href="?page=<?= $total_pages ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($category_filter) ?>&status=<?= urlencode($status_filter) ?>" 
                                   title="Última página">
                                    <i class="fas fa-angle-double-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal para formulario de producto -->
    <div id="productModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Agregar Producto</h3>
                <button type="button" class="modal-close" onclick="closeProductModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="productForm">
                <input type="hidden" id="productId" name="product_id" value="">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Nombre del Producto *</label>
                        <input type="text" id="name" name="name" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="sku">SKU *</label>
                        <input type="text" id="sku" name="sku" class="form-control" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="description">Descripción</label>
                    <textarea id="description" name="description" class="form-control" rows="3"></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="price">Precio *</label>
                        <input type="number" id="price" name="price" class="form-control" step="0.01" min="0" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="stock_quantity">Stock *</label>
                        <input type="number" id="stock_quantity" name="stock_quantity" class="form-control" min="0" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="category_id">Categoría *</label>
                    <select id="category_id" name="category_id" class="form-control" required>
                        <option value="">Selecciona una categoría</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['id'] ?>">
                                <?= htmlspecialchars($category['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeProductModal()">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        Guardar Producto
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        let currentProductId = null;
        let isSubmitting = false;

        // Función para mostrar notificaciones
        function showNotification(message, type = 'info') {
            const toast = document.getElementById('notificationToast');
            const toastMessage = document.getElementById('toastMessage');
            const toastIcon = toast.querySelector('.fa-info-circle');
            
            // Remover clases anteriores
            toast.classList.remove('bg-success', 'bg-danger', 'bg-warning', 'bg-info');
            toastIcon.classList.remove('fa-check-circle', 'fa-exclamation-triangle', 'fa-info-circle');
            
            // Aplicar nuevo estilo
            switch(type) {
                case 'success':
                    toast.classList.add('bg-success', 'text-white');
                    toastIcon.classList.add('fa-check-circle');
                    break;
                case 'error':
                    toast.classList.add('bg-danger', 'text-white');
                    toastIcon.classList.add('fa-exclamation-triangle');
                    break;
                case 'warning':
                    toast.classList.add('bg-warning', 'text-dark');
                    toastIcon.classList.add('fa-exclamation-triangle');
                    break;
                default:
                    toast.classList.add('bg-info', 'text-white');
                    toastIcon.classList.add('fa-info-circle');
            }
            
            toastMessage.textContent = message;
            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();
        }

        // Función para abrir modal de producto
        function openProductModal(productId = null) {
            currentProductId = productId;
            const modal = document.getElementById('productModal');
            const modalTitle = document.getElementById('modalTitle');
            const productForm = document.getElementById('productForm');
            const submitBtn = document.getElementById('submitBtn');
            
            // Reset form
            productForm.reset();
            document.getElementById('productId').value = '';
            
            if (productId) {
                modalTitle.textContent = 'Editar Producto';
                submitBtn.textContent = 'Actualizar Producto';
                loadProductData(productId);
            } else {
                modalTitle.textContent = 'Agregar Producto';
                submitBtn.textContent = 'Guardar Producto';
            }
            
            modal.classList.add('show');
        }

        // Función para cerrar modal
        function closeProductModal() {
            const modal = document.getElementById('productModal');
            modal.classList.remove('show');
            currentProductId = null;
        }

        // Función para cargar datos del producto
        function loadProductData(productId) {
            // Buscar el producto en la tabla actual
            const rows = document.querySelectorAll('.products-table tbody tr');
            
            for (let row of rows) {
                const editButton = row.querySelector('button[onclick*="' + productId + '"]');
                if (editButton) {
                    const productName = row.querySelector('.product-details h4').textContent;
                    const productSku = row.querySelector('.product-sku').textContent.replace('SKU: ', '');
                    const categoryBadge = row.querySelector('.category-badge');
                    const priceText = row.querySelector('.price').textContent.replace('€', '').replace(',', '.');
                    const stockText = row.querySelector('.stock-badge').textContent.replace(' unidades', '');
                    
                    document.getElementById('name').value = productName;
                    document.getElementById('sku').value = productSku;
                    document.getElementById('price').value = parseFloat(priceText);
                    document.getElementById('stock_quantity').value = parseInt(stockText);
                    
                    break;
                }
            }
        }

        // Editar producto
        function editProduct(productId) {
            openProductModal(productId);
        }

        // Toggle status producto
        function toggleProductStatus(productId) {
            if (!productId) return;
            
            fetch('products-fixed.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'ajax_action=toggle_status&product_id=' + productId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showNotification(data.error || 'Error al cambiar estado', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Error de conexión', 'error');
            });
        }

        // Eliminar producto
        function deleteProduct(productId, productName) {
            if (!productId) return;
            
            if (confirm(`¿Estás seguro de eliminar el producto "${productName}"?`)) {
                fetch('products-fixed.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'ajax_action=delete_product&product_id=' + productId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification(data.message, 'success');
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showNotification(data.error || 'Error al eliminar', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Error de conexión', 'error');
                });
            }
        }

        // Limpiar filtros
        function clearFilters() {
            window.location.href = 'products-fixed.php';
        }

        // Actualizar estadísticas
        function refreshStats() {
            fetch('products-fixed.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'ajax_action=get_stats'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.stats) {
                    const stats = data.stats;
                    document.querySelector('.stats-grid .stat-card:first-child h3').textContent = stats.total || 0;
                    document.querySelector('.stats-grid .stat-card:nth-child(2) h3').textContent = stats.active || 0;
                    document.querySelector('.stats-grid .stat-card:nth-child(3) h3').textContent = stats.low_stock || 0;
                    document.querySelector('.stats-grid .stat-card:last-child h3').textContent = '€' + (stats.total_value || 0).toLocaleString('es-ES');
                    showNotification('Estadísticas actualizadas', 'success');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Error al actualizar estadísticas', 'error');
            });
        }

        // Manejar envío del formulario
        document.getElementById('productForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (isSubmitting) return;
            isSubmitting = true;
            
            const submitBtn = document.getElementById('submitBtn');
            const originalText = submitBtn.textContent;
            submitBtn.textContent = 'Guardando...';
            submitBtn.disabled = true;
            
            const formData = new FormData(this);
            const action = currentProductId ? 'update_product' : 'create_product';
            formData.append('ajax_action', action);
            
            if (currentProductId) {
                formData.append('product_id', currentProductId);
            }
            
            fetch('products-fixed.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                isSubmitting = false;
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
                
                if (data.success) {
                    showNotification(data.message, 'success');
                    closeProductModal();
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showNotification(data.error || 'Error al guardar', 'error');
                }
            })
            .catch(error => {
                isSubmitting = false;
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
                console.error('Error:', error);
                showNotification('Error de conexión', 'error');
            });
        });

        // Cerrar modal al hacer clic fuera
        document.getElementById('productModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeProductModal();
            }
        });

        // Cerrar modal con Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeProductModal();
            }
        });

        // Auto-generar SKU si está vacío
        document.getElementById('name').addEventListener('blur', function() {
            const name = this.value.trim();
            const skuField = document.getElementById('sku');
            
            if (name && !skuField.value) {
                const sku = 'PS-' + name.toUpperCase()
                    .replace(/[^A-Z0-9]/g, '')
                    .substring(0, 8) + '-' + Math.random().toString(36).substring(2, 6).toUpperCase();
                
                skuField.value = sku;
            }
        });
    </script>
</body>
</html>