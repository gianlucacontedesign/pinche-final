<?php
/**
 * ADMIN - CATEGORIES MANAGER
 * Gestión de categorías - Crear, Editar, Eliminar
 * Versión: 03 Nov 2025 - 21:36
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Autenticación
$auth = new Auth();
$auth->requireLogin();

// Conexión base de datos
$db = Database::getInstance();

$action = $_GET['action'] ?? 'create';
$category_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$message = '';
$error = '';

// Obtener categoría para edición
if ($action === 'edit' && $category_id > 0) {
    $category = $db->fetchOne("SELECT * FROM categories WHERE id = ? AND is_deleted = 0", [$category_id]);
    
    if (!$category) {
        $error = 'Categoría no encontrada.';
        $action = 'create'; // Fallback a crear
    }
} else {
    // Valores por defecto para crear nueva categoría
    $category = [
        'id' => 0,
        'name' => '',
        'description' => '',
        'is_active' => 1
    ];
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Validaciones
    if (empty($name)) {
        $error = 'El nombre de la categoría es obligatorio.';
    } else {
        try {
            if ($action === 'create') {
                // Verificar que no exista otra categoría con el mismo nombre
                $existing = $db->fetchOne("SELECT id FROM categories WHERE name = ? AND is_deleted = 0", [$name]);
                
                if ($existing) {
                    $error = 'Ya existe una categoría con ese nombre.';
                } else {
                    // Crear nueva categoría
                    $db->execute(
                        "INSERT INTO categories (name, description, is_active, created_at) VALUES (?, ?, ?, NOW())",
                        [$name, $description, $is_active]
                    );
                    
                    $message = 'Categoría creada correctamente.';
                    $action = 'success';
                }
                
            } elseif ($action === 'edit' && $category_id > 0) {
                // Verificar que no exista otra categoría con el mismo nombre (excluyendo la actual)
                $existing = $db->fetchOne(
                    "SELECT id FROM categories WHERE name = ? AND id != ? AND is_deleted = 0", 
                    [$name, $category_id]
                );
                
                if ($existing) {
                    $error = 'Ya existe otra categoría con ese nombre.';
                } else {
                    // Actualizar categoría existente
                    $db->execute(
                        "UPDATE categories SET name = ?, description = ?, is_active = ?, updated_at = NOW() WHERE id = ?",
                        [$name, $description, $is_active, $category_id]
                    );
                    
                    $message = 'Categoría actualizada correctamente.';
                    $action = 'success';
                }
            }
            
        } catch (Exception $e) {
            $error = 'Error al guardar la categoría: ' . $e->getMessage();
        }
    }
    
    // Si hay error, recargar datos del formulario
    if ($error) {
        $category['name'] = $name;
        $category['description'] = $description;
        $category['is_active'] = $is_active;
    }
}

include __DIR__ . '/includes/sidebar.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?= $action === 'create' ? 'Nueva Categoría' : ($action === 'edit' ? 'Editar Categoría' : 'Categoría Guardada') ?> 
        - Pinche Supplies Admin
    </title>
    
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
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .form-card {
            background: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
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
        
        .form-control, .form-select {
            border: 2px solid #e9ecef;
            border-radius: 6px;
            padding: 10px 12px;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        
        .form-control.is-invalid {
            border-color: #dc3545;
        }
        
        .form-control.is-valid {
            border-color: #28a745;
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
        
        .form-actions {
            border-top: 2px solid #e9ecef;
            padding-top: 20px;
            margin-top: 30px;
        }
        
        .btn {
            border-radius: 6px;
            font-weight: 500;
            padding: 10px 20px;
            transition: all 0.2s ease;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #007bff, #0056b3);
            border: none;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #0056b3, #004085);
            transform: translateY(-1px);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #6c757d, #545b62);
            border: none;
        }
        
        .btn-secondary:hover {
            background: linear-gradient(135deg, #545b62, #3d4142);
            transform: translateY(-1px);
        }
        
        .btn-success {
            background: linear-gradient(135deg, #28a745, #1e7e34);
            border: none;
        }
        
        .btn-success:hover {
            background: linear-gradient(135deg, #1e7e34, #155724);
            transform: translateY(-1px);
        }
        
        .success-card {
            background: white;
            border-radius: 8px;
            padding: 60px 30px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .success-icon {
            font-size: 4rem;
            color: #28a745;
            margin-bottom: 20px;
        }
        
        .info-box {
            background-color: #e7f3ff;
            border: 1px solid #b3d9ff;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .info-box h6 {
            color: #0066cc;
            margin-bottom: 8px;
        }
        
        .warning-box {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .warning-box h6 {
            color: #856404;
            margin-bottom: 8px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 10px;
            }
            
            .form-card {
                padding: 20px;
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
    </style>
</head>
<body>

<div class="main-content">
    
    <?php if ($action === 'success'): ?>
        <!-- Página de éxito -->
        <div class="success-card fade-in">
            <i class="fas fa-check-circle success-icon"></i>
            <h2>¡Categoría Guardada Correctamente!</h2>
            <p class="text-muted">La categoría ha sido procesada exitosamente.</p>
            
            <div class="mt-4">
                <a href="categories.php" class="btn btn-primary">
                    <i class="fas fa-tags"></i> Volver a Categorías
                </a>
                <a href="categories-manager.php?action=create" class="btn btn-success">
                    <i class="fas fa-plus"></i> Nueva Categoría
                </a>
            </div>
        </div>
        
    <?php else: ?>
        <!-- Formulario -->
        <div class="page-header">
            <div>
                <h1>
                    <i class="fas fa-<?= $action === 'create' ? 'plus' : 'edit' ?>"></i> 
                    <?= $action === 'create' ? 'Nueva Categoría' : 'Editar Categoría' ?>
                </h1>
                <p class="text-muted mb-0">
                    <?= $action === 'create' ? 'Crear una nueva categoría para organizar productos' : 'Modificar los datos de la categoría' ?>
                </p>
            </div>
            <div>
                <a href="categories.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>

        <!-- Mensajes -->
        <?php if ($message): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> <?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Información adicional -->
        <?php if ($action === 'create'): ?>
            <div class="info-box">
                <h6><i class="fas fa-lightbulb"></i> Consejos para crear categorías</h6>
                <ul class="mb-0">
                    <li>Usa nombres descriptivos y claros</li>
                    <li>La descripción ayuda a identificar el propósito de la categoría</li>
                    <li>Puedes activar/desactivar categorías después de crearlas</li>
                </ul>
            </div>
        <?php else: ?>
            <div class="warning-box">
                <h6><i class="fas fa-exclamation-triangle"></i> Nota sobre modificación</h6>
                <p class="mb-0">Si cambias el nombre de una categoría que tiene productos asociados, estos mantendrá su asociación automáticamente.</p>
            </div>
        <?php endif; ?>

        <!-- Formulario -->
        <form method="POST" class="form-card fade-in" id="categoryForm">
            
            <div class="form-section">
                <h5><i class="fas fa-info-circle"></i> Información Básica</h5>
                
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
                                   value="<?= htmlspecialchars($category['name']) ?>"
                                   placeholder="Ej: Electrónicos, Ropa, Hogar..."
                                   maxlength="100"
                                   required>
                            <div class="char-counter">
                                <span id="nameCounter">0</span>/100 caracteres
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
                                           <?= $category['is_active'] ? 'checked' : '' ?>>
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
                              placeholder="Describe brevemente esta categoría..."><?= htmlspecialchars($category['description']) ?></textarea>
                    <div class="char-counter">
                        <span id="descCounter">0</span>/500 caracteres
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <div class="row">
                    <div class="col-md-6">
                        <a href="categories.php" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                    <div class="col-md-6 text-end">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-<?= $action === 'create' ? 'plus' : 'save' ?>"></i> 
                            <?= $action === 'create' ? 'Crear Categoría' : 'Guardar Cambios' ?>
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <!-- Vista previa de la categoría -->
        <?php if (!empty($category['name'])): ?>
            <div class="form-card">
                <h5><i class="fas fa-eye"></i> Vista Previa</h5>
                <div class="border rounded p-3" style="background-color: #f8f9fa;">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h6 class="mb-1"><?= htmlspecialchars($category['name']) ?: 'Nombre de la categoría' ?></h6>
                            <p class="text-muted mb-0">
                                <?= htmlspecialchars($category['description']) ?: 'Descripción de la categoría...' ?>
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <span class="badge <?= $category['is_active'] ? 'bg-success' : 'bg-secondary' ?>">
                                <?= $category['is_active'] ? 'Activa' : 'Inactiva' ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    <?php endif; ?>

</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Contadores de caracteres
    const nameInput = document.getElementById('name');
    const descTextarea = document.getElementById('description');
    const nameCounter = document.getElementById('nameCounter');
    const descCounter = document.getElementById('descCounter');
    
    function updateCounters() {
        if (nameInput) {
            nameCounter.textContent = nameInput.value.length;
        }
        if (descTextarea) {
            descCounter.textContent = descTextarea.value.length;
        }
    }
    
    if (nameInput) {
        nameInput.addEventListener('input', updateCounters);
        updateCounters(); // Inicializar
    }
    
    if (descTextarea) {
        descTextarea.addEventListener('input', updateCounters);
        updateCounters(); // Inicializar
    }
    
    // Validación del formulario
    const form = document.getElementById('categoryForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            const name = document.getElementById('name').value.trim();
            
            if (!name) {
                e.preventDefault();
                alert('El nombre de la categoría es obligatorio.');
                document.getElementById('name').focus();
                return false;
            }
            
            if (name.length < 2) {
                e.preventDefault();
                alert('El nombre debe tener al menos 2 caracteres.');
                document.getElementById('name').focus();
                return false;
            }
            
            return true;
        });
    }
    
    // Auto-focus en el primer campo
    if (document.getElementById('name')) {
        document.getElementById('name').focus();
    }
});
</script>

</body>
</html>