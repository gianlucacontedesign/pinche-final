<!-- Admin Sidebar -->
<aside class="admin-sidebar">
    <nav class="admin-nav">
        <a href="<?php echo ADMIN_URL; ?>/index.php" 
           class="admin-nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
            📊 Dashboard
        </a>
        
        <div class="admin-nav-section">
            <p class="admin-nav-section-title">Catálogo</p>
            
            <a href="<?php echo ADMIN_URL; ?>/categories.php" 
               class="admin-nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'categories') !== false ? 'active' : ''; ?>">
                📂 Categorías
            </a>
            
            <a href="<?php echo ADMIN_URL; ?>/products.php" 
               class="admin-nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'products') !== false ? 'active' : ''; ?>">
                📦 Productos
            </a>
        </div>
        
        <div class="admin-nav-section">
            <p class="admin-nav-section-title">Ventas</p>
            
            <a href="<?php echo ADMIN_URL; ?>/orders.php" 
               class="admin-nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'orders') !== false ? 'active' : ''; ?>">
                🛒 Pedidos
            </a>
        </div>
        
        <div class="admin-nav-section">
            <p class="admin-nav-section-title">Configuración</p>
            
            <a href="<?php echo ADMIN_URL; ?>/settings.php" 
               class="admin-nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
                ⚙️ Ajustes
            </a>
        </div>
    </nav>
</aside>
