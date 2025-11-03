<?php
/**
 * Funciones de utilidad
 */

/**
 * Escapar HTML para prevenir XSS
 */
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Formatear precio en pesos argentinos
 */
function formatPrice($price) {
    return '$' . number_format($price, 2, ',', '.');
}

/**
 * Generar número de orden único
 */
function generateOrderNumber() {
    return 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
}

/**
 * Obtener configuración del sitio
 */
function getSetting($key, $default = null) {
    $db = Database::getInstance();
    $sql = "SELECT setting_value FROM settings WHERE setting_key = ?";
    $result = $db->fetchOne($sql, [$key]);
    return $result ? $result['setting_value'] : $default;
}

/**
 * Guardar configuración del sitio
 */
function setSetting($key, $value, $type = 'text') {
    $db = Database::getInstance();
    $sql = "INSERT INTO settings (setting_key, setting_value, setting_type) 
            VALUES (?, ?, ?) 
            ON DUPLICATE KEY UPDATE setting_value = ?, setting_type = ?";
    return $db->query($sql, [$key, $value, $type, $value, $type]) !== false;
}

/**
 * Subir imagen
 */
function uploadImage($file, $destination = 'products', $maxSize = MAX_IMAGE_SIZE) {
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        return ['success' => false, 'message' => 'No se recibió ningún archivo'];
    }
    
    // Validar tipo de archivo
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, ALLOWED_IMAGE_TYPES)) {
        return ['success' => false, 'message' => 'Tipo de archivo no permitido'];
    }
    
    // Validar tamaño
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'El archivo es demasiado grande'];
    }
    
    // Generar nombre único
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '-' . time() . '.' . $extension;
    $uploadPath = UPLOADS_PATH . '/' . $destination;
    
    // Crear directorio si no existe
    if (!is_dir($uploadPath)) {
        mkdir($uploadPath, 0755, true);
    }
    
    $filePath = $uploadPath . '/' . $filename;
    
    // Procesar y optimizar imagen
    try {
        $image = null;
        
        switch ($mimeType) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($file['tmp_name']);
                break;
            case 'image/png':
                $image = imagecreatefrompng($file['tmp_name']);
                break;
            case 'image/webp':
                $image = imagecreatefromwebp($file['tmp_name']);
                break;
            case 'image/gif':
                $image = imagecreatefromgif($file['tmp_name']);
                break;
        }
        
        if ($image) {
            // Obtener dimensiones originales
            $width = imagesx($image);
            $height = imagesy($image);
            
            // Redimensionar si es necesario
            $maxWidth = 1200;
            $maxHeight = 1200;
            
            if ($width > $maxWidth || $height > $maxHeight) {
                $ratio = min($maxWidth / $width, $maxHeight / $height);
                $newWidth = round($width * $ratio);
                $newHeight = round($height * $ratio);
                
                $newImage = imagecreatetruecolor($newWidth, $newHeight);
                
                // Preservar transparencia para PNG
                if ($mimeType === 'image/png') {
                    imagealphablending($newImage, false);
                    imagesavealpha($newImage, true);
                }
                
                imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                imagedestroy($image);
                $image = $newImage;
            }
            
            // Guardar imagen optimizada
            switch ($mimeType) {
                case 'image/jpeg':
                    imagejpeg($image, $filePath, IMAGE_QUALITY);
                    break;
                case 'image/png':
                    imagepng($image, $filePath, 9);
                    break;
                case 'image/webp':
                    imagewebp($image, $filePath, IMAGE_QUALITY);
                    break;
                case 'image/gif':
                    imagegif($image, $filePath);
                    break;
            }
            
            imagedestroy($image);
            
            $relativePath = 'assets/uploads/' . $destination . '/' . $filename;
            return ['success' => true, 'path' => $relativePath, 'filename' => $filename];
        }
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error al procesar la imagen'];
    }
    
    return ['success' => false, 'message' => 'Error desconocido'];
}

/**
 * Eliminar imagen
 */
function deleteImage($path) {
    $filePath = ROOT_PATH . '/' . $path;
    if (file_exists($filePath)) {
        return unlink($filePath);
    }
    return false;
}

/**
 * Generar breadcrumbs
 */
function generateBreadcrumbs($items) {
    $html = '<nav class="breadcrumbs">';
    $html .= '<a href="' . SITE_URL . '">Inicio</a>';
    
    foreach ($items as $item) {
        $html .= ' / ';
        if (isset($item['url'])) {
            $html .= '<a href="' . e($item['url']) . '">' . e($item['name']) . '</a>';
        } else {
            $html .= '<span>' . e($item['name']) . '</span>';
        }
    }
    
    $html .= '</nav>';
    return $html;
}

/**
 * Validar email
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Sanitizar string
 */
function sanitize($string) {
    return trim(strip_tags($string));
}

/**
 * Generar token CSRF
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verificar token CSRF
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Mostrar mensaje flash
 */
function setFlashMessage($message, $type = 'success') {
    $_SESSION['flash_message'] = [
        'message' => $message,
        'type' => $type
    ];
}

/**
 * Verificar si hay mensaje flash
 */
function hasFlashMessage() {
    return isset($_SESSION['flash_message']);
}

/**
 * Obtener y limpiar mensaje flash
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

/**
 * Paginación
 */
function paginate($currentPage, $totalItems, $itemsPerPage, $baseUrl) {
    $totalPages = ceil($totalItems / $itemsPerPage);
    
    if ($totalPages <= 1) {
        return '';
    }
    
    $html = '<div class="pagination">';
    
    // Botón anterior
    if ($currentPage > 1) {
        $html .= '<a href="' . $baseUrl . '?page=' . ($currentPage - 1) . '" class="pagination-btn">Anterior</a>';
    }
    
    // Números de página
    for ($i = 1; $i <= $totalPages; $i++) {
        if ($i == $currentPage) {
            $html .= '<span class="pagination-current">' . $i . '</span>';
        } else {
            $html .= '<a href="' . $baseUrl . '?page=' . $i . '" class="pagination-link">' . $i . '</a>';
        }
    }
    
    // Botón siguiente
    if ($currentPage < $totalPages) {
        $html .= '<a href="' . $baseUrl . '?page=' . ($currentPage + 1) . '" class="pagination-btn">Siguiente</a>';
    }
    
    $html .= '</div>';
    return $html;
}
