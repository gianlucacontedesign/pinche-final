<?php
require_once __DIR__ . '/../config/config.php';
$auth = new Auth();
$auth->requireLogin();

$categoryModel = new Category();

// Modo edición o creación
$isEdit = isset($_GET['id']);
$category = null;
$pageTitle = 'Nueva Categoría';

if ($isEdit) {
    $category = $categoryModel->getById($_GET['id']);
    if (!$category) {
        setFlashMessage('Categoría no encontrada', 'error');
        header('Location: categories.php');
        exit;
    }
    $pageTitle = 'Editar Categoría';
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name' => $_POST['name'] ?? '',
        'description' => $_POST['description'] ?? null,
        'parent_id' => !empty($_POST['parent_id']) ? $_POST['parent_id'] : null,
        'display_order' => $_POST['display_order'] ?? 0,
        'is_active' => isset($_POST['is_active']) ? 1 : 0
    ];
    
    // Validaciones
    $errors = [];
    
    if (empty($data['name'])) {
        $errors[] = 'El nombre es obligatorio';
    }
    
    // Validar que no se seleccione a sí misma como padre
    if ($isEdit && $data['parent_id'] == $_GET['id']) {
        $errors[] = 'Una categoría no puede ser su propia categoría padre';
    }
    
    if (empty($errors)) {
        if ($isEdit) {
            // Actualizar categoría existente
            if ($categoryModel->update($_GET['id'], $data)) {
                setFlashMessage('Categoría actualizada exitosamente', 'success');
                header('Location: categories.php');
                exit;
            } else {
                setFlashMessage('Error al actualizar categoría', 'error');
            }
        } else {
            // Crear nueva categoría
            if ($categoryModel->create($data)) {
                setFlashMessage('Categoría creada exitosamente', 'success');
                header('Location: categories.php');
                exit;
            } else {
                setFlashMessage('Error al crear categoría', 'error');
            }
        }
    }
}

// Obtener todas las categorías para el selector de padre
$allCategories = $categoryModel->getAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($pageTitle); ?> - Panel de Administración</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?php echo ADMIN_URL; ?>/assets/css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .form-section {
            background: white;
            border-radius: 0.75rem;
            padding: 2rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .form-section h2 {
            font-size: 1.25rem;
            font-weight: 700;
            color: #171717;
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid #f5f5f5;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            font-weight: 600;
            color: #404040;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
        }
        
        .form-input,
        .form-textarea,
        .form-select {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e5e5e5;
            border-radius: 0.5rem;
            font-size: 1rem;
            transition: all 0.2s;
        }
        
        .form-input:focus,
        .form-textarea:focus,
        .form-select:focus {
            outline: none;
            border-color: #6b46c1;
            box-shadow: 0 0 0 3px rgba(107, 70, 193, 0.1);
        }
        
        .form-textarea {
            min-height: 120px;
            resize: vertical;
            font-family: inherit;
        }
        
        .form-checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem;
            background: #f9fafb;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: background 0.2s;
        }
        
        .form-checkbox-wrapper:hover {
            background: #f3f4f6;
        }
        
        .form-checkbox-wrapper input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }
        
        .form-actions {
            display: flex;
            gap: 1rem;
            padding-top: 1.5rem;
            border-top: 2px solid #f5f5f5;
        }
        
        .btn {
            padding: 0.875rem 2rem;
            border-radius: 0.5rem;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        
        .btn-primary {
            background: #6b46c1;
            color: white;
            border: none;
        }
        
        .btn-primary:hover {
            background: #553c9a;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(107, 70, 193, 0.3);
        }
        
        .btn-secondary {
            background: white;
            color: #404040;
            border: 2px solid #e5e5e5;
        }
        
        .btn-secondary:hover {
            background: #f9fafb;
            border-color: #d4d4d4;
        }
        
        .alert {
            padding: 1rem 1.5rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }
        
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #dc2626;
        }
        
        .form-hint {
            font-size: 0.875rem;
            color: #737373;
            margin-top: 0.375rem;
        }
        
        .required-mark {
            color: #dc2626;
            margin-left: 0.25rem;
        }
        
        .category-preview {
            background: #f9fafb;
            border: 2px dashed #d4d4d4;
            border-radius: 0.5rem;
            padding: 1.5rem;
            margin-top: 1rem;
        }
        
        .category-preview h3 {
            font-size: 1rem;
            font-weight: 600;
            color: #6b46c1;
            margin-bottom: 1rem;
        }
        
        .preview-item {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid #e5e5e5;
        }
        
        .preview-item:last-child {
            border-bottom: none;
        }
        
        .preview-label {
            font-weight: 600;
            color: #525252;
        }
        
        .preview-value {
            color: #737373;
        }
    </style>
</head>
<body>
    <?php include 'includes/admin-header.php'; ?>
    <div class="admin-layout">
        <?php include 'includes/admin-sidebar.php'; ?>
        <main class="admin-content">
            <!-- Breadcrumb -->
            <div style="margin-bottom: 1.5rem;">
                <a href="categories.php" style="color: #6b46c1; text-decoration: none; font-weight: 500;">
                    ← Volver a Categorías
                </a>
            </div>
            
            <!-- Header -->
            <div class="admin-header-page" style="margin-bottom: 2rem;">
                <div>
                    <h1><?php echo e($pageTitle); ?></h1>
                    <p style="color: #737373;">
                        <?php if ($isEdit): ?>
                        Modifica los datos de la categoría existente
                        <?php else: ?>
                        Completa los datos para crear una nueva categoría
                        <?php endif; ?>
                    </p>
                </div>
            </div>
            
            <?php if (isset($errors) && !empty($errors)): ?>
            <div class="alert alert-error">
                <strong>Error:</strong>
                <ul style="margin: 0.5rem 0 0 1.5rem;">
                    <?php foreach ($errors as $error): ?>
                    <li><?php echo e($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <!-- Información Básica -->
                <div class="form-section">
                    <h2>📋 Información Básica</h2>
                    
                    <div class="form-group">
                        <label class="form-label">
                            Nombre de la Categoría
                            <span class="required-mark">*</span>
                        </label>
                        <input 
                            type="text" 
                            name="name" 
                            class="form-input" 
                            value="<?php echo $isEdit ? e($category['name']) : ''; ?>"
                            placeholder="Ej: Máquinas, Tintas, Agujas..."
                            required
                            autofocus>
                        <p class="form-hint">Este nombre se mostrará en el menú de navegación y listados</p>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Descripción</label>
                        <textarea 
                            name="description" 
                            class="form-textarea"
                            placeholder="Describe brevemente esta categoría..."><?php echo $isEdit ? e($category['description']) : ''; ?></textarea>
                        <p class="form-hint">Descripción opcional para SEO y ayuda a los usuarios</p>
                    </div>
                </div>
                
                <!-- Organización -->
                <div class="form-section">
                    <h2>📁 Organización</h2>
                    
                    <div class="form-group">
                        <label class="form-label">Categoría Padre</label>
                        <select name="parent_id" class="form-select">
                            <option value="">Sin categoría padre (Principal)</option>
                            <?php foreach ($allCategories as $cat): ?>
                                <?php 
                                // No mostrar la categoría actual como opción (evitar que sea su propio padre)
                                if ($isEdit && $cat['id'] == $_GET['id']) continue;
                                ?>
                                <option 
                                    value="<?php echo $cat['id']; ?>"
                                    <?php echo ($isEdit && $category['parent_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                    <?php echo e($cat['name']); ?>
                                    <?php if ($cat['parent_id']): ?>
                                        (Subcategoría)
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="form-hint">Selecciona una categoría padre para crear una subcategoría</p>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Orden de Visualización</label>
                        <input 
                            type="number" 
                            name="display_order" 
                            class="form-input" 
                            value="<?php echo $isEdit ? $category['display_order'] : 0; ?>"
                            min="0"
                            placeholder="0">
                        <p class="form-hint">Las categorías se ordenan de menor a mayor número (0 = primera)</p>
                    </div>
                </div>
                
                <!-- Estado -->
                <div class="form-section">
                    <h2>⚙️ Configuración</h2>
                    
                    <div class="form-group">
                        <label class="form-checkbox-wrapper">
                            <input 
                                type="checkbox" 
                                name="is_active"
                                <?php echo (!$isEdit || $category['is_active']) ? 'checked' : ''; ?>>
                            <div>
                                <strong style="display: block; color: #171717;">Categoría Activa</strong>
                                <span style="font-size: 0.875rem; color: #737373;">
                                    Las categorías inactivas no se muestran en la tienda
                                </span>
                            </div>
                        </label>
                    </div>
                </div>
                
                <?php if ($isEdit): ?>
                <!-- Preview de la Categoría -->
                <div class="form-section">
                    <h2>👁️ Vista Previa</h2>
                    <div class="category-preview">
                        <h3>Información de la Categoría</h3>
                        <div class="preview-item">
                            <span class="preview-label">ID:</span>
                            <span class="preview-value">#<?php echo $category['id']; ?></span>
                        </div>
                        <div class="preview-item">
                            <span class="preview-label">Slug URL:</span>
                            <span class="preview-value"><?php echo e($category['slug']); ?></span>
                        </div>
                        <div class="preview-item">
                            <span class="preview-label">Creada:</span>
                            <span class="preview-value"><?php echo date('d/m/Y H:i', strtotime($category['created_at'])); ?></span>
                        </div>
                        <?php if ($category['updated_at']): ?>
                        <div class="preview-item">
                            <span class="preview-label">Última actualización:</span>
                            <span class="preview-value"><?php echo date('d/m/Y H:i', strtotime($category['updated_at'])); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Acciones -->
                <div class="form-section">
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <?php echo $isEdit ? '💾 Guardar Cambios' : '✅ Crear Categoría'; ?>
                        </button>
                        <a href="categories.php" class="btn btn-secondary">
                            ❌ Cancelar
                        </a>
                    </div>
                </div>
            </form>
        </main>
    </div>
    
    <script>
        // Auto-hide alerts después de 5 segundos
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);
        
        // Atajo de teclado: Ctrl/Cmd + S para guardar
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                document.querySelector('form').submit();
            }
        });
    </script>
</body>
</html>
