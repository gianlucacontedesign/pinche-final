<?php
/**
 * Gestore Operazioni AJAX - Pinche Supplies
 * File: admin/ajax-handlers.php
 * Creato: 03 Nov 2025 - 21:36
 * 
 * Questo file gestisce tutte le operazioni AJAX per l'area amministrativa:
 * - Operazioni sui prodotti (CRUD + toggle stato)
 * - Operazioni sulle categorie (CRUD + toggle stato)
 * - Validazione sicurezza
 * - Logging delle operazioni
 * - Gestione errori e risposte JSON
 */

// Impedisce l'accesso diretto
if (!defined('ACCESS_CONTROL')) {
    define('ACCESS_CONTROL', true);
}

// Include configurazione e dipendenze
require_once '../config/config.php';
require_once '../classes/Database.php';
require_once '../includes/functions.php';

// Avvia la sessione se non è già avviata
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Classe per gestire le operazioni AJAX
 */
class AjaxHandler {
    private $db;
    private $user_id;
    private $user_ip;
    private $user_agent;
    
    public function __construct() {
        try {
            $this->db = Database::getInstance();
            $this->user_id = $_SESSION['admin_id'] ?? null;
            $this->user_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $this->user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
            
            // Imposta l'header JSON
            header('Content-Type: application/json; charset=utf-8');
            
            // Verifica il metodo di richiesta
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Metodo di richiesta non valido');
            }
            
            // Verifica la validità della sessione
            if (!$this->isValidSession()) {
                throw new Exception('Sessione non valida o scaduta');
            }
            
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }
    
    /**
     * Verifica se la sessione è valida
     */
    private function isValidSession() {
        if (empty($this->user_id)) {
            return false;
        }
        
        // Verifica timeout sessione
        if (isset($_SESSION['last_activity'])) {
            $elapsed_time = time() - $_SESSION['last_activity'];
            if ($elapsed_time > SESSION_TIMEOUT) {
                session_destroy();
                return false;
            }
        }
        
        $_SESSION['last_activity'] = time();
        return true;
    }
    
    /**
     * Verifica il token CSRF
     */
    private function verifyCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Invia una risposta di successo
     */
    private function sendSuccess($data = [], $message = 'Operazione completata con successo') {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        logActivity('ajax_success', "Operazione AJAX completata: " . ($_POST['action'] ?? 'sconosciuta'), $this->user_id);
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Invia una risposta di errore
     */
    private function sendError($message, $code = 400, $details = []) {
        $response = [
            'success' => false,
            'error' => [
                'message' => $message,
                'code' => $code,
                'details' => $details
            ],
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        logActivity('ajax_error', "Errore AJAX: $message", $this->user_id);
        http_response_code($code);
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Valida i dati di input
     */
    private function validateInput($data, $rules) {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? null;
            
            // Campo obbligatorio
            if ($rule['required'] && (empty($value) && $value !== '0')) {
                $errors[$field] = "Il campo {$rule['label']} è obbligatorio";
                continue;
            }
            
            if (empty($value)) {
                continue;
            }
            
            // Lunghezza minima
            if (isset($rule['min_length']) && strlen($value) < $rule['min_length']) {
                $errors[$field] = "Il campo {$rule['label']} deve avere almeno {$rule['min_length']} caratteri";
            }
            
            // Lunghezza massima
            if (isset($rule['max_length']) && strlen($value) > $rule['max_length']) {
                $errors[$field] = "Il campo {$rule['label']} non può superare {$rule['max_length']} caratteri";
            }
            
            // Pattern regex
            if (isset($rule['pattern']) && !preg_match($rule['pattern'], $value)) {
                $errors[$field] = "Il campo {$rule['label']} ha un formato non valido";
            }
            
            // Numerico
            if (isset($rule['numeric']) && $rule['numeric'] && !is_numeric($value)) {
                $errors[$field] = "Il campo {$rule['label']} deve essere un numero";
            }
            
            // Email
            if (isset($rule['email']) && $rule['email'] && !validateEmail($value)) {
                $errors[$field] = "Il campo {$rule['label']} deve contenere un indirizzo email valido";
            }
        }
        
        return $errors;
    }
    
    /**
     * GESTIONE PRODOTTI
     */
    
    /**
     * Aggiungi un nuovo prodotto
     */
    public function addProduct() {
        // Verifica token CSRF
        if (!$this->verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->sendError('Token di sicurezza non valido', 403);
        }
        
        $data = sanitizeInput($_POST);
        
        $rules = [
            'name' => ['required' => true, 'min_length' => 2, 'max_length' => 255, 'label' => 'Nome'],
            'description' => ['required' => false, 'max_length' => 1000, 'label' => 'Descrizione'],
            'price' => ['required' => true, 'numeric' => true, 'label' => 'Prezzo'],
            'category_id' => ['required' => true, 'numeric' => true, 'label' => 'Categoria'],
            'sku' => ['required' => true, 'pattern' => '/^[A-Z0-9\-]+$/', 'label' => 'SKU'],
            'stock' => ['required' => true, 'numeric' => true, 'label' => 'Magazzino'],
            'weight' => ['required' => false, 'numeric' => true, 'label' => 'Peso'],
            'status' => ['required' => true, 'pattern' => '/^(active|inactive)$/', 'label' => 'Stato']
        ];
        
        $errors = $this->validateInput($data, $rules);
        if (!empty($errors)) {
            $this->sendError('Dati non validi', 422, $errors);
        }
        
        try {
            $this->db->beginTransaction();
            
            // Verifica che lo SKU non esista già
            $existingSku = $this->db->fetchOne("SELECT id FROM products WHERE sku = ?", [$data['sku']]);
            if ($existingSku) {
                $this->db->rollback();
                $this->sendError('SKU già esistente', 409, ['sku' => 'Questo SKU è già stato utilizzato']);
            }
            
            // Inserisci il prodotto
            $query = "INSERT INTO products (name, description, price, category_id, sku, stock, weight, status, created_at, updated_at) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
            
            $params = [
                $data['name'],
                $data['description'] ?? '',
                $data['price'],
                $data['category_id'],
                strtoupper($data['sku']),
                $data['stock'],
                $data['weight'] ?? 0,
                $data['status']
            ];
            
            $productId = $this->db->insert($query, $params);
            
            $this->db->commit();
            
            $this->sendSuccess([
                'product_id' => $productId,
                'sku' => strtoupper($data['sku'])
            ], 'Prodotto aggiunto con successo');
            
        } catch (Exception $e) {
            $this->db->rollback();
            $this->sendError('Errore durante l\'aggiunta del prodotto: ' . $e->getMessage());
        }
    }
    
    /**
     * Modifica un prodotto esistente
     */
    public function editProduct() {
        if (!$this->verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->sendError('Token di sicurezza non valido', 403);
        }
        
        $data = sanitizeInput($_POST);
        
        if (empty($data['product_id'])) {
            $this->sendError('ID prodotto mancante', 422);
        }
        
        $rules = [
            'product_id' => ['required' => true, 'numeric' => true, 'label' => 'ID Prodotto'],
            'name' => ['required' => true, 'min_length' => 2, 'max_length' => 255, 'label' => 'Nome'],
            'description' => ['required' => false, 'max_length' => 1000, 'label' => 'Descrizione'],
            'price' => ['required' => true, 'numeric' => true, 'label' => 'Prezzo'],
            'category_id' => ['required' => true, 'numeric' => true, 'label' => 'Categoria'],
            'sku' => ['required' => true, 'pattern' => '/^[A-Z0-9\-]+$/', 'label' => 'SKU'],
            'stock' => ['required' => true, 'numeric' => true, 'label' => 'Magazzino'],
            'weight' => ['required' => false, 'numeric' => true, 'label' => 'Peso'],
            'status' => ['required' => true, 'pattern' => '/^(active|inactive)$/', 'label' => 'Stato']
        ];
        
        $errors = $this->validateInput($data, $rules);
        if (!empty($errors)) {
            $this->sendError('Dati non validi', 422, $errors);
        }
        
        try {
            $this->db->beginTransaction();
            
            // Verifica che il prodotto esista
            $existingProduct = $this->db->fetchOne("SELECT id FROM products WHERE id = ?", [$data['product_id']]);
            if (!$existingProduct) {
                $this->db->rollback();
                $this->sendError('Prodotto non trovato', 404);
            }
            
            // Verifica che lo SKU non sia già usato da un altro prodotto
            $existingSku = $this->db->fetchOne(
                "SELECT id FROM products WHERE sku = ? AND id != ?", 
                [$data['sku'], $data['product_id']]
            );
            if ($existingSku) {
                $this->db->rollback();
                $this->sendError('SKU già esistente', 409, ['sku' => 'Questo SKU è già stato utilizzato']);
            }
            
            // Aggiorna il prodotto
            $query = "UPDATE products 
                     SET name = ?, description = ?, price = ?, category_id = ?, sku = ?, 
                         stock = ?, weight = ?, status = ?, updated_at = NOW()
                     WHERE id = ?";
            
            $params = [
                $data['name'],
                $data['description'] ?? '',
                $data['price'],
                $data['category_id'],
                strtoupper($data['sku']),
                $data['stock'],
                $data['weight'] ?? 0,
                $data['status'],
                $data['product_id']
            ];
            
            $this->db->execute($query, $params);
            
            $this->db->commit();
            
            $this->sendSuccess([
                'product_id' => $data['product_id'],
                'sku' => strtoupper($data['sku'])
            ], 'Prodotto modificato con successo');
            
        } catch (Exception $e) {
            $this->db->rollback();
            $this->sendError('Errore durante la modifica del prodotto: ' . $e->getMessage());
        }
    }
    
    /**
     * Elimina un prodotto
     */
    public function deleteProduct() {
        if (!$this->verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->sendError('Token di sicurezza non valido', 403);
        }
        
        $data = sanitizeInput($_POST);
        
        if (empty($data['product_id'])) {
            $this->sendError('ID prodotto mancante', 422);
        }
        
        try {
            $this->db->beginTransaction();
            
            // Verifica che il prodotto esista
            $product = $this->db->fetchOne("SELECT name, sku FROM products WHERE id = ?", [$data['product_id']]);
            if (!$product) {
                $this->db->rollback();
                $this->sendError('Prodotto non trovato', 404);
            }
            
            // Verifica se ci sono ordini associati
            $orderItems = $this->db->fetchOne(
                "SELECT COUNT(*) as count FROM order_items WHERE product_id = ?", 
                [$data['product_id']]
            );
            
            if ($orderItems['count'] > 0) {
                $this->db->rollback();
                $this->sendError('Impossibile eliminare il prodotto perché è associato a ordini esistenti', 409);
            }
            
            // Elimina il prodotto
            $this->db->execute("DELETE FROM products WHERE id = ?", [$data['product_id']]);
            
            $this->db->commit();
            
            $this->sendSuccess([
                'product_id' => $data['product_id'],
                'product_name' => $product['name'],
                'sku' => $product['sku']
            ], 'Prodotto eliminato con successo');
            
        } catch (Exception $e) {
            $this->db->rollback();
            $this->sendError('Errore durante l\'eliminazione del prodotto: ' . $e->getMessage());
        }
    }
    
    /**
     * Toggle stato prodotto (attivo/inattivo)
     */
    public function toggleProductStatus() {
        if (!$this->verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->sendError('Token di sicurezza non valido', 403);
        }
        
        $data = sanitizeInput($_POST);
        
        if (empty($data['product_id'])) {
            $this->sendError('ID prodotto mancante', 422);
        }
        
        try {
            // Ottieni lo stato attuale
            $product = $this->db->fetchOne(
                "SELECT status, name FROM products WHERE id = ?", 
                [$data['product_id']]
            );
            
            if (!$product) {
                $this->sendError('Prodotto non trovato', 404);
            }
            
            // Toggle dello stato
            $newStatus = $product['status'] === 'active' ? 'inactive' : 'active';
            
            $this->db->execute(
                "UPDATE products SET status = ?, updated_at = NOW() WHERE id = ?",
                [$newStatus, $data['product_id']]
            );
            
            $this->sendSuccess([
                'product_id' => $data['product_id'],
                'new_status' => $newStatus,
                'product_name' => $product['name']
            ], "Prodotto " . ($newStatus === 'active' ? 'attivato' : 'disattivato') . " con successo");
            
        } catch (Exception $e) {
            $this->sendError('Errore durante il cambio di stato: ' . $e->getMessage());
        }
    }
    
    /**
     * GESTIONE CATEGORIE
     */
    
    /**
     * Aggiungi una nuova categoria
     */
    public function addCategory() {
        if (!$this->verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->sendError('Token di sicurezza non valido', 403);
        }
        
        $data = sanitizeInput($_POST);
        
        $rules = [
            'name' => ['required' => true, 'min_length' => 2, 'max_length' => 255, 'label' => 'Nome'],
            'description' => ['required' => false, 'max_length' => 500, 'label' => 'Descrizione'],
            'status' => ['required' => true, 'pattern' => '/^(active|inactive)$/', 'label' => 'Stato']
        ];
        
        $errors = $this->validateInput($data, $rules);
        if (!empty($errors)) {
            $this->sendError('Dati non validi', 422, $errors);
        }
        
        try {
            $this->db->beginTransaction();
            
            // Genera slug dalla categoria
            $slug = generateSlug($data['name']);
            
            // Verifica che lo slug non esista già
            $existingSlug = $this->db->fetchOne("SELECT id FROM categories WHERE slug = ?", [$slug]);
            if ($existingSlug) {
                $this->db->rollback();
                $this->sendError('Nome categoria già esistente', 409, ['name' => 'Una categoria con questo nome esiste già']);
            }
            
            // Inserisci la categoria
            $query = "INSERT INTO categories (name, description, slug, status, created_at, updated_at) 
                     VALUES (?, ?, ?, ?, NOW(), NOW())";
            
            $params = [
                $data['name'],
                $data['description'] ?? '',
                $slug,
                $data['status']
            ];
            
            $categoryId = $this->db->insert($query, $params);
            
            $this->db->commit();
            
            $this->sendSuccess([
                'category_id' => $categoryId,
                'slug' => $slug
            ], 'Categoria aggiunta con successo');
            
        } catch (Exception $e) {
            $this->db->rollback();
            $this->sendError('Errore durante l\'aggiunta della categoria: ' . $e->getMessage());
        }
    }
    
    /**
     * Modifica una categoria esistente
     */
    public function editCategory() {
        if (!$this->verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->sendError('Token di sicurezza non valido', 403);
        }
        
        $data = sanitizeInput($_POST);
        
        if (empty($data['category_id'])) {
            $this->sendError('ID categoria mancante', 422);
        }
        
        $rules = [
            'category_id' => ['required' => true, 'numeric' => true, 'label' => 'ID Categoria'],
            'name' => ['required' => true, 'min_length' => 2, 'max_length' => 255, 'label' => 'Nome'],
            'description' => ['required' => false, 'max_length' => 500, 'label' => 'Descrizione'],
            'status' => ['required' => true, 'pattern' => '/^(active|inactive)$/', 'label' => 'Stato']
        ];
        
        $errors = $this->validateInput($data, $rules);
        if (!empty($errors)) {
            $this->sendError('Dati non validi', 422, $errors);
        }
        
        try {
            $this->db->beginTransaction();
            
            // Verifica che la categoria esista
            $existingCategory = $this->db->fetchOne("SELECT id FROM categories WHERE id = ?", [$data['category_id']]);
            if (!$existingCategory) {
                $this->db->rollback();
                $this->sendError('Categoria non trovata', 404);
            }
            
            // Genera nuovo slug
            $newSlug = generateSlug($data['name']);
            
            // Verifica che lo slug non sia già usato da un'altra categoria
            $existingSlug = $this->db->fetchOne(
                "SELECT id FROM categories WHERE slug = ? AND id != ?", 
                [$newSlug, $data['category_id']]
            );
            if ($existingSlug) {
                $this->db->rollback();
                $this->sendError('Nome categoria già esistente', 409, ['name' => 'Una categoria con questo nome esiste già']);
            }
            
            // Aggiorna la categoria
            $query = "UPDATE categories 
                     SET name = ?, description = ?, slug = ?, status = ?, updated_at = NOW()
                     WHERE id = ?";
            
            $params = [
                $data['name'],
                $data['description'] ?? '',
                $newSlug,
                $data['status'],
                $data['category_id']
            ];
            
            $this->db->execute($query, $params);
            
            $this->db->commit();
            
            $this->sendSuccess([
                'category_id' => $data['category_id'],
                'slug' => $newSlug
            ], 'Categoria modificata con successo');
            
        } catch (Exception $e) {
            $this->db->rollback();
            $this->sendError('Errore durante la modifica della categoria: ' . $e->getMessage());
        }
    }
    
    /**
     * Elimina una categoria
     */
    public function deleteCategory() {
        if (!$this->verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->sendError('Token di sicurezza non valido', 403);
        }
        
        $data = sanitizeInput($_POST);
        
        if (empty($data['category_id'])) {
            $this->sendError('ID categoria mancante', 422);
        }
        
        try {
            $this->db->beginTransaction();
            
            // Verifica che la categoria esista
            $category = $this->db->fetchOne("SELECT name FROM categories WHERE id = ?", [$data['category_id']]);
            if (!$category) {
                $this->db->rollback();
                $this->sendError('Categoria non trovata', 404);
            }
            
            // Verifica se ci sono prodotti associati
            $productsCount = $this->db->fetchOne(
                "SELECT COUNT(*) as count FROM products WHERE category_id = ?", 
                [$data['category_id']]
            );
            
            if ($productsCount['count'] > 0) {
                $this->db->rollback();
                $this->sendError('Impossibile eliminare la categoria perché contiene prodotti', 409);
            }
            
            // Elimina la categoria
            $this->db->execute("DELETE FROM categories WHERE id = ?", [$data['category_id']]);
            
            $this->db->commit();
            
            $this->sendSuccess([
                'category_id' => $data['category_id'],
                'category_name' => $category['name']
            ], 'Categoria eliminata con successo');
            
        } catch (Exception $e) {
            $this->db->rollback();
            $this->sendError('Errore durante l\'eliminazione della categoria: ' . $e->getMessage());
        }
    }
    
    /**
     * Toggle stato categoria (attivo/inattivo)
     */
    public function toggleCategoryStatus() {
        if (!$this->verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->sendError('Token di sicurezza non valido', 403);
        }
        
        $data = sanitizeInput($_POST);
        
        if (empty($data['category_id'])) {
            $this->sendError('ID categoria mancante', 422);
        }
        
        try {
            // Ottieni lo stato attuale
            $category = $this->db->fetchOne(
                "SELECT status, name FROM categories WHERE id = ?", 
                [$data['category_id']]
            );
            
            if (!$category) {
                $this->sendError('Categoria non trovata', 404);
            }
            
            // Toggle dello stato
            $newStatus = $category['status'] === 'active' ? 'inactive' : 'active';
            
            $this->db->execute(
                "UPDATE categories SET status = ?, updated_at = NOW() WHERE id = ?",
                [$newStatus, $data['category_id']]
            );
            
            $this->sendSuccess([
                'category_id' => $data['category_id'],
                'new_status' => $newStatus,
                'category_name' => $category['name']
            ], "Categoria " . ($newStatus === 'active' ? 'attivata' : 'disattivata') . " con successo");
            
        } catch (Exception $e) {
            $this->sendError('Errore durante il cambio di stato: ' . $e->getMessage());
        }
    }
    
    /**
     * FUNZIONI DI UTILITÀ
     */
    
    /**
     * Ottieni dati per un prodotto (per modal di modifica)
     */
    public function getProductData() {
        $productId = $_POST['product_id'] ?? '';
        
        if (empty($productId) || !is_numeric($productId)) {
            $this->sendError('ID prodotto non valido', 422);
        }
        
        try {
            $product = $this->db->fetchOne(
                "SELECT p.*, c.name as category_name 
                 FROM products p 
                 LEFT JOIN categories c ON p.category_id = c.id 
                 WHERE p.id = ?", 
                [$productId]
            );
            
            if (!$product) {
                $this->sendError('Prodotto non trovato', 404);
            }
            
            $this->sendSuccess(['product' => $product]);
            
        } catch (Exception $e) {
            $this->sendError('Errore durante il recupero dei dati del prodotto: ' . $e->getMessage());
        }
    }
    
    /**
     * Ottieni dati per una categoria (per modal di modifica)
     */
    public function getCategoryData() {
        $categoryId = $_POST['category_id'] ?? '';
        
        if (empty($categoryId) || !is_numeric($categoryId)) {
            $this->sendError('ID categoria non valido', 422);
        }
        
        try {
            $category = $this->db->fetchOne(
                "SELECT * FROM categories WHERE id = ?", 
                [$categoryId]
            );
            
            if (!$category) {
                $this->sendError('Categoria non trovata', 404);
            }
            
            $this->sendSuccess(['category' => $category]);
            
        } catch (Exception $e) {
            $this->sendError('Errore durante il recupero dei dati della categoria: ' . $e->getMessage());
        }
    }
    
    /**
     * Verifica disponibilità SKU
     */
    public function checkSKUAvailability() {
        $sku = strtoupper($_POST['sku'] ?? '');
        $excludeId = $_POST['exclude_id'] ?? null;
        
        if (empty($sku) || !preg_match('/^[A-Z0-9\-]+$/', $sku)) {
            $this->sendError('SKU non valido', 422);
        }
        
        try {
            $query = "SELECT id FROM products WHERE sku = ?";
            $params = [$sku];
            
            if ($excludeId) {
                $query .= " AND id != ?";
                $params[] = $excludeId;
            }
            
            $existing = $this->db->fetchOne($query, $params);
            
            $this->sendSuccess([
                'available' => !$existing,
                'sku' => $sku
            ]);
            
        } catch (Exception $e) {
            $this->sendError('Errore durante la verifica SKU: ' . $e->getMessage());
        }
    }
    
    /**
     * Ottieni statistiche rapide per dashboard
     */
    public function getQuickStats() {
        try {
            $stats = [
                'total_products' => $this->db->count('products'),
                'active_products' => $this->db->count('products', 'status = ?', ['active']),
                'inactive_products' => $this->db->count('products', 'status = ?', ['inactive']),
                'total_categories' => $this->db->count('categories'),
                'active_categories' => $this->db->count('categories', 'status = ?', ['active']),
                'low_stock_products' => $this->db->count('products', 'stock <= ?', [LOW_STOCK_THRESHOLD]),
                'critical_stock_products' => $this->db->count('products', 'stock <= ?', [CRITICAL_STOCK_THRESHOLD])
            ];
            
            $this->sendSuccess(['stats' => $stats]);
            
        } catch (Exception $e) {
            $this->sendError('Errore durante il recupero delle statistiche: ' . $e->getMessage());
        }
    }
}

// Inizializza il gestore AJAX
try {
    $ajax = new AjaxHandler();
    
    // Ottieni l'azione richiesta
    $action = $_POST['action'] ?? '';
    
    // Mappa delle azioni consentite
    $allowedActions = [
        // Prodotti
        'add_product',
        'edit_product',
        'delete_product',
        'toggle_product_status',
        'get_product_data',
        
        // Categorie
        'add_category',
        'edit_category',
        'delete_category',
        'toggle_category_status',
        'get_category_data',
        
        // Utilità
        'check_sku_availability',
        'get_quick_stats'
    ];
    
    if (!in_array($action, $allowedActions)) {
        throw new Exception('Azione non riconosciuta: ' . $action);
    }
    
    // Esegui l'azione richiesta
    $ajax->$action();
    
} catch (Exception $e) {
    // Fallback per errori non gestiti
    error_log("Errore AJAX non gestito: " . $e->getMessage());
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'error' => [
            'message' => 'Errore interno del server',
            'code' => 500
        ],
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_UNESCAPED_UNICODE);
}
?>
