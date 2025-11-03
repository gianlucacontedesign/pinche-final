<?php
/**
 * Editor de Productos
 * Crear y editar productos completos con imágenes, categorías, SEO, etc.
 */

require_once __DIR__ . '/../config/config.php';
$auth = new Auth();
$auth->requireLogin();

$productModel = new Product();
$categoryModel = new Category();
$categories = $categoryModel->getAll();

$isEdit = isset($_GET['id']) && !empty($_GET['id']);
$productId = $isEdit ? (int)$_GET['id'] : null;
$product = null;
$images = [];

if ($isEdit) {
    $product = $productModel->getById($productId);
    if (!$product) {
        setFlashMessage('Producto no encontrado', 'error');
        header('Location: products.php');
        exit;
    }
    $images = $productModel->getImages($productId);
}

$error = '';
$success = '';

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'save_product') {
        $data = [
            'name' => trim($_POST['name']),
            'sku' => trim($_POST['sku']),
            'category_id' => (int)$_POST['category_id'],
            'price' => (float)$_POST['price'],
            'compare_price' => !empty($_POST['compare_price']) ? (float)$_POST['compare_price'] : null,
            'stock' => (int)$_POST['stock'],
            'min_stock' => (int)($_POST['min_stock'] ?? 5),
            'short_description' => trim($_POST['short_description'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'meta_title' => trim($_POST['meta_title'] ?? ''),
            'meta_description' => trim($_POST['meta_description'] ?? ''),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
            'is_new' => isset($_POST['is_new']) ? 1 : 0,
            'is_sale' => isset($_POST['is_sale']) ? 1 : 0
        ];
        
        // Validaciones
        if (empty($data['name'])) {
            $error = 'El nombre del producto es obligatorio';
        } elseif (empty($data['sku'])) {
            $error = 'El SKU es obligatorio';
        } elseif ($data['category_id'] <= 0) {
            $error = 'Debe seleccionar una categoría';
        } elseif ($data['price'] <= 0) {
            $error = 'El precio debe ser mayor a 0';
        } else {
            try {
                if ($isEdit) {
                    // Actualizar producto existente
                    $result = $productModel->update($productId, $data);
                    if ($result) {
                        $success = 'Producto actualizado exitosamente';
                        $product = $productModel->getById($productId);
                    } else {
                        $error = 'Error al actualizar el producto';
                    }
                } else {
                    // Crear nuevo producto
                    $newId = $productModel->create($data);
                    if ($newId) {
                        $success = 'Producto creado exitosamente';
                        header('Location: products-edit.php?id=' . $newId . '&created=1');
                        exit;
                    } else {
                        $error = 'Error al crear el producto';
                    }
                }
            } catch (Exception $e) {
                $error = 'Error: ' . $e->getMessage();
            }
        }
    }
}

// Procesar carga de imágenes
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['product_images']) && $isEdit) {
    $uploadErrors = [];
    $uploadSuccess = 0;
    
    $uploadDir = ROOT_PATH . '/uploads/products/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    foreach ($_FILES['product_images']['tmp_name'] as $key => $tmpName) {
        if ($_FILES['product_images']['error'][$key] === UPLOAD_ERR_OK) {
            $fileName = time() . '_' . $key . '_' . basename($_FILES['product_images']['name'][$key]);
            $targetPath = $uploadDir . $fileName;
            $relativePath = 'uploads/products/' . $fileName;
            
            // Validar tipo de archivo
            $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            if (!in_array($fileType, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $uploadErrors[] = "Archivo {$_FILES['product_images']['name'][$key]}: tipo no permitido";
                continue;
            }
            
            // Validar tamaño (max 5MB)
            if ($_FILES['product_images']['size'][$key] > 5 * 1024 * 1024) {
                $uploadErrors[] = "Archivo {$_FILES['product_images']['name'][$key]}: demasiado grande (máx 5MB)";
                continue;
            }
            
            if (move_uploaded_file($tmpName, $targetPath)) {
                // Determinar si es la primera imagen (será primary)
                $isPrimary = empty($images) && $uploadSuccess === 0;
                $productModel->addImage($productId, $relativePath, $isPrimary);
                $uploadSuccess++;
            }
        }
    }
    
    if ($uploadSuccess > 0) {
        $success = "Se cargaron {$uploadSuccess} imágenes correctamente";
        $images = $productModel->getImages($productId);
    }
    
    if (!empty($uploadErrors)) {
        $error = implode('<br>', $uploadErrors);
    }
}

// Eliminar imagen
if (isset($_GET['delete_image'])) {
    $productModel->deleteImage((int)$_GET['delete_image']);
    header('Location: products-edit.php?id=' . $productId);
    exit;
}

// Establecer imagen principal
if (isset($_GET['set_primary'])) {
    $db = Database::getInstance();
    $db->update('product_images', ['is_primary' => 0], 'product_id = ?', [$productId]);
    $db->update('product_images', ['is_primary' => 1], 'id = ?', [(int)$_GET['set_primary']]);
    header('Location: products-edit.php?id=' . $productId);
    exit;
}

$pageTitle = $isEdit ? 'Editar Producto' : 'Nuevo Producto';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Panel de Administración</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?php echo ADMIN_URL; ?>/assets/css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .image-upload-area {
            border: 2px dashed #d1d5db;
            border-radius: 8px;
            padding: 40px;
            text-align: center;
            background: #f9fafb;
            cursor: pointer;
            transition: all 0.2s;
        }
        .image-upload-area:hover {
            border-color: #6b46c1;
            background: #f3f0ff;
        }
        .image-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 16px;
            margin-top: 20px;
        }
        .image-item {
            position: relative;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            overflow: hidden;
            aspect-ratio: 1;
        }
        .image-item.primary {
            border-color: #6b46c1;
            box-shadow: 0 0 0 2px rgba(107, 70, 193, 0.1);
        }
        .image-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .image-actions {
            position: absolute;
            top: 8px;
            right: 8px;
            display: flex;
            gap: 4px;
        }
        .image-badge {
            position: absolute;
            top: 8px;
            left: 8px;
            background: #6b46c1;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
        }
        .btn-icon {
            background: rgba(255, 255, 255, 0.9);
            border: none;
            width: 32px;
            height: 32px;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
        }
        .btn-icon:hover {
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <?php include 'includes/admin-header.php'; ?>
    
    <div class="admin-layout">
        <?php include 'includes/admin-sidebar.php'; ?>
        
        <main class="admin-content">
            <div class="admin-header-page">
                <div>
                    <h1><?php echo $pageTitle; ?></h1>
                    <p style="color: #737373;"><?php echo $isEdit ? 'Actualiza la información del producto' : 'Crea un nuevo producto para tu catálogo'; ?></p>
                </div>
                <a href="products.php" class="btn-admin btn-admin-secondary">← Volver a Productos</a>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error" style="margin-bottom: 20px;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success || isset($_GET['created'])): ?>
                <div class="alert alert-success" style="margin-bottom: 20px;">
                    <?php echo $success ?: 'Producto creado exitosamente. Ahora puedes agregar imágenes.'; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="admin-form">
                <input type="hidden" name="action" value="save_product">
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
                    <!-- Columna izquierda -->
                    <div>
                        <div class="admin-card">
                            <h2 style="font-size: 18px; font-weight: 600; margin-bottom: 20px;">Información Básica</h2>
                            
                            <div class="form-group">
                                <label class="form-label required">Nombre del Producto</label>
                                <input type="text" name="name" class="form-control" required 
                                       value="<?php echo e($product['name'] ?? ''); ?>" 
                                       placeholder="Ej: Máquina de Tatuar Rotativa">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label required">SKU (Código)</label>
                                <input type="text" name="sku" class="form-control" required 
                                       value="<?php echo e($product['sku'] ?? ''); ?>" 
                                       placeholder="Ej: MTR-001">
                                <small style="color: #737373;">Código único de producto</small>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label required">Categoría</label>
                                <select name="category_id" class="form-control" required>
                                    <option value="">Seleccionar categoría...</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <?php if ($cat['parent_id'] === null): ?>
                                            <optgroup label="<?php echo e($cat['name']); ?>">
                                                <option value="<?php echo $cat['id']; ?>" 
                                                        <?php echo ($product['category_id'] ?? 0) == $cat['id'] ? 'selected' : ''; ?>>
                                                    <?php echo e($cat['name']); ?>
                                                </option>
                                                <?php 
                                                $subcats = array_filter($categories, fn($c) => $c['parent_id'] == $cat['id']);
                                                foreach ($subcats as $subcat): 
                                                ?>
                                                    <option value="<?php echo $subcat['id']; ?>" 
                                                            <?php echo ($product['category_id'] ?? 0) == $subcat['id'] ? 'selected' : ''; ?>>
                                                        &nbsp;&nbsp;&nbsp;<?php echo e($subcat['name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </optgroup>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                                <div class="form-group">
                                    <label class="form-label required">Precio</label>
                                    <input type="number" name="price" class="form-control" required step="0.01" min="0"
                                           value="<?php echo $product['price'] ?? ''; ?>" 
                                           placeholder="0.00">
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Precio Comparación</label>
                                    <input type="number" name="compare_price" class="form-control" step="0.01" min="0"
                                           value="<?php echo $product['compare_price'] ?? ''; ?>" 
                                           placeholder="0.00">
                                    <small style="color: #737373;">Precio anterior (tachado)</small>
                                </div>
                            </div>
                            
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                                <div class="form-group">
                                    <label class="form-label required">Stock Actual</label>
                                    <input type="number" name="stock" class="form-control" required min="0"
                                           value="<?php echo $product['stock'] ?? '0'; ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Stock Mínimo</label>
                                    <input type="number" name="min_stock" class="form-control" min="0"
                                           value="<?php echo $product['min_stock'] ?? '5'; ?>">
                                    <small style="color: #737373;">Alerta de stock bajo</small>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Descripción Corta</label>
                                <textarea name="short_description" class="form-control" rows="3" 
                                          placeholder="Breve descripción del producto (máx 200 caracteres)"
                                          maxlength="200"><?php echo e($product['short_description'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Descripción Completa</label>
                                <textarea name="description" class="form-control" rows="8" 
                                          placeholder="Descripción detallada del producto, características, usos, etc."><?php echo e($product['description'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Columna derecha -->
                    <div>
                        <div class="admin-card" style="margin-bottom: 24px;">
                            <h2 style="font-size: 18px; font-weight: 600; margin-bottom: 20px;">SEO</h2>
                            
                            <div class="form-group">
                                <label class="form-label">Meta Título</label>
                                <input type="text" name="meta_title" class="form-control" maxlength="60"
                                       value="<?php echo e($product['meta_title'] ?? ''); ?>" 
                                       placeholder="Título para buscadores (máx 60 caracteres)">
                                <small style="color: #737373;">Si está vacío, se usa el nombre del producto</small>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Meta Descripción</label>
                                <textarea name="meta_description" class="form-control" rows="3" maxlength="160"
                                          placeholder="Descripción para buscadores (máx 160 caracteres)"><?php echo e($product['meta_description'] ?? ''); ?></textarea>
                            </div>
                        </div>
                        
                        <div class="admin-card">
                            <h2 style="font-size: 18px; font-weight: 600; margin-bottom: 20px;">Opciones</h2>
                            
                            <div class="form-group">
                                <label class="checkbox-container">
                                    <input type="checkbox" name="is_active" value="1" 
                                           <?php echo ($product['is_active'] ?? 1) ? 'checked' : ''; ?>>
                                    <span>Producto activo</span>
                                </label>
                                <small style="color: #737373; display: block; margin-left: 28px;">Visible en la tienda</small>
                            </div>
                            
                            <div class="form-group">
                                <label class="checkbox-container">
                                    <input type="checkbox" name="is_featured" value="1" 
                                           <?php echo ($product['is_featured'] ?? 0) ? 'checked' : ''; ?>>
                                    <span>Producto destacado</span>
                                </label>
                                <small style="color: #737373; display: block; margin-left: 28px;">Aparece en sección destacados</small>
                            </div>
                            
                            <div class="form-group">
                                <label class="checkbox-container">
                                    <input type="checkbox" name="is_new" value="1" 
                                           <?php echo ($product['is_new'] ?? 0) ? 'checked' : ''; ?>>
                                    <span>Badge "NUEVO"</span>
                                </label>
                                <small style="color: #737373; display: block; margin-left: 28px;">Muestra badge de nuevo en la ficha</small>
                            </div>
                            
                            <div class="form-group">
                                <label class="checkbox-container">
                                    <input type="checkbox" name="is_sale" value="1" 
                                           <?php echo ($product['is_sale'] ?? 0) ? 'checked' : ''; ?>>
                                    <span>Badge "OFERTA"</span>
                                </label>
                                <small style="color: #737373; display: block; margin-left: 28px;">Muestra badge de oferta</small>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn-admin btn-admin-primary" style="width: 100%; margin-top: 24px;">
                            <?php echo $isEdit ? '💾 Guardar Cambios' : '✨ Crear Producto'; ?>
                        </button>
                    </div>
                </div>
            </form>
            
            <?php if ($isEdit): ?>
            <div class="admin-card" style="margin-top: 24px;">
                <h2 style="font-size: 18px; font-weight: 600; margin-bottom: 20px;">Imágenes del Producto</h2>
                
                <form method="POST" enctype="multipart/form-data" id="imageUploadForm">
                    <div class="image-upload-area" onclick="document.getElementById('imageInput').click()">
                        <input type="file" id="imageInput" name="product_images[]" multiple accept="image/*" 
                               style="display: none;" onchange="this.form.submit()">
                        <div style="font-size: 48px; margin-bottom: 12px;">📸</div>
                        <p style="font-weight: 600; margin-bottom: 8px;">Haz clic para cargar imágenes</p>
                        <p style="color: #737373; font-size: 14px;">PNG, JPG, GIF, WebP hasta 5MB cada una</p>
                    </div>
                </form>
                
                <?php if (!empty($images)): ?>
                <div class="image-gallery">
                    <?php foreach ($images as $image): ?>
                    <div class="image-item <?php echo $image['is_primary'] ? 'primary' : ''; ?>">
                        <img src="<?php echo SITE_URL . '/' . e($image['image_path']); ?>" alt="Imagen del producto">
                        
                        <?php if ($image['is_primary']): ?>
                        <span class="image-badge">Principal</span>
                        <?php endif; ?>
                        
                        <div class="image-actions">
                            <?php if (!$image['is_primary']): ?>
                            <a href="?id=<?php echo $productId; ?>&set_primary=<?php echo $image['id']; ?>" 
                               class="btn-icon" title="Establecer como principal">⭐</a>
                            <?php endif; ?>
                            <a href="?id=<?php echo $productId; ?>&delete_image=<?php echo $image['id']; ?>" 
                               class="btn-icon" title="Eliminar" 
                               onclick="return confirm('¿Eliminar esta imagen?')">🗑️</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p style="text-align: center; color: #737373; padding: 40px 0;">No hay imágenes cargadas aún</p>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
