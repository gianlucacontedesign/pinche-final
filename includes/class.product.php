<?php
/**
 * Clase Product
 * Maneja las operaciones relacionadas con productos
 */

class Product {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function getAll($params = []) {
        $sql = "SELECT p.*, c.name as category_name,
                (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image,
                (SELECT COUNT(*) FROM product_images WHERE product_id = p.id) as image_count
                FROM products p
                INNER JOIN categories c ON p.category_id = c.id
                WHERE 1=1";
        $sqlParams = [];
        
        if (isset($params['category_id'])) {
            $sql .= " AND p.category_id = ?";
            $sqlParams[] = $params['category_id'];
        }
        
        if (isset($params['active_only']) && $params['active_only']) {
            $sql .= " AND p.is_active = 1";
        }
        
        if (isset($params['featured_only']) && $params['featured_only']) {
            $sql .= " AND p.is_featured = 1";
        }
        
        if (isset($params['new_only']) && $params['new_only']) {
            $sql .= " AND p.is_new = 1";
        }
        
        if (isset($params['search']) && !empty($params['search'])) {
            $sql .= " AND (p.name LIKE ? OR p.description LIKE ? OR p.sku LIKE ?)";
            $searchTerm = '%' . $params['search'] . '%';
            $sqlParams[] = $searchTerm;
            $sqlParams[] = $searchTerm;
            $sqlParams[] = $searchTerm;
        }
        
        if (isset($params['low_stock']) && $params['low_stock']) {
            $sql .= " AND p.stock <= p.min_stock";
        }
        
        // Ordenamiento
        $orderBy = isset($params['order_by']) ? $params['order_by'] : 'p.created_at';
        $orderDir = isset($params['order_dir']) ? $params['order_dir'] : 'DESC';
        $sql .= " ORDER BY {$orderBy} {$orderDir}";
        
        // Paginación
        if (isset($params['limit'])) {
            $limit = (int)$params['limit'];
            $offset = isset($params['offset']) ? (int)$params['offset'] : 0;
            $sql .= " LIMIT {$limit} OFFSET {$offset}";
        }
        
        return $this->db->fetchAll($sql, $sqlParams);
    }
    
    public function getCount($params = []) {
        $sql = "SELECT COUNT(*) as count FROM products p WHERE 1=1";
        $sqlParams = [];
        
        if (isset($params['category_id'])) {
            $sql .= " AND p.category_id = ?";
            $sqlParams[] = $params['category_id'];
        }
        
        if (isset($params['active_only']) && $params['active_only']) {
            $sql .= " AND p.is_active = 1";
        }
        
        if (isset($params['search']) && !empty($params['search'])) {
            $sql .= " AND (p.name LIKE ? OR p.description LIKE ? OR p.sku LIKE ?)";
            $searchTerm = '%' . $params['search'] . '%';
            $sqlParams[] = $searchTerm;
            $sqlParams[] = $searchTerm;
            $sqlParams[] = $searchTerm;
        }
        
        $result = $this->db->fetchOne($sql, $sqlParams);
        return $result['count'];
    }
    
    public function getById($id) {
        $sql = "SELECT p.*, c.name as category_name
                FROM products p
                INNER JOIN categories c ON p.category_id = c.id
                WHERE p.id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }
    
    public function getBySlug($slug) {
        $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug
                FROM products p
                INNER JOIN categories c ON p.category_id = c.id
                WHERE p.slug = ? AND p.is_active = 1";
        return $this->db->fetchOne($sql, [$slug]);
    }
    
    public function getImages($productId) {
        $sql = "SELECT * FROM product_images WHERE product_id = ? ORDER BY display_order ASC, is_primary DESC";
        return $this->db->fetchAll($sql, [$productId]);
    }
    
    public function getVariants($productId) {
        $sql = "SELECT * FROM product_variants WHERE product_id = ? ORDER BY name ASC";
        return $this->db->fetchAll($sql, [$productId]);
    }
    
    public function create($data) {
        $slug = $this->generateSlug($data['name']);
        $data['slug'] = $slug;
        
        return $this->db->insert('products', $data);
    }
    
    public function update($id, $data) {
        if (isset($data['name'])) {
            $data['slug'] = $this->generateSlug($data['name'], $id);
        }
        
        return $this->db->update('products', $data, 'id = ?', [$id]);
    }
    
    public function delete($id) {
        // Las imágenes se eliminan automáticamente por CASCADE
        $success = $this->db->delete('products', 'id = ?', [$id]);
        return ['success' => $success, 'message' => $success ? 'Producto eliminado' : 'Error al eliminar'];
    }
    
    public function addImage($productId, $imagePath, $isPrimary = false) {
        // Si es imagen principal, desmarcar otras
        if ($isPrimary) {
            $this->db->update('product_images', 
                ['is_primary' => 0], 
                'product_id = ?', 
                [$productId]
            );
        }
        
        $data = [
            'product_id' => $productId,
            'image_path' => $imagePath,
            'is_primary' => $isPrimary ? 1 : 0,
            'display_order' => 99
        ];
        
        return $this->db->insert('product_images', $data);
    }
    
    public function deleteImage($imageId) {
        $image = $this->db->fetchOne("SELECT * FROM product_images WHERE id = ?", [$imageId]);
        if ($image) {
            // Eliminar archivo físico
            $filePath = ROOT_PATH . '/' . $image['image_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            
            return $this->db->delete('product_images', 'id = ?', [$imageId]);
        }
        return false;
    }
    
    public function updateStock($productId, $quantity, $operation = 'set') {
        if ($operation === 'add') {
            $sql = "UPDATE products SET stock = stock + ? WHERE id = ?";
        } elseif ($operation === 'subtract') {
            $sql = "UPDATE products SET stock = stock - ? WHERE id = ?";
        } else {
            $sql = "UPDATE products SET stock = ? WHERE id = ?";
        }
        
        return $this->db->query($sql, [$quantity, $productId]) !== false;
    }
    
    public function incrementViews($productId) {
        $sql = "UPDATE products SET views = views + 1 WHERE id = ?";
        return $this->db->query($sql, [$productId]) !== false;
    }
    
    private function generateSlug($name, $excludeId = null) {
        $slug = strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');
        
        $sql = "SELECT COUNT(*) as count FROM products WHERE slug = ?";
        $params = [$slug];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $result = $this->db->fetchOne($sql, $params);
        
        if ($result['count'] > 0) {
            $slug .= '-' . time();
        }
        
        return $slug;
    }
}
