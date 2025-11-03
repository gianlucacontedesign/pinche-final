<?php
/**
 * Category Model - Gestión de Categorías
 * Pinche Supplies - Sistema de E-commerce
 */

class Category {
    private $db;
    private $table = 'categories';
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Obtener todas las categorías
     */
    public function getAll($options = []) {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE 1=1";
            $params = [];
            
            // Filtrar solo activas
            if (isset($options['active_only']) && $options['active_only']) {
                $sql .= " AND active = 1";
            }
            
            // Filtrar solo principales (sin padre)
            if (isset($options['parent_only']) && $options['parent_only']) {
                $sql .= " AND (parent_id IS NULL OR parent_id = 0)";
            }
            
            // Ordenar
            $sql .= " ORDER BY display_order ASC, name ASC";
            
            // Límite
            if (isset($options['limit'])) {
                $sql .= " LIMIT :limit";
            }
            
            $stmt = $this->db->prepare($sql);
            
            if (isset($options['limit'])) {
                $stmt->bindValue(':limit', (int)$options['limit'], PDO::PARAM_INT);
            }
            
            $stmt->execute();
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            error_log("Category::getAll Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener categorías principales (sin padre)
     */
    public function getParentCategories() {
        return $this->getAll([
            'active_only' => true,
            'parent_only' => true
        ]);
    }
    
    /**
     * Obtener categoría por ID
     */
    public function getById($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ? AND active = 1");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Category::getById Error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Obtener categoría por slug
     */
    public function getBySlug($slug) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE slug = ? AND active = 1");
            $stmt->execute([$slug]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Category::getBySlug Error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Obtener subcategorías de una categoría
     */
    public function getChildren($parentId) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM {$this->table} 
                WHERE parent_id = ? AND active = 1 
                ORDER BY display_order ASC, name ASC
            ");
            $stmt->execute([$parentId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Category::getChildren Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Contar productos en una categoría
     */
    public function countProducts($categoryId) {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) FROM products 
                WHERE category_id = ? AND active = 1
            ");
            $stmt->execute([$categoryId]);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Category::countProducts Error: " . $e->getMessage());
            return 0;
        }
    }
}
?>
