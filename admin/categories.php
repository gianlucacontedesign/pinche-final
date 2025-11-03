<?php
require_once __DIR__ . '/../config/config.php';
$auth = new Auth();
$auth->requireLogin();

$categoryModel = new Category();
$categories = $categoryModel->getAll();

// Manejar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'create') {
            $data = [
                'name' => $_POST['name'],
                'description' => $_POST['description'] ?? null,
                'parent_id' => !empty($_POST['parent_id']) ? $_POST['parent_id'] : null,
                'display_order' => $_POST['display_order'] ?? 0,
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ];
            if ($categoryModel->create($data)) {
                setFlashMessage('Categoría creada exitosamente', 'success');
            } else {
                setFlashMessage('Error al crear categoría', 'error');
            }
            header('Location: categories.php');
            exit;
        }
    }
}

// Eliminar categoría
if (isset($_GET['delete'])) {
    $result = $categoryModel->delete($_GET['delete']);
    setFlashMessage($result['message'], $result['success'] ? 'success' : 'error');
    header('Location: categories.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categorías - Panel de Administración</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?php echo ADMIN_URL; ?>/assets/css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'includes/admin-header.php'; ?>
    <div class="admin-layout">
        <?php include 'includes/admin-sidebar.php'; ?>
        <main class="admin-content">
            <div class="admin-header-page" style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h1>Categorías</h1>
                    <p style="color: #737373;">Gestiona las categorías de productos</p>
                </div>
                <button onclick="document.getElementById('modal-create').style.display='flex'" class="btn-admin btn-admin-primary">
                    + Nueva Categoría
                </button>
            </div>
            
            <div class="admin-card">
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Descripción</th>
                                <th>Categoría Padre</th>
                                <th>Orden</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $category): ?>
                            <tr>
                                <td><?php echo e($category['name']); ?></td>
                                <td><?php echo e(substr($category['description'] ?? '', 0, 50)); ?></td>
                                <td><?php 
                                    if ($category['parent_id']) {
                                        $parent = $categoryModel->getById($category['parent_id']);
                                        echo e($parent['name'] ?? '-');
                                    } else {
                                        echo '-';
                                    }
                                ?></td>
                                <td><?php echo $category['display_order']; ?></td>
                                <td>
                                    <span class="badge <?php echo $category['is_active'] ? 'badge-success' : 'badge-danger'; ?>">
                                        <?php echo $category['is_active'] ? 'Activo' : 'Inactivo'; ?>
                                    </span>
                                </td>
                                <td class="table-actions">
                                    <a href="categories-edit.php?id=<?php echo $category['id']; ?>" class="action-btn action-btn-edit">Editar</a>
                                    <a href="?delete=<?php echo $category['id']; ?>" class="action-btn action-btn-delete" onclick="return confirm('¿Eliminar esta categoría?')">Eliminar</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal Crear Categoría -->
    <div id="modal-create" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; z-index: 1000;">
        <div style="background: white; border-radius: 1rem; padding: 2rem; width: 100%; max-width: 600px; max-height: 90vh; overflow-y: auto;">
            <h2 style="font-size: 1.5rem; font-weight: 800; margin-bottom: 1.5rem;">Nueva Categoría</h2>
            <form method="POST">
                <input type="hidden" name="action" value="create">
                <div class="form-group">
                    <label class="form-label">Nombre *</label>
                    <input type="text" name="name" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Descripción</label>
                    <textarea name="description" class="form-textarea"></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Categoría Padre</label>
                    <select name="parent_id" class="form-select">
                        <option value="">Sin padre (categoría principal)</option>
                        <?php foreach ($categoryModel->getParentCategories() as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>"><?php echo e($cat['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Orden</label>
                    <input type="number" name="display_order" class="form-input" value="0">
                </div>
                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="checkbox" name="is_active" checked>
                        <span>Activo</span>
                    </label>
                </div>
                <div style="display: flex; gap: 1rem;">
                    <button type="submit" class="btn-admin btn-admin-primary">Crear</button>
                    <button type="button" onclick="document.getElementById('modal-create').style.display='none'" class="btn-admin btn-admin-outline">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
