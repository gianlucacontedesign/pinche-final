<?php
/**
 * Clase Cart
 * Maneja el carrito de compras en sesión
 */

class Cart {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }
    
    public function add($productId, $quantity = 1, $variantId = null) {
        $product = new Product();
        $productData = $product->getById($productId);
        
        if (!$productData || $productData['is_active'] != 1) {
            return ['success' => false, 'message' => 'Producto no disponible'];
        }
        
        if ($productData['stock'] < $quantity) {
            return ['success' => false, 'message' => 'Stock insuficiente'];
        }
        
        $cartKey = $productId . ($variantId ? '_' . $variantId : '');
        
        if (isset($_SESSION['cart'][$cartKey])) {
            $_SESSION['cart'][$cartKey]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$cartKey] = [
                'product_id' => $productId,
                'variant_id' => $variantId,
                'quantity' => $quantity
            ];
        }
        
        return ['success' => true, 'message' => 'Producto agregado al carrito'];
    }
    
    public function update($cartKey, $quantity) {
        if (isset($_SESSION['cart'][$cartKey])) {
            if ($quantity <= 0) {
                unset($_SESSION['cart'][$cartKey]);
            } else {
                $_SESSION['cart'][$cartKey]['quantity'] = $quantity;
            }
            return ['success' => true];
        }
        return ['success' => false, 'message' => 'Item no encontrado'];
    }
    
    public function remove($cartKey) {
        if (isset($_SESSION['cart'][$cartKey])) {
            unset($_SESSION['cart'][$cartKey]);
            return ['success' => true];
        }
        return ['success' => false, 'message' => 'Item no encontrado'];
    }
    
    public function clear() {
        $_SESSION['cart'] = [];
    }
    
    public function getItems() {
        if (empty($_SESSION['cart'])) {
            return [];
        }
        
        $items = [];
        $product = new Product();
        
        foreach ($_SESSION['cart'] as $cartKey => $item) {
            $productData = $product->getById($item['product_id']);
            
            if ($productData) {
                $price = $productData['price'];
                $variantInfo = null;
                
                if ($item['variant_id']) {
                    $sql = "SELECT * FROM product_variants WHERE id = ?";
                    $variant = $this->db->fetchOne($sql, [$item['variant_id']]);
                    if ($variant) {
                        $price += $variant['price_modifier'];
                        $variantInfo = $variant;
                    }
                }
                
                $images = $product->getImages($productData['id']);
                $primaryImage = !empty($images) ? $images[0]['image_path'] : null;
                
                $items[$cartKey] = [
                    'cart_key' => $cartKey,
                    'product_id' => $productData['id'],
                    'name' => $productData['name'],
                    'slug' => $productData['slug'],
                    'price' => $price,
                    'quantity' => $item['quantity'],
                    'subtotal' => $price * $item['quantity'],
                    'image' => $primaryImage,
                    'variant' => $variantInfo,
                    'stock' => $productData['stock']
                ];
            }
        }
        
        return $items;
    }
    
    public function getCount() {
        $count = 0;
        foreach ($_SESSION['cart'] as $item) {
            $count += $item['quantity'];
        }
        return $count;
    }
    
    public function getSubtotal() {
        $subtotal = 0;
        $items = $this->getItems();
        
        foreach ($items as $item) {
            $subtotal += $item['subtotal'];
        }
        
        return $subtotal;
    }
    
    public function getTotal() {
        $subtotal = $this->getSubtotal();
        
        // Obtener configuración de impuestos y envío
        $sql = "SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('tax_rate', 'shipping_cost', 'free_shipping_threshold')";
        $settings = $this->db->fetchAll($sql);
        
        $taxRate = 0;
        $shippingCost = 0;
        $freeShippingThreshold = 0;
        
        foreach ($settings as $setting) {
            if ($setting['setting_key'] === 'tax_rate') {
                $taxRate = (float)$setting['setting_value'] / 100;
            } elseif ($setting['setting_key'] === 'shipping_cost') {
                $shippingCost = (float)$setting['setting_value'];
            } elseif ($setting['setting_key'] === 'free_shipping_threshold') {
                $freeShippingThreshold = (float)$setting['setting_value'];
            }
        }
        
        // Envío gratis si supera el umbral
        if ($subtotal >= $freeShippingThreshold) {
            $shippingCost = 0;
        }
        
        $tax = $subtotal * $taxRate;
        $total = $subtotal + $tax + $shippingCost;
        
        return [
            'subtotal' => $subtotal,
            'tax' => $tax,
            'shipping' => $shippingCost,
            'total' => $total
        ];
    }
}
