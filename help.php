<?php
/**
 * Página de Ayuda y Documentación
 */

require_once 'config/config.php';
require_once 'includes/class.database.php';
require_once 'includes/class.auth.php';

// Verificar que el usuario esté logueado como admin
$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header('Location: ' . ADMIN_URL . '/login.php');
    exit;
}

$activeSection = $_GET['section'] ?? 'overview';
$pageTitle = 'Ayuda y Documentación - Panel de Administración';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($pageTitle); ?></title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .help-container {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: var(--spacing-6);
            max-width: 1400px;
            margin: 0 auto;
            padding: var(--spacing-6);
            min-height: calc(100vh - 120px);
        }
        
        .help-sidebar {
            background: var(--color-white);
            border-radius: var(--radius-lg);
            padding: var(--spacing-6);
            box-shadow: var(--shadow-base);
            height: fit-content;
            position: sticky;
            top: var(--spacing-6);
        }
        
        .help-nav-title {
            font-size: var(--font-size-lg);
            font-weight: 700;
            color: var(--color-primary);
            margin-bottom: var(--spacing-4);
            text-align: center;
        }
        
        .help-nav {
            list-style: none;
        }
        
        .help-nav li {
            margin-bottom: var(--spacing-2);
        }
        
        .help-nav a {
            display: flex;
            align-items: center;
            padding: var(--spacing-3) var(--spacing-4);
            color: var(--color-gray-700);
            text-decoration: none;
            border-radius: var(--radius-base);
            transition: all var(--transition-fast);
        }
        
        .help-nav a:hover {
            background: var(--color-gray-100);
            color: var(--color-primary);
        }
        
        .help-nav a.active {
            background: var(--color-primary);
            color: var(--color-white);
        }
        
        .help-nav svg {
            width: 20px;
            height: 20px;
            margin-right: var(--spacing-3);
        }
        
        .help-content {
            background: var(--color-white);
            border-radius: var(--radius-lg);
            padding: var(--spacing-8);
            box-shadow: var(--shadow-base);
        }
        
        .help-section {
            display: none;
        }
        
        .help-section.active {
            display: block;
        }
        
        .help-section-title {
            font-size: var(--font-size-2xl);
            font-weight: 700;
            color: var(--color-gray-900);
            margin-bottom: var(--spacing-6);
            padding-bottom: var(--spacing-3);
            border-bottom: 2px solid var(--color-gray-100);
        }
        
        .help-subsection {
            margin-bottom: var(--spacing-8);
        }
        
        .help-subsection-title {
            font-size: var(--font-size-lg);
            font-weight: 600;
            color: var(--color-gray-900);
            margin-bottom: var(--spacing-4);
        }
        
        .help-text {
            color: var(--color-gray-600);
            line-height: 1.6;
            margin-bottom: var(--spacing-4);
        }
        
        .help-list {
            margin: var(--spacing-4) 0;
            padding-left: var(--spacing-6);
        }
        
        .help-list li {
            margin-bottom: var(--spacing-2);
            color: var(--color-gray-600);
        }
        
        .help-code {
            background: var(--color-gray-100);
            border: 1px solid var(--color-gray-300);
            border-radius: var(--radius-base);
            padding: var(--spacing-4);
            font-family: 'Monaco', 'Courier New', monospace;
            font-size: var(--font-size-sm);
            overflow-x: auto;
            margin: var(--spacing-4) 0;
        }
        
        .help-alert {
            background: var(--color-gray-100);
            border-left: 4px solid var(--color-info);
            padding: var(--spacing-4);
            margin: var(--spacing-4) 0;
            border-radius: var(--radius-base);
        }
        
        .help-alert-warning {
            border-left-color: var(--color-warning);
        }
        
        .help-alert-error {
            border-left-color: var(--color-error);
        }
        
        .help-alert-success {
            border-left-color: var(--color-success);
        }
        
        .help-step {
            background: var(--color-gray-100);
            border-radius: var(--radius-base);
            padding: var(--spacing-4);
            margin: var(--spacing-4) 0;
        }
        
        .help-step-number {
            background: var(--color-primary);
            color: var(--color-white);
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: var(--font-size-sm);
            font-weight: 600;
            margin-right: var(--spacing-3);
        }
        
        @media (max-width: 768px) {
            .help-container {
                grid-template-columns: 1fr;
                gap: var(--spacing-4);
            }
            
            .help-sidebar {
                position: static;
            }
            
            .help-content {
                padding: var(--spacing-6);
            }
        }
    </style>
</head>
<body>
    <div class="help-container">
        <nav class="help-sidebar">
            <h2 class="help-nav-title">Documentación</h2>
            <ul class="help-nav">
                <li>
                    <a href="?section=overview" class="<?php echo $activeSection === 'overview' ? 'active' : ''; ?>">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2V5a2 2 0 012-2h14a2 2 0 012 2v2"></path>
                        </svg>
                        Introducción
                    </a>
                </li>
                <li>
                    <a href="?section=login" class="<?php echo $activeSection === 'login' ? 'active' : ''; ?>">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                        </svg>
                        Acceso al Panel
                    </a>
                </li>
                <li>
                    <a href="?section=products" class="<?php echo $activeSection === 'products' ? 'active' : ''; ?>">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                        Gestión de Productos
                    </a>
                </li>
                <li>
                    <a href="?section=categories" class="<?php echo $activeSection === 'categories' ? 'active' : ''; ?>">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                        Gestión de Categorías
                    </a>
                </li>
                <li>
                    <a href="?section=orders" class="<?php echo $activeSection === 'orders' ? 'active' : ''; ?>">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                        </svg>
                        Gestión de Pedidos
                    </a>
                </li>
                <li>
                    <a href="?section=settings" class="<?php echo $activeSection === 'settings' ? 'active' : ''; ?>">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Configuración
                    </a>
                </li>
                <li>
                    <a href="?section=backup" class="<?php echo $activeSection === 'backup' ? 'active' : ''; ?>">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        Backups
                    </a>
                </li>
                <li>
                    <a href="?section=troubleshooting" class="<?php echo $activeSection === 'troubleshooting' ? 'active' : ''; ?>">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        Solución de Problemas
                    </a>
                </li>
            </ul>
        </nav>
        
        <main class="help-content">
            <!-- Introducción -->
            <div class="help-section <?php echo $activeSection === 'overview' ? 'active' : ''; ?>">
                <h1 class="help-section-title">
                    <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right: var(--spacing-3); vertical-align: middle;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Introducción al Panel de Administración
                </h1>
                
                <div class="help-text">
                    <p>Bienvenido al panel de administración de <strong><?php echo e(SITE_NAME); ?></strong>. Esta documentación te ayudará a gestionar eficientemente tu tienda online.</p>
                </div>
                
                <div class="help-subsection">
                    <h3 class="help-subsection-title">¿Qué puedes hacer aquí?</h3>
                    <ul class="help-list">
                        <li><strong>Gestionar productos:</strong> Crear, editar y eliminar productos de tu catálogo</li>
                        <li><strong>Organizar categorías:</strong> Estructurar tus productos en categorías</li>
                        <li><strong>Administrar pedidos:</strong> Ver y gestionar todas las órdenes de compra</li>
                        <li><strong>Configurar el sistema:</strong> Personalizar ajustes generales de la tienda</li>
                        <li><strong>Realizar backups:</strong> Crear respaldos de seguridad de tu base de datos</li>
                        <li><strong>Monitorear estadísticas:</strong> Ver datos importantes del rendimiento de la tienda</li>
                    </ul>
                </div>
                
                <div class="help-alert help-alert-success">
                    <strong>💡 Tip:</strong> Utiliza el menú lateral para navegar entre las diferentes secciones de la documentación.
                </div>
            </div>
            
            <!-- Acceso al Panel -->
            <div class="help-section <?php echo $activeSection === 'login' ? 'active' : ''; ?>">
                <h1 class="help-section-title">
                    <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right: var(--spacing-3); vertical-align: middle;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                    </svg>
                    Acceso al Panel de Administración
                </h1>
                
                <div class="help-step">
                    <span class="help-step-number">1</span>
                    <strong>Acceder a la página de login:</strong>
                    <p class="help-text">Ve a <code>login-admin.php</code> en tu navegador o usa el enlace del menú principal.</p>
                </div>
                
                <div class="help-step">
                    <span class="help-step-number">2</span>
                    <strong>Ingresar credenciales:</strong>
                    <p class="help-text">Utiliza las credenciales por defecto o las que hayas configurado:</p>
                    <ul class="help-list">
                        <li><strong>Usuario:</strong> admin</li>
                        <li><strong>Contraseña:</strong> admin123</li>
                    </ul>
                </div>
                
                <div class="help-step">
                    <span class="help-step-number">3</span>
                    <strong>Cambiar credenciales:</strong>
                    <div class="help-alert help-alert-warning">
                        <strong>⚠️ Importante:</strong> Cambia las credenciales por defecto inmediatamente por seguridad.
                    </div>
                </div>
                
                <div class="help-subsection">
                    <h3 class="help-subsection-title">Problemas comunes de acceso</h3>
                    <ul class="help-list">
                        <li><strong>Credenciales incorrectas:</strong> Verifica usuario y contraseña</li>
                        <li><strong>Sesión expirada:</strong> Inicia sesión nuevamente</li>
                        <li><strong>Error de conexión:</strong> Verifica la configuración de la base de datos</li>
                    </ul>
                </div>
            </div>
            
            <!-- Gestión de Productos -->
            <div class="help-section <?php echo $activeSection === 'products' ? 'active' : ''; ?>">
                <h1 class="help-section-title">
                    <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right: var(--spacing-3); vertical-align: middle;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                    Gestión de Productos
                </h1>
                
                <div class="help-step">
                    <span class="help-step-number">1</span>
                    <strong>Ver productos existentes:</strong>
                    <p class="help-text">Accede a la sección "Productos" desde el menú lateral para ver todos los productos en tu catálogo.</p>
                </div>
                
                <div class="help-step">
                    <span class="help-step-number">2</span>
                    <strong>Agregar nuevo producto:</strong>
                    <ul class="help-list">
                        <li>Haz clic en "Agregar Producto"</li>
                        <li>Completa la información básica (nombre, precio, descripción)</li>
                        <li>Selecciona una categoría</li>
                        <li>Agrega imágenes del producto</li>
                        <li>Configura el stock disponible</li>
                        <li>Guarda los cambios</li>
                    </ul>
                </div>
                
                <div class="help-step">
                    <span class="help-step-number">3</span>
                    <strong>Editar producto existente:</strong>
                    <p class="help-text">Haz clic en el ícono de edición (✏️) junto a cualquier producto para modificar sus detalles.</p>
                </div>
                
                <div class="help-subsection">
                    <h3 class="help-subsection-title">Campos importantes</h3>
                    <ul class="help-list">
                        <li><strong>Nombre:</strong> Título visible del producto</li>
                        <li><strong>Precio:</strong> Costo del producto (sin impuestos)</li>
                        <li><strong>Stock:</strong> Cantidad disponible</li>
                        <li><strong>Estado:</strong> Producto activo o inactivo</li>
                        <li><strong>SKU:</strong> Código único del producto (opcional)</li>
                    </ul>
                </div>
                
                <div class="help-alert">
                    <strong>💡 Optimización:</strong> Usa imágenes de alta calidad (mínimo 800x800px) para mejor experiencia visual.
                </div>
            </div>
            
            <!-- Gestión de Categorías -->
            <div class="help-section <?php echo $activeSection === 'categories' ? 'active' : ''; ?>">
                <h1 class="help-section-title">
                    <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right: var(--spacing-3); vertical-align: middle;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                    Gestión de Categorías
                </h1>
                
                <div class="help-step">
                    <span class="help-step-number">1</span>
                    <strong>Crear nueva categoría:</strong>
                    <ul class="help-list">
                        <li>Ve a "Categorías" en el menú lateral</li>
                        <li>Haz clic en "Agregar Categoría"</li>
                        <li>Escribe el nombre y descripción</li>
                        <li>Selecciona la categoría padre (si es una subcategoría)</li>
                        <li>Guarda la categoría</li>
                    </ul>
                </div>
                
                <div class="help-subsection">
                    <h3 class="help-subsection-title">Estructura de categorías</h3>
                    <p class="help-text">Puedes crear categorías jerárquicas:</p>
                    <ul class="help-list">
                        <li><strong>Categorías principales:</strong> Ropa, Electrónicos, Hogar</li>
                        <li><strong>Subcategorías:</strong> Ropa > Camisas, Pantalones</li>
                    </ul>
                </div>
                
                <div class="help-alert help-alert-warning">
                    <strong>⚠️ No elimines categorías</strong> que tengan productos asignados, primero reasigna los productos a otra categoría.
                </div>
            </div>
            
            <!-- Gestión de Pedidos -->
            <div class="help-section <?php echo $activeSection === 'orders' ? 'active' : ''; ?>">
                <h1 class="help-section-title">
                    <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right: var(--spacing-3); vertical-align: middle;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                    </svg>
                    Gestión de Pedidos
                </h1>
                
                <div class="help-step">
                    <span class="help-step-number">1</span>
                    <strong>Ver lista de pedidos:</strong>
                    <p class="help-text">Accede a "Pedidos" para ver todas las órdenes de compra ordenadas por fecha (más recientes primero).</p>
                </div>
                
                <div class="help-step">
                    <span class="help-step-number">2</span>
                    <strong>Estados de pedidos:</strong>
                    <ul class="help-list">
                        <li><strong>Pending:</strong> Pedido recién recibido</li>
                        <li><strong>Processing:</strong> En preparación</li>
                        <li><strong>Shipped:</strong> Enviado al cliente</li>
                        <li><strong>Delivered:</strong> Entregado</li>
                        <li><strong>Cancelled:</strong> Cancelado</li>
                    </ul>
                </div>
                
                <div class="help-step">
                    <span class="help-step-number">3</span>
                    <strong>Actualizar estado:</strong>
                    <p class="help-text">Haz clic en "Detalles" para ver información completa del pedido y cambiar su estado.</p>
                </div>
                
                <div class="help-subsection">
                    <h3 class="help-subsection-title">Información de pedidos</h3>
                    <p class="help-text">Cada pedido incluye:</p>
                    <ul class="help-list">
                        <li>Datos del cliente (nombre, email, teléfono)</li>
                        <li>Dirección de envío</li>
                        <li>Lista de productos adquiridos</li>
                        <li>Total del pedido (subtotal + impuestos + envío)</li>
                        <li>Fecha y hora del pedido</li>
                    </ul>
                </div>
            </div>
            
            <!-- Configuración -->
            <div class="help-section <?php echo $activeSection === 'settings' ? 'active' : ''; ?>">
                <h1 class="help-section-title">
                    <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right: var(--spacing-3); vertical-align: middle;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    Configuración del Sistema
                </h1>
                
                <div class="help-subsection">
                    <h3 class="help-subsection-title">Configuración general</h3>
                    <p class="help-text">Personaliza la información básica de tu tienda:</p>
                    <ul class="help-list">
                        <li><strong>Nombre del sitio:</strong> Aparece en el navegador y comunicaciones</li>
                        <li><strong>Email del sitio:</strong> Para notificaciones y contacto</li>
                        <li><strong>Descripción:</strong> Para SEO y referencias</li>
                    </ul>
                </div>
                
                <div class="help-subsection">
                    <h3 class="help-subsection-title">Configuración de email</h3>
                    <p class="help-text">Configura el envío de emails automáticos:</p>
                    <div class="help-code">
SMTP Host: smtp.gmail.com
Puerto: 587
Usuario: tu-email@gmail.com
Contraseña: tu-app-password
                    </div>
                </div>
                
                <div class="help-subsection">
                    <h3 class="help-subsection-title">Configuración comercial</h3>
                    <ul class="help-list">
                        <li><strong>Moneda:</strong> Símbolo y código (USD, EUR, etc.)</li>
                        <li><strong>Impuestos:</strong> Porcentaje aplicable</li>
                        <li><strong>Envío:</strong> Costos de envío y envío gratis</li>
                    </ul>
                </div>
                
                <div class="help-alert help-alert-warning">
                    <strong>⚠️ Cambios inmediatos:</strong> Los ajustes se aplican inmediatamente al guardar.
                </div>
            </div>
            
            <!-- Backups -->
            <div class="help-section <?php echo $activeSection === 'backup' ? 'active' : ''; ?>">
                <h1 class="help-section-title">
                    <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right: var(--spacing-3); vertical-align: middle;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                    Respaldos de Base de Datos
                </h1>
                
                <div class="help-step">
                    <span class="help-step-number">1</span>
                    <strong>Crear backup manual:</strong>
                    <ul class="help-list">
                        <li>Ve a la sección "Backups"</li>
                        <li>Haz clic en "Crear Backup"</li>
                        <li>Espera a que se complete el proceso</li>
                        <li>Descarga el archivo SQL si es necesario</li>
                    </ul>
                </div>
                
                <div class="help-subsection">
                    <h3 class="help-subsection-title">¿Qué incluye un backup?</h3>
                    <ul class="help-list">
                        <li>Estructura completa de todas las tablas</li>
                        <li>Datos de todos los registros</li>
                        <li>Metadatos y configuración de base de datos</li>
                    </ul>
                </div>
                
                <div class="help-subsection">
                    <h3 class="help-subsection-title">Ubicación de backups</h3>
                    <p class="help-text">Los archivos se guardan en:</p>
                    <div class="help-code">/backups/YYYY-MM-DD/backup_YYYY-MM-DD_HH-MM-SS.sql</div>
                </div>
                
                <div class="help-alert">
                    <strong>💡 Recomendación:</strong> Realiza backups regulares, especialmente antes de cambios importantes.
                </div>
            </div>
            
            <!-- Solución de Problemas -->
            <div class="help-section <?php echo $activeSection === 'troubleshooting' ? 'active' : ''; ?>">
                <h1 class="help-section-title">
                    <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right: var(--spacing-3); vertical-align: middle;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    Solución de Problemas
                </h1>
                
                <div class="help-subsection">
                    <h3 class="help-subsection-title">Problemas comunes</h3>
                    
                    <div class="help-step">
                        <h4>No puedo acceder al panel de administración</h4>
                        <ul class="help-list">
                            <li>Verifica las credenciales (admin / admin123)</li>
                            <li>Comprueba la conexión a la base de datos</li>
                            <li>Revisa el archivo de configuración (config.php)</li>
                        </ul>
                    </div>
                    
                    <div class="help-step">
                        <h4>Error al crear productos</h4>
                        <ul class="help-list">
                            <li>Asegúrate de que la categoría existe</li>
                            <li>Verifica que el precio sea un número válido</li>
                            <li>Comprueba permisos de escritura en uploads/</li>
                        </ul>
                    </div>
                    
                    <div class="help-step">
                        <h4>Las imágenes no se cargan</h4>
                        <ul class="help-list">
                            <li>Verifica permisos en la carpeta assets/images/</li>
                            <li>Comprueba que el tamaño del archivo sea menor a 5MB</li>
                            <li>Asegúrate de que el formato sea JPG, PNG o GIF</li>
                        </ul>
                    </div>
                </div>
                
                <div class="help-subsection">
                    <h3 class="help-subsection-title">Archivos de log</h3>
                    <p class="help-text">Para diagnosticar problemas, revisa estos archivos:</p>
                    <ul class="help-list">
                        <li><strong>/logs/error.log:</strong> Errores generales del sistema</li>
                        <li><strong>/logs/access.log:</strong> Accesos y actividad</li>
                        <li><strong>/config/backup.log:</strong> Registro de backups</li>
                    </ul>
                </div>
                
                <div class="help-alert help-alert-error">
                    <strong>🚨 Problemas críticos:</strong> Si el sitio no funciona, verifica primero la conexión a la base de datos y los permisos de archivos.
                </div>
            </div>
        </main>
    </div>
</body>
</html>