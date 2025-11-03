<aside class="sidebar" style="position: fixed; left: 0; top: 0; width: 260px; height: 100vh; background: #1f2937; color: white; z-index: 1000; overflow-y: auto;">
    
    <style>
        .sidebar {
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            border-right: 1px solid #374151;
        }
        
        .sidebar-header {
            padding: 20px;
            background: linear-gradient(135deg, #7c3aed, #a855f7);
            border-bottom: 1px solid #374151;
            text-align: center;
        }
        
        .sidebar-header h3 {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 600;
        }
        
        .sidebar-header small {
            color: rgba(255,255,255,0.7);
            font-size: 0.8rem;
        }
        
        .sidebar-menu {
            padding: 10px 0;
        }
        
        .sidebar-menu a {
            display: block;
            padding: 12px 20px;
            color: #d1d5db;
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }
        
        .sidebar-menu a:hover {
            background-color: #374151;
            color: white;
            border-left-color: #7c3aed;
            transform: translateX(5px);
        }
        
        .sidebar-menu a.active {
            background-color: #4f46e5;
            color: white;
            border-left-color: #7c3aed;
            box-shadow: inset 0 0 0 1px #7c3aed;
        }
        
        .sidebar-menu a i {
            width: 20px;
            margin-right: 10px;
            text-align: center;
        }
        
        .sidebar-footer {
            position: absolute;
            bottom: 0;
            width: 100%;
            padding: 15px 20px;
            background-color: #111827;
            border-top: 1px solid #374151;
            text-align: center;
        }
        
        .sidebar-footer .user-info {
            font-size: 0.9rem;
            color: #9ca3af;
            margin-bottom: 10px;
        }
        
        .sidebar-footer .user-name {
            color: white;
            font-weight: 600;
        }
        
        .sidebar-footer .logout-btn {
            background-color: #dc2626;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            font-size: 0.8rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        
        .sidebar-footer .logout-btn:hover {
            background-color: #b91c1c;
        }
        
        .menu-section {
            margin: 10px 0;
        }
        
        .menu-section-title {
            padding: 10px 20px;
            font-size: 0.75rem;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .stats-sidebar {
            padding: 15px 20px;
            background-color: #111827;
            border-bottom: 1px solid #374151;
        }
        
        .stat-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }
        
        .stat-label {
            color: #9ca3af;
        }
        
        .stat-value {
            color: white;
            font-weight: 600;
        }
        
        .notification-badge {
            background-color: #ef4444;
            color: white;
            border-radius: 10px;
            padding: 2px 6px;
            font-size: 0.7rem;
            margin-left: 8px;
        }
        
        /* Animaciones */
        @keyframes fadeInLeft {
            from { opacity: 0; transform: translateX(-20px); }
            to { opacity: 1; transform: translateX(0); }
        }
        
        .sidebar-menu a {
            animation: fadeInLeft 0.3s ease-out;
        }
        
        .sidebar-menu a:nth-child(1) { animation-delay: 0.1s; }
        .sidebar-menu a:nth-child(2) { animation-delay: 0.2s; }
        .sidebar-menu a:nth-child(3) { animation-delay: 0.3s; }
        .sidebar-menu a:nth-child(4) { animation-delay: 0.4s; }
        .sidebar-menu a:nth-child(5) { animation-delay: 0.5s; }
        .sidebar-menu a:nth-child(6) { animation-delay: 0.6s; }
        
        /* Mobile */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .sidebar-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.5);
                z-index: 999;
            }
            
            .sidebar-overlay.show {
                display: block;
            }
        }
        
        .sidebar-toggle {
            display: none;
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1001;
            background: #1f2937;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
        }
        
        @media (max-width: 768px) {
            .sidebar-toggle {
                display: block;
            }
        }
    </style>
    
    <?php
    // Ottieni la pagina corrente in modo sicuro
    $current_page = basename($_SERVER['PHP_SELF']);
    
    // Ottieni statistiche in modo sicuro con fallback
    $stats = [
        'products' => 11,
        'categories' => 6,
        'low_stock' => 0,
        'orders' => 0,
        'customers' => 0
    ];
    
    // Prova a ottenere statistiche reali dal database se disponibile
    try {
        if (isset($pdo) && $pdo instanceof PDO) {
            // Conta prodotti attivi
            $stmt = $pdo->query("SELECT COUNT(*) FROM products WHERE is_active = 1");
            $stats['products'] = $stmt->fetchColumn();
            
            // Conta categorie attive
            $stmt = $pdo->query("SELECT COUNT(*) FROM categories WHERE is_active = 1");
            $stats['categories'] = $stmt->fetchColumn();
            
            // Conta stock basso (prodotti con stock <= 5)
            $stmt = $pdo->query("SELECT COUNT(*) FROM products WHERE stock <= 5 AND is_active = 1");
            $stats['low_stock'] = $stmt->fetchColumn();
            
            // Conta ordini
            $stmt = $pdo->query("SELECT COUNT(*) FROM orders");
            $stats['orders'] = $stmt->fetchColumn();
            
            // Conta clienti
            $stmt = $pdo->query("SELECT COUNT(*) FROM customers");
            $stats['customers'] = $stmt->fetchColumn();
        }
    } catch (Exception $e) {
        // Mantieni i valori di fallback in caso di errore
        error_log("Error fetching sidebar stats: " . $e->getMessage());
    }
    
    // Nome utente sicuro
    $admin_name = isset($_SESSION['admin_name']) ? htmlspecialchars($_SESSION['admin_name']) : 'Administrador';
    ?>
    
    <!-- Estadísticas rápidas -->
    <div class="stats-sidebar">
        <div class="stat-item">
            <span class="stat-label">Productos:</span>
            <span class="stat-value" id="stat-products"><?php echo $stats['products']; ?></span>
        </div>
        <div class="stat-item">
            <span class="stat-label">Categorías:</span>
            <span class="stat-value" id="stat-categories"><?php echo $stats['categories']; ?></span>
        </div>
        <div class="stat-item">
            <span class="stat-label">Stock Bajo:</span>
            <span class="stat-value" id="stat-low-stock"><?php echo $stats['low_stock']; ?></span>
        </div>
    </div>
    
    <!-- Header -->
    <div class="sidebar-header">
        <h3>Pinche Supplies</h3>
        <small>Panel de Administración</small>
    </div>
    
    <!-- Navegación Principal -->
    <nav class="sidebar-menu">
        <div class="menu-section">
            <a href="index.php" <?php echo ($current_page == 'index.php') ? 'class="active"' : ''; ?>>
                <i class="fas fa-home"></i> Dashboard
            </a>
            
            <a href="products.php" <?php echo (in_array($current_page, ['products.php', 'products-paginacion.php'])) ? 'class="active"' : ''; ?>>
                <i class="fas fa-box"></i> Productos
                <span class="notification-badge" id="products-badge"><?php echo $stats['products']; ?></span>
            </a>
            
            <a href="categories.php" <?php echo ($current_page == 'categories.php') ? 'class="active"' : ''; ?>>
                <i class="fas fa-tags"></i> Categorías
                <span class="notification-badge" id="categories-badge"><?php echo $stats['categories']; ?></span>
            </a>
            
            <a href="orders.php" <?php echo ($current_page == 'orders.php') ? 'class="active"' : ''; ?>>
                <i class="fas fa-shopping-cart"></i> Pedidos
                <span class="notification-badge" id="orders-badge"><?php echo $stats['orders']; ?></span>
            </a>
            
            <a href="customers.php" <?php echo ($current_page == 'customers.php') ? 'class="active"' : ''; ?>>
                <i class="fas fa-users"></i> Clientes
                <span class="notification-badge" id="customers-badge"><?php echo $stats['customers']; ?></span>
            </a>
        </div>
        
        <div class="menu-section">
            <div class="menu-section-title">Reportes</div>
            
            <a href="sales-reports.php" <?php echo ($current_page == 'sales-reports.php') ? 'class="active"' : ''; ?>>
                <i class="fas fa-chart-line"></i> Reportes de Ventas
            </a>
            
            <a href="inventory-reports.php" <?php echo ($current_page == 'inventory-reports.php') ? 'class="active"' : ''; ?>>
                <i class="fas fa-boxes"></i> Reportes de Stock
            </a>
            
            <a href="analytics.php" <?php echo ($current_page == 'analytics.php') ? 'class="active"' : ''; ?>>
                <i class="fas fa-analytics"></i> Analíticas
            </a>
        </div>
        
        <div class="menu-section">
            <div class="menu-section-title">Sistema</div>
            
            <a href="settings.php" <?php echo ($current_page == 'settings.php') ? 'class="active"' : ''; ?>>
                <i class="fas fa-cog"></i> Configuración
            </a>
            
            <a href="backup.php" <?php echo ($current_page == 'backup.php') ? 'class="active"' : ''; ?>>
                <i class="fas fa-database"></i> Respaldos
            </a>
            
            <a href="logs.php" <?php echo ($current_page == 'logs.php') ? 'class="active"' : ''; ?>>
                <i class="fas fa-file-alt"></i> Registros
            </a>
            
            <a href="help.php" <?php echo ($current_page == 'help.php') ? 'class="active"' : ''; ?>>
                <i class="fas fa-question-circle"></i> Ayuda
            </a>
        </div>
    </nav>
    
    <!-- Footer con usuario -->
    <div class="sidebar-footer">
        <div class="user-info">
            <div class="user-name"><?php echo $admin_name; ?></div>
            <div style="font-size: 0.8rem; color: #6b7280;">Admin</div>
        </div>
        
        <form method="POST" action="logout.php" style="margin: 0;">
            <button type="submit" class="logout-btn" onclick="return confirm('¿Cerrar sesión?')">
                <i class="fas fa-sign-out-alt"></i> Salir
            </button>
        </form>
    </div>
</aside>

<!-- Overlay para móviles -->
<div class="sidebar-overlay" onclick="toggleSidebar()"></div>

<!-- Botón toggle para móviles -->
<button class="sidebar-toggle" onclick="toggleSidebar()">
    <i class="fas fa-bars"></i>
</button>

<script>
// Toggle sidebar en móviles
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.querySelector('.sidebar-overlay');
    
    sidebar.classList.toggle('show');
    overlay.classList.toggle('show');
}

// Cerrar sidebar al hacer click fuera en móviles
document.addEventListener('click', function(e) {
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.querySelector('.sidebar-overlay');
    const toggleBtn = document.querySelector('.sidebar-toggle');
    
    if (window.innerWidth <= 768) {
        if (!sidebar.contains(e.target) && !toggleBtn.contains(e.target)) {
            sidebar.classList.remove('show');
            overlay.classList.remove('show');
        }
    }
});

// Actualizar estadísticas en tiempo real (AJAX) con manejo de errores mejorado
function updateStats() {
    fetch('ajax-stats.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data && typeof data === 'object') {
                if (data.products !== undefined) {
                    document.getElementById('stat-products').textContent = data.products;
                    document.getElementById('products-badge').textContent = data.products;
                }
                if (data.categories !== undefined) {
                    document.getElementById('stat-categories').textContent = data.categories;
                    document.getElementById('categories-badge').textContent = data.categories;
                }
                if (data.lowStock !== undefined || data.low_stock !== undefined) {
                    const lowStock = data.lowStock || data.low_stock;
                    document.getElementById('stat-low-stock').textContent = lowStock;
                }
                if (data.orders !== undefined) {
                    document.getElementById('orders-badge').textContent = data.orders;
                }
                if (data.customers !== undefined) {
                    document.getElementById('customers-badge').textContent = data.customers;
                }
            }
        })
        .catch(error => {
            // Fall silenzioso per non disturbare l'utente
            console.log('Stats update skipped (file not found or error):', error.message);
        });
}

// Actualizar estadísticas cada 5 minutos
setInterval(updateStats, 300000);

// Actualizar estadísticas al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    updateStats();
});

// Resaltar elemento activo en el menú (fallback JavaScript)
document.addEventListener('DOMContentLoaded', function() {
    const currentPage = window.location.pathname.split('/').pop();
    const menuItems = document.querySelectorAll('.sidebar-menu a');
    
    menuItems.forEach(item => {
        const href = item.getAttribute('href');
        if (href && currentPage && href.includes(currentPage)) {
            item.classList.add('active');
        }
    });
});
</script>