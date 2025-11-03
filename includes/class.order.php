<?php
/**
 * Clase Order
 * Maneja las operaciones relacionadas con pedidos
 */

class Order {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function create($orderData, $items) {
        try {
            $this->db->beginTransaction();
            
            // Generar número de orden
            $orderData['order_number'] = generateOrderNumber();
            
            // Crear orden
            $orderId = $this->db->insert('orders', $orderData);
            
            if (!$orderId) {
                throw new Exception('Error al crear la orden');
            }
            
            // Crear items de la orden
            foreach ($items as $item) {
                $orderItem = [
                    'order_id' => $orderId,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['name'],
                    'variant_info' => $item['variant'] ? json_encode($item['variant']) : null,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['subtotal']
                ];
                
                $this->db->insert('order_items', $orderItem);
                
                // Reducir stock
                $productModel = new Product();
                $productModel->updateStock($item['product_id'], $item['quantity'], 'subtract');
            }
            
            $this->db->commit();
            
            return ['success' => true, 'order_id' => $orderId, 'order_number' => $orderData['order_number']];
            
        } catch (Exception $e) {
            $this->db->rollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function getAll($params = []) {
        $sql = "SELECT * FROM orders WHERE 1=1";
        $sqlParams = [];
        
        if (isset($params['status'])) {
            $sql .= " AND status = ?";
            $sqlParams[] = $params['status'];
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        if (isset($params['limit'])) {
            $sql .= " LIMIT " . (int)$params['limit'];
        }
        
        return $this->db->fetchAll($sql, $sqlParams);
    }
    
    public function getById($id) {
        $sql = "SELECT * FROM orders WHERE id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }
    
    public function getItems($orderId) {
        $sql = "SELECT * FROM order_items WHERE order_id = ?";
        return $this->db->fetchAll($sql, [$orderId]);
    }
    
    public function updateStatus($orderId, $status) {
        return $this->db->update('orders', ['status' => $status], 'id = ?', [$orderId]);
    }
}
