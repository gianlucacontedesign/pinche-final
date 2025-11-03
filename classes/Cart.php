<?php
/**
 * Cart Class - Gestión del Carrito de Compras
 * Pinche Supplies - Sistema de E-commerce
 */

class Cart {
    private $db;
    private $sessionKey = 'shopping_cart';
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        
        // Iniciar sesión si no está iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Inicializar carrito en sesión
        if (!isset($_SESSION[$this->sessionKey])) {
            $_SESSION[$this->sessionKey] = [];
        }
    }
    
    /**
     * Agregar producto al carrito
     */
    public function add($productId, $quantity = 1, $options = []) {
        try {
            $productId = (int)$productId;
            $quantity = (int)$quantity;
            
            if ($quantity <= 0) {
                return ['success' => false, 'message' => 'Cantidad inválida'];
            }
            
            // Verificar que el producto existe
            $product = new Product();
            $productData = $product->getById($productId);
            
            if (!$productData) {
                return ['success' => false, 'message' => 'Producto no encontrado'];
            }
            
            // Verificar stock
            if (!$product->hasStock($productId, $quantity)) {
                return ['success' => false, 'message' => 'Stock insuficiente'];
            }
            
            // Generar clave única para el item (por si hay variaciones)
            $itemKey = $this->generateItemKey($productId, $options);
            
            // Si ya existe, sumar cantidad
            if (isset($_SESSION[$this->sessionKey][$itemKey])) {
                $_SESSION[$this->sessionKey][$itemKey]['quantity'] += $quantity;
            } else {
                $_SESSION[$this->sessionKey][$itemKey] = [
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'options' => $options,
                    'added_at' => time()
                ];
            }
            
            return [
                'success' => true, 
                'message' => 'Producto agregado al carrito',
                'cart_count' => $this->getItemCount()
            ];
            
        } catch (Exception $e) {
            error_log("Cart::add Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al agregar producto'];
        }
    }
    
    /**
     * Actualizar cantidad de un producto
     */
    public function update($itemKey, $quantity) {
        try {
            $quantity = (int)$quantity;
            
            if (!isset($_SESSION[$this->sessionKey][$itemKey])) {
                return ['success' => false, 'message' => 'Item no encontrado'];
            }
            
            if ($quantity <= 0) {
                return $this->remove($itemKey);
            }
            
            // Verificar stock
            $productId = $_SESSION[$this->sessionKey][$itemKey]['product_id'];
            $product = new Product();
            
            if (!$product->hasStock($productId, $quantity)) {
                return ['success' => false, 'message' => 'Stock insuficiente'];
            }
            
            $_SESSION[$this->sessionKey][$itemKey]['quantity'] = $quantity;
            
            return [
                'success' => true, 
                'message' => 'Cantidad actualizada',
                'cart_total' => $this->getTotal()
            ];
            
        } catch (Exception $e) {
            error_log("Cart::update Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al actualizar cantidad'];
        }
    }
    
    /**
     * Remover producto del carrito
     */
    public function remove($itemKey) {
        if (isset($_SESSION[$this->sessionKey][$itemKey])) {
            unset($_SESSION[$this->sessionKey][$itemKey]);
            return [
                'success' => true, 
                'message' => 'Producto removido',
                'cart_count' => $this->getItemCount()
            ];
        }
        return ['success' => false, 'message' => 'Item no encontrado'];
    }
    
    /**
     * Vaciar carrito
     */
    public function clear() {
        $_SESSION[$this->sessionKey] = [];
        return ['success' => true, 'message' => 'Carrito vaciado'];
    }
    
    /**
     * Obtener items del carrito con información completa
     */
    public function getItems() {
        if (empty($_SESSION[$this->sessionKey])) {
            return [];
        }
        
        $items = [];
        $product = new Product();
        
        foreach ($_SESSION[$this->sessionKey] as $key => $item) {
            $productData = $product->getById($item['product_id']);
            
            if ($productData) {
                $items[$key] = array_merge($item, [
                    'name' => $productData['name'],
                    'price' => $productData['price'],
                    'image' => $productData['image'],
                    'slug' => $productData['slug'],
                    'subtotal' => $productData['price'] * $item['quantity']
                ]);
            }
        }
        
        return $items;
    }
    
    /**
     * Obtener cantidad total de items
     */
    public function getItemCount() {
        $count = 0;
        foreach ($_SESSION[$this->sessionKey] as $item) {
            $count += $item['quantity'];
        }
        return $count;
    }
    
    /**
     * Obtener total del carrito
     */
    public function getTotal() {
        $total = 0;
        $items = $this->getItems();
        
        foreach ($items as $item) {
            $total += $item['subtotal'];
        }
        
        return $total;
    }
    
    /**
     * Verificar si el carrito está vacío
     */
    public function isEmpty() {
        return empty($_SESSION[$this->sessionKey]);
    }
    
    /**
     * Generar clave única para item
     */
    private function generateItemKey($productId, $options = []) {
        if (empty($options)) {
            return 'product_' . $productId;
        }
        
        ksort($options);
        return 'product_' . $productId . '_' . md5(json_encode($options));
    }
    
    /**
     * Validar disponibilidad de todos los productos
     */
    public function validateStock() {
        $product = new Product();
        $errors = [];
        
        foreach ($_SESSION[$this->sessionKey] as $key => $item) {
            if (!$product->hasStock($item['product_id'], $item['quantity'])) {
                $productData = $product->getById($item['product_id']);
                $errors[] = $productData['name'] . ' - Stock insuficiente';
            }
        }
        
        return empty($errors) ? ['valid' => true] : ['valid' => false, 'errors' => $errors];
    }
}
?>
