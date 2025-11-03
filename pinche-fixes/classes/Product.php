<?php
/**
 * Product Model - Gestión de Productos
 * Pinche Supplies - Sistema de E-commerce
 */

class Product {
    private $db;
    private $table = 'products';
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Obtener todos los productos con filtros
     */
    public function getAll($options = []) {
        try {
            $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug 
                    FROM {$this->table} p 
                    LEFT JOIN categories c ON p.category_id = c.id 
                    WHERE 1=1";
            $params = [];
            
            // Filtrar solo activos
            if (isset($options['active_only']) && $options['active_only']) {
                $sql .= " AND p.active = 1";
            }
            
            // Filtrar destacados
            if (isset($options['featured_only']) && $options['featured_only']) {
                $sql .= " AND p.featured = 1";
            }
            
            // Filtrar nuevos
            if (isset($options['new_only']) && $options['new_only']) {
                $sql .= " AND p.is_new = 1";
            }
            
            // Filtrar por categoría
            if (isset($options['category_id'])) {
                $sql .= " AND p.category_id = :category_id";
                $params[':category_id'] = $options['category_id'];
            }
            
            // Búsqueda por término
            if (isset($options['search']) && !empty($options['search'])) {
                $sql .= " AND (p.name LIKE :search OR p.description LIKE :search OR p.sku LIKE :search)";
                $params[':search'] = '%' . $options['search'] . '%';
            }
            
            // Filtrar por rango de precio
            if (isset($options['min_price'])) {
                $sql .= " AND p.price >= :min_price";
                $params[':min_price'] = $options['min_price'];
            }
            
            if (isset($options['max_price'])) {
                $sql .= " AND p.price <= :max_price";
                $params[':max_price'] = $options['max_price'];
            }
            
            // Ordenar
            $orderBy = $options['order_by'] ?? 'created_at';
            $orderDir = $options['order_dir'] ?? 'DESC';
            $sql .= " ORDER BY p.{$orderBy} {$orderDir}";
            
            // Límite y offset
            if (isset($options['limit'])) {
                $sql .= " LIMIT :limit";
                if (isset($options['offset'])) {
                    $sql .= " OFFSET :offset";
                }
            }
            
            $stmt = $this->db->prepare($sql);
            
            // Bind parámetros
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            if (isset($options['limit'])) {
                $stmt->bindValue(':limit', (int)$options['limit'], PDO::PARAM_INT);
                if (isset($options['offset'])) {
                    $stmt->bindValue(':offset', (int)$options['offset'], PDO::PARAM_INT);
                }
            }
            
            $stmt->execute();
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            error_log("Product::getAll Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener producto por ID
     */
    public function getById($id) {
        try {
            $stmt = $this->db->prepare("
                SELECT p.*, c.name as category_name, c.slug as category_slug 
                FROM {$this->table} p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.id = ? AND p.active = 1
            ");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Product::getById Error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Obtener producto por slug
     */
    public function getBySlug($slug) {
        try {
            $stmt = $this->db->prepare("
                SELECT p.*, c.name as category_name, c.slug as category_slug 
                FROM {$this->table} p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.slug = ? AND p.active = 1
            ");
            $stmt->execute([$slug]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Product::getBySlug Error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Obtener productos relacionados
     */
    public function getRelated($productId, $categoryId, $limit = 4) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM {$this->table} 
                WHERE category_id = ? AND id != ? AND active = 1 
                ORDER BY RAND() 
                LIMIT ?
            ");
            $stmt->execute([$categoryId, $productId, $limit]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Product::getRelated Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Buscar productos
     */
    public function search($query, $limit = 20) {
        return $this->getAll([
            'search' => $query,
            'active_only' => true,
            'limit' => $limit
        ]);
    }
    
    /**
     * Verificar stock disponible
     */
    public function hasStock($productId, $quantity = 1) {
        try {
            $stmt = $this->db->prepare("SELECT stock FROM {$this->table} WHERE id = ?");
            $stmt->execute([$productId]);
            $product = $stmt->fetch();
            
            if (!$product) {
                return false;
            }
            
            // Si stock es NULL, consideramos stock ilimitado
            if ($product['stock'] === null) {
                return true;
            }
            
            return (int)$product['stock'] >= $quantity;
            
        } catch (PDOException $e) {
            error_log("Product::hasStock Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualizar stock
     */
    public function updateStock($productId, $quantity, $operation = 'subtract') {
        try {
            if ($operation === 'subtract') {
                $sql = "UPDATE {$this->table} SET stock = stock - ? WHERE id = ? AND stock >= ?";
                $stmt = $this->db->prepare($sql);
                return $stmt->execute([$quantity, $productId, $quantity]);
            } else {
                $sql = "UPDATE {$this->table} SET stock = stock + ? WHERE id = ?";
                $stmt = $this->db->prepare($sql);
                return $stmt->execute([$quantity, $productId]);
            }
        } catch (PDOException $e) {
            error_log("Product::updateStock Error: " . $e->getMessage());
            return false;
        }
    }
}
?>
