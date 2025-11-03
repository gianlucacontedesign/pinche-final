<?php
/**
 * Clase Category
 * Maneja las operaciones relacionadas con categorías
 */

class Category {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function getAll($activeOnly = false) {
        $sql = "SELECT * FROM categories";
        if ($activeOnly) {
            $sql .= " WHERE is_active = 1";
        }
        $sql .= " ORDER BY display_order ASC, name ASC";
        
        return $this->db->fetchAll($sql);
    }
    
    public function getById($id) {
        $sql = "SELECT * FROM categories WHERE id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }
    
    public function getBySlug($slug) {
        $sql = "SELECT * FROM categories WHERE slug = ? AND is_active = 1";
        return $this->db->fetchOne($sql, [$slug]);
    }
    
    public function getTree($activeOnly = false) {
        $categories = $this->getAll($activeOnly);
        return $this->buildTree($categories);
    }
    
    private function buildTree($categories, $parentId = null) {
        $branch = [];
        
        foreach ($categories as $category) {
            if ($category['parent_id'] == $parentId) {
                $children = $this->buildTree($categories, $category['id']);
                if ($children) {
                    $category['children'] = $children;
                }
                $branch[] = $category;
            }
        }
        
        return $branch;
    }
    
    public function getParentCategories() {
        $sql = "SELECT * FROM categories WHERE parent_id IS NULL ORDER BY display_order ASC, name ASC";
        return $this->db->fetchAll($sql);
    }
    
    public function getChildren($parentId) {
        $sql = "SELECT * FROM categories WHERE parent_id = ? ORDER BY display_order ASC, name ASC";
        return $this->db->fetchAll($sql, [$parentId]);
    }
    
    public function create($data) {
        $slug = $this->generateSlug($data['name']);
        $data['slug'] = $slug;
        
        return $this->db->insert('categories', $data);
    }
    
    public function update($id, $data) {
        if (isset($data['name'])) {
            $data['slug'] = $this->generateSlug($data['name'], $id);
        }
        
        return $this->db->update('categories', $data, 'id = ?', [$id]);
    }
    
    public function delete($id) {
        // Verificar si tiene productos asociados
        $sql = "SELECT COUNT(*) as count FROM products WHERE category_id = ?";
        $result = $this->db->fetchOne($sql, [$id]);
        
        if ($result['count'] > 0) {
            return ['success' => false, 'message' => 'No se puede eliminar: tiene productos asociados'];
        }
        
        // Verificar si tiene subcategorías
        $sql = "SELECT COUNT(*) as count FROM categories WHERE parent_id = ?";
        $result = $this->db->fetchOne($sql, [$id]);
        
        if ($result['count'] > 0) {
            return ['success' => false, 'message' => 'No se puede eliminar: tiene subcategorías'];
        }
        
        $success = $this->db->delete('categories', 'id = ?', [$id]);
        return ['success' => $success, 'message' => $success ? 'Categoría eliminada' : 'Error al eliminar'];
    }
    
    public function getProductCount($categoryId) {
        $sql = "SELECT COUNT(*) as count FROM products WHERE category_id = ? AND is_active = 1";
        $result = $this->db->fetchOne($sql, [$categoryId]);
        return $result['count'];
    }
    
    private function generateSlug($name, $excludeId = null) {
        $slug = strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');
        
        // Verificar si el slug ya existe
        $sql = "SELECT COUNT(*) as count FROM categories WHERE slug = ?";
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
