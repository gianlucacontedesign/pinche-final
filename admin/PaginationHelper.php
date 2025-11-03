<?php
/**
 * CLASE UTILITY PARA PAGINACIÓN
 * Utilidad reutilizable para manejar paginación en cualquier lista de datos
 */

class PaginationHelper {
    
    private $db;
    private $table;
    private $conditions;
    private $order_by;
    private $page_size;
    private $current_page;
    
    public function __construct($table, $page_size = 15, $conditions = '', $order_by = 'id DESC') {
        $this->db = Database::getInstance();
        $this->table = $table;
        $this->conditions = $conditions;
        $this->order_by = $order_by;
        $this->page_size = $page_size;
        $this->current_page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    }
    
    /**
     * Obtiene datos paginados
     */
    public function getPaginatedData() {
        $offset = ($this->current_page - 1) * $this->page_size;
        
        // Consulta total de registros
        $total_query = "SELECT COUNT(*) as total FROM {$this->table}";
        if ($this->conditions) {
            $total_query .= " WHERE " . $this->conditions;
        }
        
        $total_records = $this->db->fetchOne($total_query)['total'];
        $total_pages = ceil($total_records / $this->page_size);
        
        // Consulta con paginación
        $data_query = "SELECT * FROM {$this->table}";
        if ($this->conditions) {
            $data_query .= " WHERE " . $this->conditions;
        }
        $data_query .= " ORDER BY {$this->order_by} LIMIT {$this->page_size} OFFSET {$offset}";
        
        $data = $this->db->fetchAll($data_query);
        
        return [
            'data' => $data,
            'total_records' => $total_records,
            'total_pages' => $total_pages,
            'current_page' => $this->current_page,
            'page_size' => $this->page_size,
            'start_record' => $offset + 1,
            'end_record' => min($offset + $this->page_size, $total_records)
        ];
    }
    
    /**
     * Genera HTML de paginación
     */
    public function generatePaginationHTML($base_url = '') {
        $result = $this->getPaginatedData();
        $current_page = $result['current_page'];
        $total_pages = $result['total_pages'];
        
        if ($total_pages <= 1) {
            return ''; // No mostrar paginación si solo hay una página
        }
        
        $html = '<nav aria-label="Paginación"><ul class="pagination justify-content-center">';
        
        // Primera página
        $html .= '<li class="page-item ' . ($current_page <= 1 ? 'disabled' : '') . '">';
        $html .= '<a class="page-link" href="' . $base_url . '?page=1" tabindex="-1">';
        $html .= '<i class="fas fa-angle-double-left"></i></a></li>';
        
        // Página anterior
        $html .= '<li class="page-item ' . ($current_page <= 1 ? 'disabled' : '') . '">';
        $html .= '<a class="page-link" href="' . $base_url . '?page=' . ($current_page - 1) . '" tabindex="-1">';
        $html .= '<i class="fas fa-angle-left"></i> Anterior</a></li>';
        
        // Números de página (mostrar 5 páginas máximo)
        $start = max(1, $current_page - 2);
        $end = min($total_pages, $current_page + 2);
        
        for ($i = $start; $i <= $end; $i++) {
            $active_class = ($i == $current_page) ? 'active' : '';
            $html .= '<li class="page-item ' . $active_class . '">';
            $html .= '<a class="page-link" href="' . $base_url . '?page=' . $i . '">' . $i . '</a></li>';
        }
        
        // Página siguiente
        $html .= '<li class="page-item ' . ($current_page >= $total_pages ? 'disabled' : '') . '">';
        $html .= '<a class="page-link" href="' . $base_url . '?page=' . ($current_page + 1) . '">';
        $html .= 'Siguiente <i class="fas fa-angle-right"></i></a></li>';
        
        // Última página
        $html .= '<li class="page-item ' . ($current_page >= $total_pages ? 'disabled' : '') . '">';
        $html .= '<a class="page-link" href="' . $base_url . '?page=' . $total_pages . '">';
        $html .= '<i class="fas fa-angle-double-right"></i></a></li>';
        
        $html .= '</ul></nav>';
        
        // Información adicional
        $html .= '<div class="text-center mt-2">';
        $html .= '<small class="text-muted">Página ' . $current_page . ' de ' . $total_pages . '</small>';
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Genera información de registros mostrados
     */
    public function getRecordsInfo() {
        $result = $this->getPaginatedData();
        
        if ($result['total_records'] == 0) {
            return 'No hay registros';
        }
        
        return 'Mostrando ' . $result['start_record'] . ' - ' . $result['end_record'] . 
               ' de ' . $result['total_records'] . ' registros';
    }
    
    /**
     * Obtiene el número de página actual
     */
    public function getCurrentPage() {
        return $this->current_page;
    }
    
    /**
     * Obtiene el total de páginas
     */
    public function getTotalPages() {
        $result = $this->getPaginatedData();
        return $result['total_pages'];
    }
    
    /**
     * Obtiene el total de registros
     */
    public function getTotalRecords() {
        $result = $this->getPaginatedData();
        return $result['total_records'];
    }
    
    /**
     * Verifica si hay página anterior
     */
    public function hasPreviousPage() {
        return $this->current_page > 1;
    }
    
    /**
     * Verifica si hay página siguiente
     */
    public function hasNextPage() {
        $result = $this->getPaginatedData();
        return $this->current_page < $result['total_pages'];
    }
    
    /**
     * Obtiene la URL para una página específica
     */
    public function getPageUrl($page) {
        return '?page=' . $page;
    }
}

/**
 * EJEMPLO DE USO:
 * 
 * // Para productos
 * $pagination = new PaginationHelper('products WHERE is_active = 1', 15, 'created_at DESC');
 * $result = $pagination->getPaginatedData();
 * 
 * echo $pagination->generatePaginationHTML('products.php');
 * echo '<p>' . $pagination->getRecordsInfo() . '</p>';
 * 
 * // Para categorías
 * $pagination = new PaginationHelper('categories WHERE is_active = 1', 10, 'name ASC');
 * $result = $pagination->getPaginatedData();
 * 
 * // Para pedidos
 * $pagination = new PaginationHelper('orders', 20, 'created_at DESC', 'id');
 * $result = $pagination->getPaginatedData();
 */