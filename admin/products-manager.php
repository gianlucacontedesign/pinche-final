<?php
/**
 * GESTIONE COMPLETA PRODOTTI - ADMIN
 * Sistema completo per la gestione dei prodotti con CRUD, AJAX e validazione
 * Creato: 03 Nov 2025 - 21:36
 */

// Inclusione configurazione e dipendenze
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../classes/Database.php';

// Inizializzazione sessione se non attiva
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Classe per la gestione dei prodotti
class ProductManager {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Ottenere tutti i prodotti con categorie
     */
    public function getAllProducts($limit = null, $offset = 0) {
        $sql = "SELECT p.*, c.name as category_name, c.id as category_id 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.is_deleted = 0 
                ORDER BY p.created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT ? OFFSET ?";
            return $this->db->fetchAll($sql, [$limit, $offset]);
        }
        
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Ottenere prodotto per ID
     */
    public function getProductById($id) {
        return $this->db->fetchOne(
            "SELECT p.*, c.name as category_name 
             FROM products p 
             LEFT JOIN categories c ON p.category_id = c.id 
             WHERE p.id = ? AND p.is_deleted = 0", 
            [$id]
        );
    }
    
    /**
     * Ottenere categorie attive
     */
    public function getActiveCategories() {
        return $this->db->fetchAll("SELECT * FROM categories WHERE is_active = 1 ORDER BY name");
    }
    
    /**
     * Creare nuovo prodotto
     */
    public function createProduct($data) {
        try {
            // Validazione dati
            $validation = $this->validateProductData($data);
            if (!$validation['valid']) {
                return ['success' => false, 'error' => $validation['message']];
            }
            
            // Preparazione dati
            $productData = $this->prepareProductData($data);
            
            // Inserimento nel database
            $sql = "INSERT INTO products (name, sku, category_id, price, stock_quantity, 
                    description, image, is_active, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
            
            $id = $this->db->insert($sql, [
                $productData['name'],
                $productData['sku'],
                $productData['category_id'],
                $productData['price'],
                $productData['stock_quantity'],
                $productData['description'],
                $productData['image'],
                $productData['is_active']
            ]);
            
            // Log attività
            logActivity('product_create', "Prodotto creato: {$productData['name']} (ID: $id)", $_SESSION['admin_id'] ?? null);
            
            return ['success' => true, 'id' => $id, 'message' => 'Prodotto creato con successo!'];
            
        } catch (Exception $e) {
            logActivity('product_create_error', $e->getMessage(), $_SESSION['admin_id'] ?? null);
            return ['success' => false, 'error' => 'Errore durante la creazione del prodotto'];
        }
    }
    
    /**
     * Aggiornare prodotto esistente
     */
    public function updateProduct($id, $data) {
        try {
            // Verifica esistenza prodotto
            $existing = $this->getProductById($id);
            if (!$existing) {
                return ['success' => false, 'error' => 'Prodotto non trovato'];
            }
            
            // Validazione dati
            $validation = $this->validateProductData($data, $id);
            if (!$validation['valid']) {
                return ['success' => false, 'error' => $validation['message']];
            }
            
            // Preparazione dati
            $productData = $this->prepareProductData($data);
            
            // Aggiornamento nel database
            $sql = "UPDATE products SET 
                    name = ?, sku = ?, category_id = ?, price = ?, stock_quantity = ?, 
                    description = ?, image = ?, is_active = ?, updated_at = NOW() 
                    WHERE id = ?";
            
            $this->db->execute($sql, [
                $productData['name'],
                $productData['sku'],
                $productData['category_id'],
                $productData['price'],
                $productData['stock_quantity'],
                $productData['description'],
                $productData['image'],
                $productData['is_active'],
                $id
            ]);
            
            // Log attività
            logActivity('product_update', "Prodotto aggiornato: {$productData['name']} (ID: $id)", $_SESSION['admin_id'] ?? null);
            
            return ['success' => true, 'message' => 'Prodotto aggiornato con successo!'];
            
        } catch (Exception $e) {
            logActivity('product_update_error', $e->getMessage(), $_SESSION['admin_id'] ?? null);
            return ['success' => false, 'error' => 'Errore durante l\'aggiornamento del prodotto'];
        }
    }
    
    /**
     * Eliminare prodotto (soft delete)
     */
    public function deleteProduct($id) {
        try {
            // Verifica esistenza prodotto
            $existing = $this->getProductById($id);
            if (!$existing) {
                return ['success' => false, 'error' => 'Prodotto non trovato'];
            }
            
            // Soft delete
            $this->db->execute("UPDATE products SET is_deleted = 1, updated_at = NOW() WHERE id = ?", [$id]);
            
            // Log attività
            logActivity('product_delete', "Prodotto eliminato: {$existing['name']} (ID: $id)", $_SESSION['admin_id'] ?? null);
            
            return ['success' => true, 'message' => 'Prodotto eliminato con successo!'];
            
        } catch (Exception $e) {
            logActivity('product_delete_error', $e->getMessage(), $_SESSION['admin_id'] ?? null);
            return ['success' => false, 'error' => 'Errore durante l\'eliminazione del prodotto'];
        }
    }
    
    /**
     * Attivare/disattivare prodotto
     */
    public function toggleProductStatus($id) {
        try {
            $existing = $this->getProductById($id);
            if (!$existing) {
                return ['success' => false, 'error' => 'Prodotto non trovato'];
            }
            
            $newStatus = $existing['is_active'] ? 0 : 1;
            $statusText = $newStatus ? 'attivato' : 'disattivato';
            
            $this->db->execute("UPDATE products SET is_active = ?, updated_at = NOW() WHERE id = ?", [$newStatus, $id]);
            
            logActivity('product_toggle', "Prodotto $statusText: {$existing['name']} (ID: $id)", $_SESSION['admin_id'] ?? null);
            
            return ['success' => true, 'message' => "Prodotto $statusText con successo!"];
            
        } catch (Exception $e) {
            logActivity('product_toggle_error', $e->getMessage(), $_SESSION['admin_id'] ?? null);
            return ['success' => false, 'error' => 'Errore durante il cambio di stato'];
        }
    }
    
    /**
     * Validazione dati prodotto
     */
    private function validateProductData($data, $excludeId = null) {
        // Nome obbligatorio
        if (empty($data['name'])) {
            return ['valid' => false, 'message' => 'Il nome del prodotto è obbligatorio'];
        }
        
        if (strlen($data['name']) < 2) {
            return ['valid' => false, 'message' => 'Il nome deve contenere almeno 2 caratteri'];
        }
        
        // SKU unico
        if (!empty($data['sku'])) {
            $existing = $this->db->fetchOne("SELECT id FROM products WHERE sku = ? AND is_deleted = 0" . ($excludeId ? " AND id != $excludeId" : ""), [$data['sku']]);
            if ($existing) {
                return ['valid' => false, 'message' => 'Questo codice prodotto (SKU) è già in uso'];
            }
        }
        
        // Categoria valida
        if (empty($data['category_id']) || !is_numeric($data['category_id'])) {
            return ['valid' => false, 'message' => 'Seleziona una categoria valida'];
        }
        
        // Prezzo valido
        if (!is_numeric($data['price']) || $data['price'] < 0) {
            return ['valid' => false, 'message' => 'Il prezzo deve essere un numero positivo'];
        }
        
        // Stock valido
        if (!is_numeric($data['stock_quantity']) || $data['stock_quantity'] < 0) {
            return ['valid' => false, 'message' => 'La quantità in stock deve essere un numero positivo'];
        }
        
        // Descrizione opzionale ma validata se presente
        if (!empty($data['description']) && strlen($data['description']) > 1000) {
            return ['valid' => false, 'message' => 'La descrizione non può superare i 1000 caratteri'];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Preparazione dati per database
     */
    private function prepareProductData($data) {
        return [
            'name' => sanitizeInput($data['name']),
            'sku' => !empty($data['sku']) ? strtoupper(sanitizeInput($data['sku'])) : generateUniqueSKU(),
            'category_id' => (int)$data['category_id'],
            'price' => round((float)$data['price'], 2),
            'stock_quantity' => (int)$data['stock_quantity'],
            'description' => !empty($data['description']) ? sanitizeInput($data['description']) : null,
            'image' => !empty($data['image']) ? $data['image'] : null,
            'is_active' => isset($data['is_active']) ? 1 : 0
        ];
    }
    
    /**
     * Ottenere statistiche prodotti
     */
    public function getProductStats() {
        try {
            $stats = [];
            
            // Totale prodotti
            $stats['total'] = $this->db->fetchOne("SELECT COUNT(*) as count FROM products WHERE is_deleted = 0")['count'];
            
            // Prodotti attivi
            $stats['active'] = $this->db->fetchOne("SELECT COUNT(*) as count FROM products WHERE is_deleted = 0 AND is_active = 1")['count'];
            
            // Prodotti con stock basso
            $stats['low_stock'] = $this->db->fetchOne("SELECT COUNT(*) as count FROM products WHERE is_deleted = 0 AND stock_quantity <= ?", [LOW_STOCK_THRESHOLD])['count'];
            
            // Valore totale inventario
            $stats['total_value'] = $this->db->fetchOne("SELECT SUM(price * stock_quantity) as total FROM products WHERE is_deleted = 0 AND is_active = 1")['total'] ?? 0;
            
            return $stats;
        } catch (Exception $e) {
            logActivity('product_stats_error', $e->getMessage(), $_SESSION['admin_id'] ?? null);
            return ['total' => 0, 'active' => 0, 'low_stock' => 0, 'total_value' => 0];
        }
    }
    
    /**
     * Caricamento immagine prodotto
     */
    public function uploadProductImage($file, $productId = null) {
        try {
            if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
                return ['success' => false, 'error' => 'Errore nel caricamento del file'];
            }
            
            // Validazioni file
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            $fileType = mime_content_type($file['tmp_name']);
            
            if (!in_array($fileType, $allowedTypes)) {
                return ['success' => false, 'error' => 'Tipo di file non supportato. Usa JPEG, PNG, GIF o WebP'];
            }
            
            if ($file['size'] > MAX_FILE_SIZE) {
                return ['success' => false, 'error' => 'File troppo grande. Dimensione massima: ' . (MAX_FILE_SIZE / 1024 / 1024) . 'MB'];
            }
            
            // Creazione directory upload se non esiste
            $uploadDir = __DIR__ . '/../uploads/products/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Nome file unico
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'product_' . ($productId ?: 'new') . '_' . time() . '_' . uniqid() . '.' . $extension;
            $filepath = $uploadDir . $filename;
            
            // Spostamento file
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                $relativePath = 'uploads/products/' . $filename;
                return ['success' => true, 'path' => $relativePath, 'filename' => $filename];
            }
            
            return ['success' => false, 'error' => 'Errore durante il salvataggio del file'];
            
        } catch (Exception $e) {
            logActivity('image_upload_error', $e->getMessage(), $_SESSION['admin_id'] ?? null);
            return ['success' => false, 'error' => 'Errore durante il caricamento dell\'immagine'];
        }
    }
    
    /**
     * Eliminare immagine prodotto
     */
    public function deleteProductImage($imagePath) {
        try {
            if (empty($imagePath)) {
                return ['success' => true];
            }
            
            $fullPath = __DIR__ . '/../' . $imagePath;
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
            
            return ['success' => true];
            
        } catch (Exception $e) {
            logActivity('image_delete_error', $e->getMessage(), $_SESSION['admin_id'] ?? null);
            return ['success' => false, 'error' => 'Errore durante l\'eliminazione dell\'immagine'];
        }
    }
}

// Inizializzazione gestore prodotti
$productManager = new ProductManager();

// Gestione AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_action'])) {
    header('Content-Type: application/json');
    
    try {
        $action = $_POST['ajax_action'];
        
        switch ($action) {
            case 'create':
                $result = $productManager->createProduct($_POST);
                echo json_encode($result);
                break;
                
            case 'update':
                if (!isset($_POST['product_id'])) {
                    throw new Exception('ID prodotto mancante');
                }
                $result = $productManager->updateProduct($_POST['product_id'], $_POST);
                echo json_encode($result);
                break;
                
            case 'delete':
                if (!isset($_POST['product_id'])) {
                    throw new Exception('ID prodotto mancante');
                }
                $result = $productManager->deleteProduct($_POST['product_id']);
                echo json_encode($result);
                break;
                
            case 'toggle_status':
                if (!isset($_POST['product_id'])) {
                    throw new Exception('ID prodotto mancante');
                }
                $result = $productManager->toggleProductStatus($_POST['product_id']);
                echo json_encode($result);
                break;
                
            case 'upload_image':
                if (!isset($_FILES['image'])) {
                    throw new Exception('Nessun file ricevuto');
                }
                $result = $productManager->uploadProductImage($_FILES['image'], $_POST['product_id'] ?? null);
                echo json_encode($result);
                break;
                
            case 'get_product':
                if (!isset($_POST['product_id'])) {
                    throw new Exception('ID prodotto mancante');
                }
                $product = $productManager->getProductById($_POST['product_id']);
                echo json_encode(['success' => true, 'product' => $product]);
                break;
                
            case 'get_stats':
                $stats = $productManager->getProductStats();
                echo json_encode(['success' => true, 'stats' => $stats]);
                break;
                
            default:
                throw new Exception('Azione non valida');
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// Gestione upload diretto (non AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    $result = $productManager->uploadProductImage($_FILES['image'], $_POST['product_id'] ?? null);
    
    if ($result['success']) {
        setFlashMessage('success', 'Immagine caricata con successo!');
        $_POST['image'] = $result['path'];
    } else {
        setFlashMessage('error', $result['error']);
    }
}

// Ottenere dati per la visualizzazione
$products = $productManager->getAllProducts();
$categories = $productManager->getActiveCategories();
$stats = $productManager->getProductStats();

// Gestione operazioni POST non AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['ajax_action'])) {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create':
            $result = $productManager->createProduct($_POST);
            if ($result['success']) {
                setFlashMessage('success', $result['message']);
                header('Location: products-manager.php');
                exit;
            } else {
                setFlashMessage('error', $result['error']);
            }
            break;
            
        case 'update':
            if (isset($_POST['product_id'])) {
                $result = $productManager->updateProduct($_POST['product_id'], $_POST);
                if ($result['success']) {
                    setFlashMessage('success', $result['message']);
                    header('Location: products-manager.php');
                    exit;
                } else {
                    setFlashMessage('error', $result['error']);
                }
            }
            break;
            
        case 'delete':
            if (isset($_POST['product_id'])) {
                $result = $productManager->deleteProduct($_POST['product_id']);
                if ($result['success']) {
                    setFlashMessage('success', $result['message']);
                } else {
                    setFlashMessage('error', $result['error']);
                }
            }
            break;
    }
}

// Ottenere prodotto per modifica se ID presente nell'URL
$editProduct = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $editProduct = $productManager->getProductById($_GET['edit']);
}

include __DIR__ . '/includes/sidebar.php';
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Prodotti - Pinche Supplies Admin</title>
    
    <!-- Font Awesome per icone -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- CSS personalizzato -->
    <style>
        .main-content {
            margin-left: 260px;
            padding: 20px;
            min-height: 100vh;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .product-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            background: white;
            transition: all 0.3s ease;
        }
        
        .product-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        
        .product-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #e9ecef;
        }
        
        .product-image-placeholder {
            width: 80px;
            height: 80px;
            background: #f8f9fa;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
            border: 2px solid #e9ecef;
        }
        
        .modal-content {
            border: none;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .modal-header {
            background: linear-gradient(135deg, #7c3aed, #a855f7);
            color: white;
            border-radius: 12px 12px 0 0;
        }
        
        .btn-gradient {
            background: linear-gradient(135deg, #7c3aed, #a855f7);
            border: none;
            color: white;
        }
        
        .btn-gradient:hover {
            background: linear-gradient(135deg, #6d28d9, #9333ea);
            color: white;
        }
        
        .stats-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
        }
        
        .status-badge {
            font-size: 0.75rem;
            padding: 0.5em 0.75em;
        }
        
        .stock-badge {
            font-size: 0.7rem;
        }
        
        .image-preview {
            max-width: 200px;
            max-height: 200px;
            border-radius: 8px;
            border: 2px solid #e9ecef;
        }
        
        .alert {
            border-radius: 8px;
            border: none;
        }
        
        .form-control:focus {
            border-color: #7c3aed;
            box-shadow: 0 0 0 0.2rem rgba(124, 58, 237, 0.25);
        }
        
        .btn:focus {
            box-shadow: 0 0 0 0.2rem rgba(124, 58, 237, 0.25);
        }
        
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }
        
        .toast-container {
            z-index: 9999;
        }
        
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 10px;
            }
            
            .page-header {
                flex-direction: column;
                align-items: stretch;
            }
            
            .product-card {
                padding: 15px;
            }
        }
    </style>
</head>
<body>

<!-- Toast per notifiche -->
<div class="toast-container position-fixed top-0 end-0 p-3">
    <div id="notificationToast" class="toast" role="alert">
        <div class="toast-header">
            <i class="fas fa-info-circle text-primary me-2"></i>
            <strong class="me-auto">Notifica</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body" id="toastMessage">
            <!-- Messaggio dinamico -->
        </div>
    </div>
</div>

<div class="main-content">
    <!-- Header -->
    <div class="page-header">
        <div>
            <h1><i class="fas fa-box"></i> Gestione Completa Prodotti</h1>
            <p class="text-muted mb-0">Gestisci i tuoi prodotti con funzionalità avanzate</p>
        </div>
        <div>
            <button type="button" class="btn btn-gradient" data-bs-toggle="modal" data-bs-target="#productModal" onclick="openProductModal()">
                <i class="fas fa-plus"></i> Nuovo Prodotto
            </button>
            <button type="button" class="btn btn-outline-primary" onclick="refreshStats()">
                <i class="fas fa-sync-alt"></i> Aggiorna Statistiche
            </button>
        </div>
    </div>

    <!-- Messaggi flash -->
    <?php if (getFlashMessage('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> <?= getFlashMessage('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if (getFlashMessage('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i> <?= getFlashMessage('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Statistiche -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card stats-card text-white bg-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Prodotti Totali</h5>
                            <h2 class="mb-0" id="stat-total"><?= $stats['total'] ?></h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-box fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stats-card text-white bg-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Prodotti Attivi</h5>
                            <h2 class="mb-0" id="stat-active"><?= $stats['active'] ?></h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stats-card text-white bg-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Stock Basso</h5>
                            <h2 class="mb-0" id="stat-low-stock"><?= $stats['low_stock'] ?></h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-exclamation-triangle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stats-card text-white bg-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Valore Inventario</h5>
                            <h2 class="mb-0" id="stat-value">$<?= number_format($stats['total_value'], 0, ',', '.') ?></h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-dollar-sign fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista prodotti -->
    <div class="card">
        <div class="card-header">
            <h5><i class="fas fa-list"></i> Lista Prodotti</h5>
        </div>
        <div class="card-body">
            <?php if (empty($products)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-box-open fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted">Nessun prodotto registrato</h4>
                    <p class="text-muted">Inizia aggiungendo il tuo primo prodotto.</p>
                    <button type="button" class="btn btn-gradient" data-bs-toggle="modal" data-bs-target="#productModal" onclick="openProductModal()">
                        <i class="fas fa-plus"></i> Aggiungi Prodotto
                    </button>
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($products as $product): ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="product-card h-100">
                                <div class="row align-items-center">
                                    <div class="col-4">
                                        <?php if ($product['image']): ?>
                                            <img src="../<?= htmlspecialchars($product['image']) ?>" 
                                                 class="product-image" 
                                                 alt="<?= htmlspecialchars($product['name']) ?>"
                                                 onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iODAiIGhlaWdodD0iODAiIHZpZXdCb3g9IjAgMCA4MCA4MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjgwIiBoZWlnaHQ9IjgwIiBmaWxsPSIjRjNGNEY2Ii8+CjxwYXRoIGQ9Ik0zNiA0NEMzNiA0MS43MTU0IDM4LjcxNTQgMzkgNDAgMzlIMjBDMjEuNzE1NCAzOSAyMCA0MS43MTU0IDIwIDQ0VjU2QzIwIDU4LjI4NDYgMjEuNzE1NCA2MCAyNCA2MEg1NkM1OC4yODQ2IDYwIDYwIDU4LjI4NDYgNjAgNTZWNDRDNjAgNDEuNzE1NCA1OC4yODQ2IDM5IDU2IDM5SDQwQzM4LjcxNTQgMzkgMzYgNDEuNzE1NCAzNiA0NFoiIGZpbGw9IiM5Q0E0QUYiLz4KPC9zdmc+'">
                                        <?php else: ?>
                                            <div class="product-image-placeholder">
                                                <i class="fas fa-image"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="col-8">
                                        <h6 class="mb-1"><?= htmlspecialchars($product['name']) ?></h6>
                                        <small class="text-muted d-block">
                                            <i class="fas fa-tag"></i> <?= htmlspecialchars($product['category_name']) ?>
                                        </small>
                                        <small class="text-muted d-block">
                                            <i class="fas fa-barcode"></i> <?= htmlspecialchars($product['sku']) ?>
                                        </small>
                                        <div class="mt-2">
                                            <strong class="text-success">$<?= number_format($product['price'], 2) ?></strong>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="badge <?= $product['is_active'] ? 'bg-success' : 'bg-secondary' ?> status-badge">
                                            <i class="fas <?= $product['is_active'] ? 'fa-check' : 'fa-pause' ?>"></i>
                                            <?= $product['is_active'] ? 'Attivo' : 'Inattivo' ?>
                                        </span>
                                        
                                        <span class="badge <?= $product['stock_quantity'] <= 2 ? 'bg-danger' : ($product['stock_quantity'] <= 5 ? 'bg-warning' : 'bg-info') ?> stock-badge">
                                            <i class="fas fa-cubes"></i> Stock: <?= $product['stock_quantity'] ?>
                                        </span>
                                    </div>
                                    
                                    <div class="d-grid gap-2">
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="editProduct(<?= $product['id'] ?>)">
                                            <i class="fas fa-edit"></i> Modifica
                                        </button>
                                        
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-warning" onclick="toggleProductStatus(<?= $product['id'] ?>)">
                                                <i class="fas <?= $product['is_active'] ? 'fa-eye-slash' : 'fa-eye' ?>"></i>
                                                <?= $product['is_active'] ? 'Disattiva' : 'Attiva' ?>
                                            </button>
                                            
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteProduct(<?= $product['id'] ?>, '<?= htmlspecialchars($product['name']) ?>')">
                                                <i class="fas fa-trash"></i> Elimina
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal per creazione/modifica prodotto -->
<div class="modal fade" id="productModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">
                    <i class="fas fa-plus"></i> Nuovo Prodotto
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            
            <form id="productForm" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" id="productId" name="product_id">
                    <input type="hidden" id="currentImage" name="current_image">
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="name" class="form-label">Nome Prodotto *</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="sku" class="form-label">Codice Prodotto (SKU)</label>
                                <input type="text" class="form-control" id="sku" name="sku" placeholder="Auto-generato se vuoto">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="category_id" class="form-label">Categoria *</label>
                                <select class="form-select" id="category_id" name="category_id" required>
                                    <option value="">Seleziona categoria...</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="price" class="form-label">Prezzo *</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" required>
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="stock_quantity" class="form-label">Stock *</label>
                                <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" min="0" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Descrizione</label>
                        <textarea class="form-control" id="description" name="description" rows="3" maxlength="1000" placeholder="Descrizione opzionale del prodotto..."></textarea>
                        <div class="form-text">Massimo 1000 caratteri</div>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="image" class="form-label">Immagine Prodotto</label>
                                <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                <div class="form-text">Formati supportati: JPEG, PNG, GIF, WebP (max 5MB)</div>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="imagePreview" class="form-label">Anteprima Immagine</label>
                                <div id="imagePreview" class="text-center">
                                    <div class="image-preview-placeholder p-3 bg-light rounded">
                                        <i class="fas fa-image fa-3x text-muted"></i>
                                        <p class="text-muted mb-0">Anteprima non disponibile</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                            <label class="form-check-label" for="is_active">
                                Prodotto attivo
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Annulla
                    </button>
                    <button type="submit" class="btn btn-gradient" id="submitBtn">
                        <i class="fas fa-save"></i> Salva Prodotto
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
let currentProductId = null;
let isSubmitting = false;

// Funzione per mostrare notifiche
function showNotification(message, type = 'info') {
    const toast = document.getElementById('notificationToast');
    const toastMessage = document.getElementById('toastMessage');
    const toastIcon = toast.querySelector('.fa-info-circle');
    
    // Rimuovi classi precedenti
    toast.classList.remove('bg-success', 'bg-danger', 'bg-warning', 'bg-info');
    toastIcon.classList.remove('fa-check-circle', 'fa-exclamation-triangle', 'fa-info-circle');
    
    // Applica nuovo stile
    switch(type) {
        case 'success':
            toast.classList.add('bg-success', 'text-white');
            toastIcon.classList.add('fa-check-circle');
            break;
        case 'error':
            toast.classList.add('bg-danger', 'text-white');
            toastIcon.classList.add('fa-exclamation-triangle');
            break;
        case 'warning':
            toast.classList.add('bg-warning', 'text-dark');
            toastIcon.classList.add('fa-exclamation-triangle');
            break;
        default:
            toast.classList.add('bg-info', 'text-white');
            toastIcon.classList.add('fa-info-circle');
    }
    
    toastMessage.textContent = message;
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
}

// Apertura modal per nuovo prodotto
function openProductModal() {
    document.getElementById('productForm').reset();
    document.getElementById('productId').value = '';
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-plus"></i> Nuovo Prodotto';
    document.getElementById('submitBtn').innerHTML = '<i class="fas fa-save"></i> Salva Prodotto';
    document.getElementById('is_active').checked = true;
    resetImagePreview();
    clearValidation();
}

// Modifica prodotto
function editProduct(productId) {
    if (!productId) return;
    
    // Carica dati prodotto
    fetch('products-manager.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'ajax_action=get_product&product_id=' + productId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.product) {
            const product = data.product;
            
            // Popola form
            document.getElementById('productId').value = product.id;
            document.getElementById('name').value = product.name || '';
            document.getElementById('sku').value = product.sku || '';
            document.getElementById('category_id').value = product.category_id || '';
            document.getElementById('price').value = product.price || '';
            document.getElementById('stock_quantity').value = product.stock_quantity || '';
            document.getElementById('description').value = product.description || '';
            document.getElementById('is_active').checked = product.is_active == 1;
            document.getElementById('currentImage').value = product.image || '';
            
            // Mostra immagine esistente
            if (product.image) {
                showImagePreview('../' + product.image);
            } else {
                resetImagePreview();
            }
            
            // Aggiorna titolo modal
            document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit"></i> Modifica Prodotto';
            document.getElementById('submitBtn').innerHTML = '<i class="fas fa-save"></i> Aggiorna Prodotto';
            
            // Apri modal
            const modal = new bootstrap.Modal(document.getElementById('productModal'));
            modal.show();
        } else {
            showNotification('Errore nel caricamento del prodotto', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Errore di connessione', 'error');
    });
}

// Toggle status prodotto
function toggleProductStatus(productId) {
    if (!productId) return;
    
    fetch('products-manager.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'ajax_action=toggle_status&product_id=' + productId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification(data.error || 'Errore nel cambio di stato', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Errore di connessione', 'error');
    });
}

// Eliminazione prodotto
function deleteProduct(productId, productName) {
    if (!productId) return;
    
    if (confirm(`Sei sicuro di voler eliminare il prodotto "${productName}"?`)) {
        fetch('products-manager.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'ajax_action=delete&product_id=' + productId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showNotification(data.error || 'Errore nell\'eliminazione', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Errore di connessione', 'error');
        });
    }
}

// Aggiornamento statistiche
function refreshStats() {
    fetch('products-manager.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'ajax_action=get_stats'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.stats) {
            const stats = data.stats;
            document.getElementById('stat-total').textContent = stats.total || 0;
            document.getElementById('stat-active').textContent = stats.active || 0;
            document.getElementById('stat-low-stock').textContent = stats.low_stock || 0;
            document.getElementById('stat-value').textContent = '$' + (stats.total_value || 0).toLocaleString('it-IT');
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

// Anteprima immagine
function showImagePreview(src) {
    const preview = document.getElementById('imagePreview');
    preview.innerHTML = `<img src="${src}" class="image-preview" alt="Anteprima">`;
}

function resetImagePreview() {
    const preview = document.getElementById('imagePreview');
    preview.innerHTML = `
        <div class="image-preview-placeholder p-3 bg-light rounded">
            <i class="fas fa-image fa-3x text-muted"></i>
            <p class="text-muted mb-0">Anteprima non disponibile</p>
        </div>
    `;
}

// Gestione upload immagine
document.getElementById('image').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        // Validazione tipo file
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (!allowedTypes.includes(file.type)) {
            showNotification('Tipo di file non supportato', 'error');
            this.value = '';
            return;
        }
        
        // Validazione dimensione
        if (file.size > 5 * 1024 * 1024) {
            showNotification('File troppo grande (max 5MB)', 'error');
            this.value = '';
            return;
        }
        
        // Mostra anteprima
        const reader = new FileReader();
        reader.onload = function(e) {
            showImagePreview(e.target.result);
        };
        reader.readAsDataURL(file);
    }
});

// Gestione submit form
document.getElementById('productForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (isSubmitting) return;
    isSubmitting = true;
    
    const submitBtn = document.getElementById('submitBtn');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Salvando...';
    submitBtn.disabled = true;
    
    const formData = new FormData(this);
    const productId = document.getElementById('productId').value;
    const action = productId ? 'update' : 'create';
    
    // Aggiungi action AJAX
    formData.append('ajax_action', action);
    
    if (productId) {
        formData.append('product_id', productId);
    }
    
    fetch('products-manager.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            const modal = bootstrap.Modal.getInstance(document.getElementById('productModal'));
            modal.hide();
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification(data.error || 'Errore nel salvataggio', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Errore di connessione', 'error');
    })
    .finally(() => {
        isSubmitting = false;
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
});

// Validazione client-side
function validateField(field, message) {
    const input = document.getElementById(field);
    const feedback = input.nextElementSibling;
    
    if (message) {
        input.classList.add('is-invalid');
        feedback.textContent = message;
        return false;
    } else {
        input.classList.remove('is-invalid');
        return true;
    }
}

function clearValidation() {
    document.querySelectorAll('.is-invalid').forEach(el => {
        el.classList.remove('is-invalid');
    });
}

// Auto-generazione SKU
document.getElementById('name').addEventListener('blur', function() {
    const name = this.value.trim();
    const skuField = document.getElementById('sku');
    
    if (name && !skuField.value) {
        // Genera SKU basato sul nome
        const sku = 'PS-' + name.toUpperCase()
            .replace(/[^A-Z0-9]/g, '')
            .substring(0, 8) + '-' + Math.random().toString(36).substring(2, 6).toUpperCase();
        
        skuField.value = sku;
    }
});

// Inizializzazione
document.addEventListener('DOMContentLoaded', function() {
    // Aggiorna statistiche ogni 5 minuti
    setInterval(refreshStats, 300000);
    
    // Focus sul primo campo quando si apre il modal
    document.getElementById('productModal').addEventListener('shown.bs.modal', function() {
        document.getElementById('name').focus();
    });
});
</script>

</body>
</html>