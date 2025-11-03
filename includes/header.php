<!-- Header Estilo Rosa Monkey con Megamenú -->

<!-- Barra de Promociones Rotativa -->
<div class="promo-bar">
    <div class="promo-slider">
        <div class="promo-slide active">
            <span>✨ HASTA 6 CUOTAS SIN INTERÉS</span>
        </div>
        <div class="promo-slide">
            <span>🚚 ENVÍOS A TODO EL PAÍS</span>
        </div>
        <div class="promo-slide">
            <span>📱 ATENCIÓN POR WHATSAPP: <?php echo e(getSetting('site_phone', '+54 11 1234-5678')); ?></span>
        </div>
        <div class="promo-slide">
            <span>🕒 <?php echo e(getSetting('customer_service_hours', 'Lun-Vie 9-18hs, Sáb 9-13hs')); ?></span>
        </div>
    </div>
</div>

<header class="header-rosamonkey">
    <!-- Barra Superior con Íconos -->
    <div class="header-top">
        <div class="header-container">
            <!-- Logo -->
            <a href="<?php echo SITE_URL; ?>" class="logo-rosamonkey">
                <?php 
                // Buscar logo en assets/images (prioridad: PNG > JPG > SVG)
                $logoPath = null;
                $logoExtensions = ['png', 'jpg', 'jpeg', 'svg'];
                foreach ($logoExtensions as $ext) {
                    if (file_exists(__DIR__ . '/../assets/images/logo.' . $ext)) {
                        $logoPath = ASSETS_URL . '/images/logo.' . $ext;
                        break;
                    }
                }
                
                if ($logoPath): ?>
                    <img src="<?php echo $logoPath; ?>" alt="<?php echo e(SITE_NAME); ?>" class="logo-img">
                <?php else: ?>
                    <span class="logo-text"><?php echo e(SITE_NAME); ?></span>
                <?php endif; ?>
            </a>
            
            <!-- Barra de Búsqueda -->
            <div class="search-bar-rosamonkey">
                <form action="<?php echo SITE_URL; ?>/search.php" method="GET" class="search-form-rosamonkey">
                    <input type="search" 
                           name="q" 
                           placeholder="Buscar productos..." 
                           class="search-input-rosamonkey"
                           value="<?php echo isset($_GET['q']) ? e($_GET['q']) : ''; ?>">
                    <button type="submit" class="search-btn-rosamonkey" aria-label="Buscar">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="m21 21-4.35-4.35"></path>
                        </svg>
                    </button>
                </form>
            </div>
            
            <!-- Acciones del Header -->
            <div class="header-actions-rosamonkey">
                <!-- Botón Buscar Móvil -->
                <button class="icon-btn search-toggle-mobile" aria-label="Buscar">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                    </svg>
                </button>
                
                <!-- Botón Usuario/Cuenta con Dropdown -->
                <?php
                // Verificar si hay un cliente logueado
                $isCustomerLoggedIn = false;
                $customerName = '';
                if (isset($_SESSION['customer_logged_in']) && $_SESSION['customer_logged_in'] === true) {
                    $isCustomerLoggedIn = true;
                    $customerName = $_SESSION['customer_first_name'] ?? 'Usuario';
                }
                ?>
                
                <?php if ($isCustomerLoggedIn): ?>
                    <!-- Usuario Logueado -->
                    <div class="account-dropdown">
                        <button class="icon-btn account-btn" aria-label="Mi cuenta">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                        </button>
                        <div class="account-dropdown-menu">
                            <div class="dropdown-header">
                                <p class="dropdown-user-name">Hola, <?php echo e($customerName); ?></p>
                            </div>
                            <div class="dropdown-divider"></div>
                            <a href="<?php echo SITE_URL; ?>/account.php" class="dropdown-item">
                                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                Mi Perfil
                            </a>
                            <a href="<?php echo SITE_URL; ?>/account.php?tab=orders" class="dropdown-item">
                                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                </svg>
                                Mis Pedidos
                            </a>
                            <a href="<?php echo SITE_URL; ?>/account.php?tab=security" class="dropdown-item">
                                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                                Seguridad
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="<?php echo SITE_URL; ?>/logout.php" class="dropdown-item dropdown-item-danger">
                                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                </svg>
                                Cerrar Sesión
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Usuario No Logueado -->
                    <div class="account-dropdown">
                        <button class="icon-btn account-btn" aria-label="Mi cuenta">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                        </button>
                        <div class="account-dropdown-menu">
                            <div class="dropdown-header">
                                <p class="dropdown-title">¡Hola! Iniciá Sesión</p>
                            </div>
                            <a href="<?php echo SITE_URL; ?>/login.php" class="dropdown-item">
                                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                                </svg>
                                Iniciar Sesión
                            </a>
                            <a href="<?php echo SITE_URL; ?>/register.php" class="dropdown-item">
                                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                                </svg>
                                Crear Cuenta
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Botón Carrito con Badge -->
                <a href="<?php echo SITE_URL; ?>/cart.php" class="icon-btn cart-btn-rosamonkey" aria-label="Carrito de compras">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="9" cy="21" r="1"></circle>
                        <circle cx="20" cy="21" r="1"></circle>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                    </svg>
                    <span class="cart-badge" id="cart-badge">0</span>
                </a>
                
                <!-- Botón Menú Móvil -->
                <button class="mobile-menu-toggle" aria-label="Menú" id="mobile-menu-toggle">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </div>
    </div>
    
    <!-- Navegación Principal con Megamenú -->
    <nav class="nav-rosamonkey" id="main-nav">
        <div class="nav-container-rosamonkey">
            <a href="<?php echo SITE_URL; ?>" class="nav-link-rosamonkey <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                Inicio
            </a>
            
            <!-- Menú Productos con Megamenú Desplegable -->
            <div class="nav-dropdown">
                <a href="<?php echo SITE_URL; ?>/category.php" class="nav-link-rosamonkey <?php echo basename($_SERVER['PHP_SELF']) == 'category.php' ? 'active' : ''; ?>">
                    Productos
                    <svg class="dropdown-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </a>
                
                <!-- Megamenú -->
                <div class="megamenu">
                    <div class="megamenu-container">
                        <?php
                        // Obtener categorías para el megamenú
                        $categoryModel = new Category();
                        $categories = $categoryModel->getParentCategories();
                        
                        // Dividir en columnas (máximo 4 columnas)
                        $columnsCount = min(count($categories), 4);
                        $categoriesPerColumn = ceil(count($categories) / $columnsCount);
                        
                        for ($col = 0; $col < $columnsCount; $col++):
                            $startIndex = $col * $categoriesPerColumn;
                            $columnCategories = array_slice($categories, $startIndex, $categoriesPerColumn);
                        ?>
                        <div class="megamenu-column">
                            <?php foreach ($columnCategories as $category): ?>
                            <div class="megamenu-section">
                                <a href="<?php echo SITE_URL; ?>/category.php?slug=<?php echo e($category['slug']); ?>" class="megamenu-title">
                                    <?php echo e($category['name']); ?>
                                </a>
                                <?php
                                // Obtener subcategorías si existen
                                $subcategories = $categoryModel->getChildren($category['id']);
                                if (!empty($subcategories)):
                                ?>
                                <ul class="megamenu-list">
                                    <?php foreach ($subcategories as $subcategory): ?>
                                    <li>
                                        <a href="<?php echo SITE_URL; ?>/category.php?slug=<?php echo e($subcategory['slug']); ?>">
                                            <?php echo e($subcategory['name']); ?>
                                        </a>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endfor; ?>
                        
                        <!-- Columna Destacada -->
                        <div class="megamenu-featured">
                            <div class="megamenu-promo">
                                <h3>¡Nuevos Productos!</h3>
                                <p>Descubrí lo último en equipamiento profesional</p>
                                <a href="<?php echo SITE_URL; ?>/category.php" class="btn-megamenu">Ver Todo</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <a href="<?php echo SITE_URL; ?>/about.php" class="nav-link-rosamonkey">
                Sobre Nosotros
            </a>
            
            <a href="<?php echo SITE_URL; ?>/contact.php" class="nav-link-rosamonkey">
                Contacto
            </a>
        </div>
    </nav>
    
    <!-- Búsqueda Móvil Expandible -->
    <div class="mobile-search" id="mobile-search">
        <div class="mobile-search-container">
            <form action="<?php echo SITE_URL; ?>/search.php" method="GET">
                <input type="search" 
                       name="q" 
                       placeholder="Buscar productos..." 
                       class="mobile-search-input"
                       value="<?php echo isset($_GET['q']) ? e($_GET['q']) : ''; ?>">
                <button type="submit" class="mobile-search-btn">Buscar</button>
            </form>
        </div>
    </div>
</header>

<?php
// Mostrar mensajes flash
$flash = getFlashMessage();
if ($flash):
?>
<div class="flash-message flash-<?php echo e($flash['type']); ?>">
    <span><?php echo e($flash['message']); ?></span>
    <button class="flash-close" onclick="this.parentElement.style.display='none'">&times;</button>
</div>

<style>
.flash-message {
    position: fixed;
    top: 120px;
    right: 20px;
    z-index: 9999;
    background: <?php echo $flash['type'] == 'success' ? '#16a34a' : '#dc2626'; ?>;
    color: white;
    padding: 1rem 1.5rem;
    border-radius: 0.5rem;
    box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
    max-width: 400px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    animation: slideIn 0.3s ease-out;
    transition: opacity 0.3s ease-out;
}

.flash-close {
    background: none;
    border: none;
    color: white;
    font-size: 1.5rem;
    margin-left: 1rem;
    cursor: pointer;
}

@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}
</style>

<script>
// Auto-dismiss flash messages
setTimeout(function() {
    const flashMsg = document.querySelector('.flash-message');
    if (flashMsg) {
        flashMsg.style.opacity = '0';
        setTimeout(() => flashMsg.remove(), 300);
    }
}, 5000);

// Promo bar rotation
let currentPromoSlide = 0;
const promoSlides = document.querySelectorAll('.promo-slide');

function rotatePromoBar() {
    promoSlides[currentPromoSlide].classList.remove('active');
    currentPromoSlide = (currentPromoSlide + 1) % promoSlides.length;
    promoSlides[currentPromoSlide].classList.add('active');
}

setInterval(rotatePromoBar, 3000);

// Mobile menu toggle
const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
const mainNav = document.getElementById('main-nav');

if (mobileMenuToggle) {
    mobileMenuToggle.addEventListener('click', function() {
        this.classList.toggle('active');
        mainNav.classList.toggle('active');
        document.body.classList.toggle('menu-open');
    });
}

// Mobile search toggle
const searchToggleMobile = document.querySelector('.search-toggle-mobile');
const mobileSearch = document.getElementById('mobile-search');

if (searchToggleMobile) {
    searchToggleMobile.addEventListener('click', function() {
        mobileSearch.classList.toggle('active');
        if (mobileSearch.classList.contains('active')) {
            mobileSearch.querySelector('.mobile-search-input').focus();
        }
    });
}

// Cerrar megamenú al hacer click fuera
document.addEventListener('click', function(e) {
    if (!e.target.closest('.nav-dropdown') && !e.target.closest('.megamenu')) {
        document.querySelectorAll('.nav-dropdown').forEach(dropdown => {
            dropdown.classList.remove('active');
        });
    }
});

// Actualizar badge del carrito
function updateCartBadge() {
    fetch('<?php echo SITE_URL; ?>/ajax/get-cart-count.php')
        .then(response => response.json())
        .then(data => {
            const badge = document.getElementById('cart-badge');
            if (badge && data.count > 0) {
                badge.textContent = data.count;
                badge.style.display = 'flex';
            } else if (badge) {
                badge.style.display = 'none';
            }
        })
        .catch(error => console.error('Error updating cart badge:', error));
}

// Actualizar al cargar la página
document.addEventListener('DOMContentLoaded', updateCartBadge);
</script>
<?php endif; ?>
