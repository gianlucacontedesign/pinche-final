<?php
/**
 * Funciones Auxiliares Globales
 * Pinche Supplies - Sistema de E-commerce
 */

/**
 * Escapar HTML para prevenir XSS
 */
if (!function_exists('e')) {
    function e($string) {
        return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
    }
}

/**
 * Formatear precio en pesos argentinos
 */
if (!function_exists('formatPrice')) {
    function formatPrice($price) {
        return '$' . number_format($price, 2, ',', '.');
    }
}

/**
 * Obtener configuración del sitio
 */
if (!function_exists('getSetting')) {
    function getSetting($key, $default = null) {
        static $settings = null;
        
        if ($settings === null) {
            try {
                $db = Database::getInstance()->getConnection();
                $stmt = $db->query("SELECT setting_key, setting_value FROM settings");
                $settings = [];
                
                while ($row = $stmt->fetch()) {
                    $settings[$row['setting_key']] = $row['setting_value'];
                }
            } catch (Exception $e) {
                error_log("getSetting Error: " . $e->getMessage());
                $settings = [];
            }
        }
        
        return $settings[$key] ?? $default;
    }
}

/**
 * Generar URL amigable (slug)
 */
if (!function_exists('generateSlug')) {
    function generateSlug($string) {
        $string = mb_strtolower($string, 'UTF-8');
        
        // Reemplazar acentos
        $replacements = [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'ñ' => 'n', 'ü' => 'u',
            'Á' => 'a', 'É' => 'e', 'Í' => 'i', 'Ó' => 'o', 'Ú' => 'u',
            'Ñ' => 'n', 'Ü' => 'u'
        ];
        $string = strtr($string, $replacements);
        
        // Remover caracteres especiales
        $string = preg_replace('/[^a-z0-9\s-]/', '', $string);
        $string = preg_replace('/[\s-]+/', '-', $string);
        $string = trim($string, '-');
        
        return $string;
    }
}

/**
 * Redireccionar con mensaje
 */
if (!function_exists('redirect')) {
    function redirect($url, $message = null, $type = 'success') {
        if ($message) {
            $_SESSION['flash_message'] = $message;
            $_SESSION['flash_type'] = $type;
        }
        header("Location: $url");
        exit;
    }
}

/**
 * Mostrar mensaje flash
 */
if (!function_exists('getFlashMessage')) {
    function getFlashMessage() {
        if (isset($_SESSION['flash_message'])) {
            $message = $_SESSION['flash_message'];
            $type = $_SESSION['flash_type'] ?? 'info';
            
            unset($_SESSION['flash_message']);
            unset($_SESSION['flash_type']);
            
            return ['message' => $message, 'type' => $type];
        }
        return null;
    }
}

/**
 * Verificar si usuario está logueado
 */
if (!function_exists('isLoggedIn')) {
    function isLoggedIn() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
}

/**
 * Obtener usuario actual
 */
if (!function_exists('getCurrentUser')) {
    function getCurrentUser() {
        if (!isLoggedIn()) {
            return null;
        }
        
        static $user = null;
        
        if ($user === null) {
            try {
                $db = Database::getInstance()->getConnection();
                $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $user = $stmt->fetch();
            } catch (Exception $e) {
                error_log("getCurrentUser Error: " . $e->getMessage());
                return null;
            }
        }
        
        return $user;
    }
}

/**
 * Truncar texto
 */
if (!function_exists('truncate')) {
    function truncate($text, $length = 100, $suffix = '...') {
        if (mb_strlen($text) <= $length) {
            return $text;
        }
        
        return mb_substr($text, 0, $length) . $suffix;
    }
}

/**
 * Formatear fecha en español
 */
if (!function_exists('formatDate')) {
    function formatDate($date, $format = 'd/m/Y H:i') {
        if (empty($date)) {
            return '';
        }
        
        $timestamp = is_numeric($date) ? $date : strtotime($date);
        return date($format, $timestamp);
    }
}

/**
 * Calcular descuento
 */
if (!function_exists('calculateDiscount')) {
    function calculateDiscount($originalPrice, $discountPercent) {
        return $originalPrice - ($originalPrice * ($discountPercent / 100));
    }
}

/**
 * Validar email
 */
if (!function_exists('isValidEmail')) {
    function isValidEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}

/**
 * Sanitizar input
 */
if (!function_exists('sanitize')) {
    function sanitize($data) {
        if (is_array($data)) {
            return array_map('sanitize', $data);
        }
        
        return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
    }
}

/**
 * Debug helper (solo en desarrollo)
 */
if (!function_exists('dd')) {
    function dd($var) {
        echo '<pre>';
        var_dump($var);
        echo '</pre>';
        die();
    }
}

/**
 * Generar token CSRF
 */
if (!function_exists('generateCsrfToken')) {
    function generateCsrfToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

/**
 * Verificar token CSRF
 */
if (!function_exists('verifyCsrfToken')) {
    function verifyCsrfToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}
?>
