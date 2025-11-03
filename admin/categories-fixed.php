<?php
/**
 * ADMIN - GESTIÓN COMPLETA DE CATEGORÍAS
 * Página completa de gestión de categorías con CRUD integrado y paginación visible
 * Versión: 03 Nov 2025 - 22:14 - CON PAGINACIÓN MEJORADA
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Autenticación
$auth = new Auth();
$auth->requireLogin();

// Conexión base de datos
$db = Database::getInstance();

$message = '';
$error = '';
$success = false;

// Procesar acciones AJAX/Formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        switch ($action) {
            case 'create':
            case 'edit':
                $name = trim($_POST['name'] ?? '');
                $description = trim($_POST['description'] ?? '');
                $is_active = isset($_POST['is_active']) ? 1 : 0;
                $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
                
                // Validaciones
                if (empty($name)) {
                    $error = 'El nombre de la categoría es obligatorio.';
                } elseif (strlen($name) < 2) {
                    $error = 'El nombre debe tener al menos 2 caracteres.';
                } elseif (strlen($name) > 100) {
                    $error = 'El nombre no puede exceder 100 caracteres.';
                } elseif (strlen($description) > 500) {
                    $error = 'La descripción no puede exceder 500 caracteres.';
                } else {
                    // Verificar que no exista otra categoría con el mismo nombre
                    $query = "SELECT id FROM categories WHERE name = ? AND is_deleted = 0";
                    $params = [$name];
                    
                    if ($action === 'edit' && $category_id > 0) {
                        $query .= " AND id != ?";
                        $params[] = $category_id;
                    }
                    
                    $existing = $db->fetchOne($query, $params);
                    
                    if ($existing) {
                        $error = 'Ya existe otra categoría con ese nombre.';
                    } else {
                        if ($action === 'create') {
                            // Crear nueva categoría
                            $db->execute(
                                "INSERT INTO categories (name, description, is_active, created_at) VALUES (?, ?, ?, NOW())",
                                [$name, $description, $is_active]
                            );
                            $message = 'Categoría creada correctamente.';
                            $success = true;
                        } elseif ($action === 'edit' && $category_id > 0) {
                            // Actualizar categoría existente
                            $db->execute(
                                "UPDATE categories SET name = ?, description = ?, is_active = ?, updated_at = NOW() WHERE id = ?",
                                [$name, $description, $is_active, $category_id]
                            );
                            $message = 'Categoría actualizada correctamente.';
                            $success = true;
                        }
                    }
                }
                break;
                
            case 'toggle_status':
                $category_id = (int)($_POST['category_id'] ?? 0);
                $new_status = (int)($_POST['new_status'] ?? 0);
                
                if ($category_id > 0) {
                    $db->execute(
                        "UPDATE categories SET is_active = ? WHERE id = ?",
                        [$new_status, $category_id]
                    );
                    $response = ['success' => true, 'message' => 'Estado de la categoría actualizado correctamente.'];
                } else {
                    $response = ['success' => false, 'message' => 'ID de categoría inválido.'];
                }
                
                if (isset($_POST['ajax']) && $_POST['ajax'] == '1') {
                    header('Content-Type: application/json');
                    echo json_encode($response);
                    exit;
                }
                break;
                
            case 'delete':
                $category_id = (int)($_POST['category_id'] ?? 0);
                
                if ($category_id > 0) {
                    // Verificar si hay productos asociados
                    $products_count = $db->count('products', 'category_id = ? AND is_active = 1', [$category_id]);
                    
                    if ($products_count > 0) {
                        $response = ['success' => false, 'message' => 'No se puede eliminar la categoría porque tiene productos asociados.'];
                    } else {
                        $db->execute("UPDATE categories SET is_deleted = 1 WHERE id = ?", [$category_id]);
                        $response = ['success' => true, 'message' => 'Categoría eliminada correctamente.'];
                    }
                } else {
                    $response = ['success' => false, 'message' => 'ID de categoría inválido.'];
                }
                
                if (isset($_POST['ajax']) && $_POST['ajax'] == '1') {
                    header('Content-Type: application/json');
                    echo json_encode($response);
                    exit;
                }
                break;
        }
        
        // Si no es AJAX, actualizar message/error para mostrar en la página
        if (!isset($response)) {
            if ($success) {
                $message = $message;
            } else {
                $error = $error;
            }
        }
        
    } catch (Exception $e) {
        error_log("Error en categories.php: " . $e->getMessage());
        $error = 'Error al procesar la solicitud: ' . $e->getMessage();
        
        if (isset($_POST['ajax']) && $_POST['ajax'] == '1') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $error]);
            exit;
        }
    }
}

// Obtener parámetros de paginación y filtros
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

$search = trim($_GET['search'] ?? '');
$status_filter = $_GET['status'] ?? '';

// Construir query base con filtros
$where_conditions = ["c.is_deleted = 0"];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(c.name LIKE ? OR c.description LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
}

if ($status_filter !== '') {
    $where_conditions[] = "c.is_active = ?";
    $params[] = $status_filter === '1' ? 1 : 0;
}

$where_clause = implode(' AND ', $where_conditions);

// Query para obtener categorías con paginación
$query = "SELECT 
            c.*,
            COUNT(p.id) as products_count
          FROM categories c
          LEFT JOIN products p ON c.id = p.category_id AND p.is_active = 1
          WHERE $where_clause
          GROUP BY c.id
          ORDER BY c.name
          LIMIT $per_page OFFSET $offset";

$categories = $db->fetchAll($query, $params);

// Query para contar total de registros (para paginación)
$count_query = "SELECT COUNT(DISTINCT c.id) as total 
                FROM categories c 
                WHERE $where_clause";
$total = $db->fetchOne($count_query, $params)['total'] ?? 0;
$total_pages = ceil($total / $per_page);

// Calcular información para mostrar en la paginación
$start_item = ($total > 0) ? ($offset + 1) : 0;
$end_item = min($offset + $per_page, $total);

// Obtener estadísticas generales
$total_categories = $db->count('categories', 'is_deleted = 0');
$active_categories = $db->count('categories', 'is_deleted = 0 AND is_active = 1');
$inactive_categories = $total_categories - $active_categories;

// Obtener categoría para editar si se especifica
$editing_category = null;
$edit_id = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
if ($edit_id > 0) {
    $editing_category = $db->fetchOne("SELECT * FROM categories WHERE id = ? AND is_deleted = 0", [$edit_id]);
    if (!$editing_category) {
        $error = 'Categoría no encontrada para editar.';
        $edit_id = 0;
    }
}

// Si no hay categoría para editar, preparar valores por defecto
if (!$editing_category) {
    $editing_category = [
        'id' => 0,
        'name' => '',
        'description' => '',
        'is_active' => 1
    ];
}

include __DIR__ . '/includes/sidebar.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Categorías - Pinche Supplies Admin</title>
    
    <!-- Font Awesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- CSS personalizado -->
    <style>
        .main-content {
            margin-left: 260px;
            padding: 20px;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .content-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-bottom: 20px;
        }

        .page-header-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            position: relative;
            overflow: hidden;
        }

        .page-header-section::before {
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

        .page-header-section h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
        }

        .page-header-section p {
            font-size: 1.1rem;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }

        .content-body {
            padding: 30px;
        }

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

        .controls-section {
            background: #f8fafc;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 30px;
            border: 1px solid #e2e8f0;
        }

        .controls-grid {
            display: grid;
            grid-template-columns: 2fr 1fr auto;
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

        .btn-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(16, 185, 129, 0.4);
        }

        .btn-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(239, 68, 68, 0.4);
        }

        .btn-sm {
            padding: 8px 15px;
            font-size: 0.9rem;
        }

        /* Formulario */
        .form-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .form-section {
            margin-bottom: 25px;
        }

        .form-section h5 {
            color: #495057;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 8px;
            margin-bottom: 20px;
        }

        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 8px;
        }

        .char-counter {
            font-size: 0.875rem;
            color: #6c757d;
            text-align: right;
            margin-top: 5px;
        }

        .checkbox-wrapper {
            background-color: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 6px;
            padding: 15px;
            transition: border-color 0.2s ease;
        }

        .checkbox-wrapper:hover {
            border-color: #007bff;
        }

        .checkbox-wrapper input[type="checkbox"] {
            transform: scale(1.2);
            margin-right: 10px;
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

        .categories-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        .categories-table th {
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

        .categories-table td {
            padding: 20px 15px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .categories-table tr:hover {
            background: #f8fafc;
        }

        .categories-table tr:last-child td {
            border-bottom: none;
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

        .products-count {
            display: inline-flex;
            align-items: center;
            background-color: #e9ecef;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.875rem;
            color: #6c757d;
        }

        .actions {
            display: flex;
            gap: 8px;
        }

        /* Paginación MEJORADA */
        .pagination-section {
            margin: 30px 0;
            padding: 0 30px 30px;
        }

        .pagination-info {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border-radius: 10px;
            padding: 15px 20px;
            margin-bottom: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-weight: 600;
            color: #374151;
            border: 1px solid #e5e7eb;
        }

        .pagination-info i {
            margin-right: 8px;
            color: #667eea;
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
            font-size: 0.95rem;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            min-width: 48px;
            justify-content: center;
        }

        .pagination a {
            background: white;
            color: #667eea;
            border: 2px solid #e5e7eb;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .pagination a:hover {
            background: #667eea;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
            border-color: #667eea;
        }

        .pagination .current {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: 2px solid #667eea;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .pagination .disabled {
            opacity: 0.4;
            cursor: not-allowed;
            background: #f9fafb;
            color: #9ca3af;
            border-color: #e5e7eb;
            box-shadow: none;
        }

        .pagination .disabled:hover {
            transform: none;
            background: #f9fafb;
            color: #9ca3af;
        }

        .pagination-ellipsis {
            padding: 12px 8px;
            color: #6b7280;
            font-weight: 500;
        }

        /* Responsive pagination */
        @media (max-width: 768px) {
            .pagination-section {
                padding: 0 15px 20px;
            }

            .pagination-info {
                font-size: 0.9rem;
                padding: 12px 15px;
            }

            .pagination {
                gap: 5px;
            }

            .pagination a, .pagination span {
                padding: 10px 12px;
                font-size: 0.9rem;
                min-width: 40px;
            }
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

        /* Empty state */
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

            .categories-table {
                font-size: 0.9rem;
            }

            .categories-table th,
            .categories-table td {
                padding: 10px 8px;
            }

            .actions {
                flex-direction: column;
            }

            .page-header {
                flex-direction: column;
                align-items: stretch;
            }
        }

        /* Animaciones */
        .fade-in {
            animation: fadeIn 0.3s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Toast notifications */
        .toast-container {
            z-index: 2000;
        }

        .toast {
            border-radius: 10px;
        }
    </style>
</head>
<body>

    <!-- Toast para notificaciones -->
    <div class="toast-container position-fixed top-0 end-0 p-3">
        <div id="notificationToast" class="toast" role="alert">
            <div class="toast-header">
                <i class="fas fa-info-circle text-primary me-2"></i>
                <strong class="me-auto">Notifica</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body" id="toastMessage"></div>
        </div>
    </div>

    <div class="main-content">
        <div class="content-container">
            <!-- Header -->
            <div class="page-header-section">
                <h1><i class="fas fa-tags"></i> Gestión de Categorías</h1>
                <p>Administra las categorías de tus productos</p>
            </div>

            <div class="content-body">
                <!-- Mensajes -->
                <?php if ($message): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <span><?= htmlspecialchars($message) ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span><?= htmlspecialchars($error) ?></span>
                    </div>
                <?php endif; ?>

                <!-- Estadísticas -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <i class="fas fa-folder"></i>
                        <h3><?= $total_categories ?></h3>
                        <p>Total Categorías</p>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-check-circle"></i>
                        <h3><?= $active_categories ?></h3>
                        <p>Activas</p>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-pause-circle"></i>
                        <h3><?= $inactive_categories ?></h3>
                        <p>Inactivas</p>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-box"></i>
                        <h3>
                            <?php
                            $total_products_in_categories = array_sum(array_column($categories, 'products_count'));
                            echo $total_products_in_categories;
                            ?>
                        </h3>
                        <p>Productos en Categorías</p>
                    </div>
                </div>

                <?php if (!$edit_id): ?>
                    <!-- Controles y Filtros -->
                    <div class="controls-section">
                        <form method="GET" id="filterForm">
                            <div class="controls-grid">
                                <div class="form-group">
                                    <label for="search">Buscar categorías</label>
                                    <input type="text" id="search" name="search" class="form-control" 
                                           placeholder="Nombre o descripción..." 
                                           value="<?= htmlspecialchars($search) ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="status">Estado</label>
                                    <select id="status" name="status" class="form-control">
                                        <option value="">Todos los estados</option>
                                        <option value="1" <?= $status_filter === '1' ? 'selected' : '' ?>>Solo activas</option>
                                        <option value="0" <?= $status_filter === '0' ? 'selected' : '' ?>>Solo inactivas</option>
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
                <?php endif; ?>

                <!-- Acciones -->
                <div style="margin-bottom: 30px;">
                    <?php if ($edit_id): ?>
                        <a href="categories.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Volver a Lista
                        </a>
                        <button type="button" class="btn btn-secondary" onclick="cancelEdit()">
                            <i class="fas fa-times"></i> Cancelar Edición
                        </button>
                    <?php else: ?>
                        <button type="button" class="btn btn-primary" onclick="toggleForm()">
                            <i class="fas fa-plus"></i> Nueva Categoría
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="clearFilters()">
                            <i class="fas fa-undo"></i> Limpiar Filtros
                        </button>
                    <?php endif; ?>
                </div>

                <!-- Formulario de Categoría -->
                <div id="categoryFormContainer" class="form-card" style="<?= ($edit_id || ($_SERVER['REQUEST_METHOD'] === 'POST' && ($action === 'create' || $action === 'edit') && !$success)) ? '' : 'display: none;' ?>">
                    <h5>
                        <i class="fas fa-<?= $edit_id ? 'edit' : 'plus' ?>"></i> 
                        <?= $edit_id ? 'Editar Categoría' : 'Nueva Categoría' ?>
                    </h5>
                    
                    <form method="POST" id="categoryForm">
                        <input type="hidden" name="action" value="<?= $edit_id ? 'edit' : 'create' ?>">
                        <?php if ($edit_id): ?>
                            <input type="hidden" name="category_id" value="<?= $edit_id ?>">
                        <?php endif; ?>
                        
                        <div class="form-section">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">
                                            <i class="fas fa-tag"></i> Nombre de la Categoría *
                                        </label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="name" 
                                               name="name" 
                                               value="<?= htmlspecialchars($editing_category['name']) ?>"
                                               placeholder="Ej: Electrónicos, Ropa, Hogar..."
                                               maxlength="100"
                                               required>
                                        <div class="char-counter">
                                            <span id="nameCounter"><?= strlen($editing_category['name']) ?></span>/100 caracteres
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="fas fa-toggle-on"></i> Estado
                                        </label>
                                        <div class="checkbox-wrapper">
                                            <div class="form-check">
                                                <input class="form-check-input" 
                                                       type="checkbox" 
                                                       id="is_active" 
                                                       name="is_active" 
                                                       value="1"
                                                       <?= $editing_category['is_active'] ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="is_active">
                                                    <strong>Categoría Activa</strong>
                                                    <br>
                                                    <small class="text-muted">
                                                        Las categorías activas aparecen en el catálogo
                                                    </small>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">
                                    <i class="fas fa-align-left"></i> Descripción
                                </label>
                                <textarea class="form-control" 
                                          id="description" 
                                          name="description" 
                                          rows="4"
                                          maxlength="500"
                                          placeholder="Describe brevemente esta categoría..."><?= htmlspecialchars($editing_category['description']) ?></textarea>
                                <div class="char-counter">
                                    <span id="descCounter"><?= strlen($editing_category['description']) ?></span>/500 caracteres
                                </div>
                            </div>
                        </div>

                        <div class="form-actions">
                            <div class="row">
                                <div class="col-md-6">
                                    <?php if ($edit_id): ?>
                                        <a href="categories.php" class="btn btn-secondary">
                                            <i class="fas fa-times"></i> Cancelar
                                        </a>
                                    <?php else: ?>
                                        <button type="button" class="btn btn-secondary" onclick="toggleForm()">
                                            <i class="fas fa-times"></i> Cancelar
                                        </button>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6 text-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-<?= $edit_id ? 'save' : 'plus' ?>"></i> 
                                        <?= $edit_id ? 'Guardar Cambios' : 'Crear Categoría' ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Tabla de categorías -->
                <div class="table-container">
                    <div class="table-responsive">
                        <?php if (empty($categories) && !$search && !$status_filter): ?>
                            <div class="empty-state">
                                <i class="fas fa-tags"></i>
                                <h3>No hay categorías registradas</h3>
                                <p>Comienza creando tu primera categoría para organizar tus productos.</p>
                                <button type="button" class="btn btn-primary" onclick="toggleForm()">
                                    <i class="fas fa-plus"></i> Crear Primera Categoría
                                </button>
                            </div>
                        <?php elseif (empty($categories)): ?>
                            <div class="empty-state">
                                <i class="fas fa-search"></i>
                                <h3>No se encontraron categorías</h3>
                                <p>Intenta ajustar tus filtros de búsqueda.</p>
                            </div>
                        <?php else: ?>
                            <table class="categories-table">
                                <thead>
                                    <tr>
                                        <th>
                                            <i class="fas fa-hashtag"></i> ID
                                        </th>
                                        <th>
                                            <i class="fas fa-tag"></i> Nombre de la Categoría
                                        </th>
                                        <th>
                                            <i class="fas fa-align-left"></i> Descripción
                                        </th>
                                        <th class="text-center">
                                            <i class="fas fa-box"></i> Productos
                                        </th>
                                        <th class="text-center">
                                            <i class="fas fa-toggle-on"></i> Estado
                                        </th>
                                        <th class="text-center">
                                            <i class="fas fa-calendar"></i> Creada
                                        </th>
                                        <th class="text-center">
                                            <i class="fas fa-cogs"></i> Acciones
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($categories as $category): ?>
                                        <tr class="fade-in">
                                            <td>
                                                <span class="badge bg-light text-dark"><?= $category['id'] ?></span>
                                            </td>
                                            <td>
                                                <strong><?= htmlspecialchars($category['name']) ?></strong>
                                            </td>
                                            <td>
                                                <span class="text-muted">
                                                    <?= htmlspecialchars($category['description']) ?: 'Sin descripción' ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="products-count">
                                                    <i class="fas fa-box mr-1"></i>
                                                    <?= $category['products_count'] ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="status-badge <?= $category['is_active'] ? 'status-active' : 'status-inactive' ?>">
                                                    <i class="fas fa-<?= $category['is_active'] ? 'check' : 'pause' ?>"></i>
                                                    <?= $category['is_active'] ? 'Activa' : 'Inactiva' ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <small class="text-muted">
                                                    <?= date('d/m/Y', strtotime($category['created_at'])) ?>
                                                </small>
                                            </td>
                                            <td class="text-center">
                                                <div class="actions">
                                                    <button type="button" class="btn btn-sm btn-primary" 
                                                            onclick="editCategory(<?= $category['id'] ?>)">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-secondary" 
                                                            onclick="toggleStatus(<?= $category['id'] ?>, <?= $category['is_active'] ?>)">
                                                        <i class="fas fa-<?= $category['is_active'] ? 'eye-slash' : 'eye' ?>"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-danger" 
                                                            onclick="deleteCategory(<?= $category['id'] ?>, '<?= htmlspecialchars($category['name']) ?>', <?= $category['products_count'] ?>)">
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

                <!-- Paginación MEJORADA -->
                <?php if ($total_pages > 1 && !$edit_id): ?>
                    <div class="pagination-section">
                        <!-- Información de paginación -->
                        <div class="pagination-info">
                            <i class="fas fa-list"></i>
                            <?php if ($total > 0): ?>
                                Mostrando <?= $start_item ?>-<?= $end_item ?> de <?= $total ?> categorías
                            <?php else: ?>
                                No hay categorías para mostrar
                            <?php endif; ?>
                        </div>

                        <!-- Controles de paginación -->
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="?page=1&search=<?= urlencode($search) ?>&status=<?= urlencode($status_filter) ?>" 
                                   title="Primera página">
                                    <i class="fas fa-angle-double-left"></i>
                                    <span class="d-none d-md-inline">Primera</span>
                                </a>
                                <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status_filter) ?>" 
                                   title="Página anterior">
                                    <i class="fas fa-angle-left"></i>
                                    <span class="d-none d-md-inline">Anterior</span>
                                </a>
                            <?php else: ?>
                                <span class="disabled">
                                    <i class="fas fa-angle-double-left"></i>
                                    <span class="d-none d-md-inline">Primera</span>
                                </span>
                                <span class="disabled">
                                    <i class="fas fa-angle-left"></i>
                                    <span class="d-none d-md-inline">Anterior</span>
                                </span>
                            <?php endif; ?>

                            <?php
                            // Mostrar números de página con ellipsis si es necesario
                            $start_page = max(1, $page - 2);
                            $end_page = min($total_pages, $page + 2);

                            // Mostrar primera página si no está en el rango
                            if ($start_page > 1): ?>
                                <a href="?page=1&search=<?= urlencode($search) ?>&status=<?= urlencode($status_filter) ?>">1</a>
                                <?php if ($start_page > 2): ?>
                                    <span class="pagination-ellipsis">...</span>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                                <?php if ($i == $page): ?>
                                    <span class="current"><?= $i ?></span>
                                <?php else: ?>
                                    <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status_filter) ?>">
                                        <?= $i ?>
                                    </a>
                                <?php endif; ?>
                            <?php endfor; ?>

                            <?php
                            // Mostrar última página si no está en el rango
                            if ($end_page < $total_pages): ?>
                                <?php if ($end_page < $total_pages - 1): ?>
                                    <span class="pagination-ellipsis">...</span>
                                <?php endif; ?>
                                <a href="?page=<?= $total_pages ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status_filter) ?>">
                                    <?= $total_pages ?>
                                </a>
                            <?php endif; ?>

                            <?php if ($page < $total_pages): ?>
                                <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status_filter) ?>" 
                                   title="Página siguiente">
                                    <span class="d-none d-md-inline">Siguiente</span>
                                    <i class="fas fa-angle-right"></i>
                                </a>
                                <a href="?page=<?= $total_pages ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status_filter) ?>" 
                                   title="Última página">
                                    <span class="d-none d-md-inline">Última</span>
                                    <i class="fas fa-angle-double-right"></i>
                                </a>
                            <?php else: ?>
                                <span class="disabled">
                                    <span class="d-none d-md-inline">Siguiente</span>
                                    <i class="fas fa-angle-right"></i>
                                </span>
                                <span class="disabled">
                                    <span class="d-none d-md-inline">Última</span>
                                    <i class="fas fa-angle-double-right"></i>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Variables globales
        let currentEditId = <?= $edit_id ? $edit_id : 'null' ?>;
        let isSubmitting = false;

        // Mostrar/ocultar formulario
        function toggleForm() {
            const formContainer = document.getElementById('categoryFormContainer');
            if (formContainer.style.display === 'none' || formContainer.style.display === '') {
                formContainer.style.display = 'block';
                document.getElementById('name').focus();
            } else {
                formContainer.style.display = 'none';
            }
        }

        // Cancelar edición
        function cancelEdit() {
            window.location.href = 'categories.php';
        }

        // Editar categoría
        function editCategory(categoryId) {
            if (!categoryId) return;
            window.location.href = `categories.php?edit=${categoryId}`;
        }

        // Función para mostrar notificaciones
        function showNotification(message, type = 'info') {
            const toast = document.getElementById('notificationToast');
            const toastMessage = document.getElementById('toastMessage');
            const toastIcon = toast.querySelector('.fa-info-circle');
            
            // Remover clases previas
            toast.classList.remove('bg-success', 'bg-danger', 'bg-warning', 'bg-info', 'text-white');
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

        // Toggle status categoría
        function toggleStatus(categoryId, currentStatus) {
            if (!categoryId || isSubmitting) return;
            
            const newStatus = currentStatus ? 0 : 1;
            const action = newStatus ? 'activar' : 'desactivar';
            
            if (!confirm(`¿Estás seguro de que deseas ${action} esta categoría?`)) {
                return;
            }
            
            isSubmitting = true;
            
            const formData = new FormData();
            formData.append('action', 'toggle_status');
            formData.append('category_id', categoryId);
            formData.append('new_status', newStatus);
            formData.append('ajax', '1');
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                isSubmitting = false;
                if (data.success) {
                    showNotification(data.message, 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showNotification(data.message || 'Error al cambiar estado', 'error');
                }
            })
            .catch(error => {
                isSubmitting = false;
                console.error('Error:', error);
                showNotification('Error al procesar la solicitud', 'error');
            });
        }

        // Eliminar categoría
        function deleteCategory(categoryId, categoryName, productsCount) {
            if (!categoryId || isSubmitting) return;
            
            let confirmMessage = `¿Estás seguro de que deseas eliminar la categoría "${categoryName}"?`;
            
            if (productsCount > 0) {
                confirmMessage += `\n\nAdvertencia: Esta categoría tiene ${productsCount} producto(s) asociado(s). No se podrá eliminar.`;
                alert(confirmMessage);
                return;
            }
            
            if (!confirm(confirmMessage)) {
                return;
            }
            
            isSubmitting = true;
            
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('category_id', categoryId);
            formData.append('ajax', '1');
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                isSubmitting = false;
                if (data.success) {
                    showNotification(data.message, 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showNotification(data.message || 'Error al eliminar', 'error');
                }
            })
            .catch(error => {
                isSubmitting = false;
                console.error('Error:', error);
                showNotification('Error al procesar la solicitud', 'error');
            });
        }

        // Limpiar filtros
        function clearFilters() {
            window.location.href = 'categories.php';
        }

        // Contadores de caracteres
        document.addEventListener('DOMContentLoaded', function() {
            const nameInput = document.getElementById('name');
            const descTextarea = document.getElementById('description');
            const nameCounter = document.getElementById('nameCounter');
            const descCounter = document.getElementById('descCounter');
            
            function updateCounters() {
                if (nameInput && nameCounter) {
                    nameCounter.textContent = nameInput.value.length;
                }
                if (descTextarea && descCounter) {
                    descCounter.textContent = descTextarea.value.length;
                }
            }
            
            if (nameInput) {
                nameInput.addEventListener('input', updateCounters);
                updateCounters();
            }
            
            if (descTextarea) {
                descTextarea.addEventListener('input', updateCounters);
                updateCounters();
            }

            // Auto-focus en el primer campo si se está editando o creando
            if (currentEditId || document.getElementById('categoryFormContainer').style.display === 'block') {
                if (nameInput) {
                    nameInput.focus();
                }
            }
        });

        // Validación del formulario
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('categoryForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const name = document.getElementById('name').value.trim();
                    
                    if (!name) {
                        e.preventDefault();
                        showNotification('El nombre de la categoría es obligatorio.', 'error');
                        document.getElementById('name').focus();
                        return false;
                    }
                    
                    if (name.length < 2) {
                        e.preventDefault();
                        showNotification('El nombre debe tener al menos 2 caracteres.', 'error');
                        document.getElementById('name').focus();
                        return false;
                    }
                    
                    if (name.length > 100) {
                        e.preventDefault();
                        showNotification('El nombre no puede exceder 100 caracteres.', 'error');
                        document.getElementById('name').focus();
                        return false;
                    }
                    
                    const description = document.getElementById('description').value;
                    if (description.length > 500) {
                        e.preventDefault();
                        showNotification('La descripción no puede exceder 500 caracteres.', 'error');
                        document.getElementById('description').focus();
                        return false;
                    }
                    
                    return true;
                });
            }
        });
    </script>

</body>
</html>