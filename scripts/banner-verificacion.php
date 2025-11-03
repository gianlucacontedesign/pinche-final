<?php
/**
 * Banner de Verificación de Email
 * Muestra un banner no invasivo cuando el usuario no ha verificado su email
 * 
 * Uso:
 * include_once 'banner-verificacion.php';
 * mostrar_banner_verificacion($usuario_verificado, $email_usuario, $url_reenvio);
 */

// Función para mostrar el banner de verificación
function mostrar_banner_verificacion($usuario_verificado = false, $email_usuario = '', $url_reenvio = '', $mensaje_personalizado = '') {
    // Si ya está verificado, no mostrar banner
    if ($usuario_verificado) {
        return;
    }
    
    // Configuración del mensaje
    $mensaje_default = 'Verifica tu email para acceder a todas las funcionalidades de tu cuenta.';
    $mensaje = $mensaje_personalizado ?: $mensaje_default;
    
    // URL de reenvío por defecto si no se proporciona
    if (empty($url_reenvio)) {
        $url_reenvio = 'reenviar-verificacion.php';
    }
    
    // Obtener parámetros de URL para reenvío
    $params = [];
    if (!empty($email_usuario)) {
        $params['email'] = urlencode($email_usuario);
    }
    $query_string = !empty($params) ? '?' . http_build_query($params) : '';
    $url_reenvio_completa = $url_reenvio . $query_string;
    ?>
    
    <div id="banner-verificacion" class="banner-verificacion-container">
        <div class="banner-verificacion-content">
            <div class="banner-verificacion-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2L13.09 8.26L19 9L13.09 9.74L12 16L10.91 9.74L5 9L10.91 8.26L12 2Z" fill="#f59e0b"/>
                    <circle cx="12" cy="19" r="1.5" fill="#f59e0b"/>
                </svg>
            </div>
            
            <div class="banner-verificacion-text">
                <h4 class="banner-verificacion-title">Verifica tu Email</h4>
                <p class="banner-verificacion-message"><?php echo htmlspecialchars($mensaje); ?></p>
            </div>
            
            <div class="banner-verificacion-actions">
                <button onclick="reenviarVerificacion()" class="banner-btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3 8V5C3 4.46957 3.21071 3.96086 3.58579 3.58579C3.96086 3.21071 4.46957 3 5 3H19C19.5304 3 20.0391 3.21071 20.4142 3.58579C20.7893 3.96086 21 4.46957 21 5V8M21 8L21 19C21 19.5304 20.7893 20.0391 20.4142 20.4142C20.0391 20.7893 19.5304 21 19 21H5C4.46957 21 3.96086 20.7893 3.58579 20.4142C3.21071 20.0391 3 19.5304 3 19V8M21 8L14 15L10 11L3 8" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Reenviar Email
                </button>
                
                <button onclick="cerrarBanner()" class="banner-btn-secondary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Cerrar
                </button>
            </div>
        </div>
    </div>
    
    <script>
    // Función para reenviar verificación
    function reenviarVerificacion() {
        // Mostrar loading
        const btnPrimary = document.querySelector('.banner-btn-primary');
        const textoOriginal = btnPrimary.innerHTML;
        btnPrimary.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="3" fill="white"><animateTransform attributeName="transform" type="rotate" values="0 12 12;360 12 12" dur="1s" repeatCount="indefinite"/></circle></svg> Enviando...';
        btnPrimary.disabled = true;
        
        // Redirigir a página de reenvío
        window.location.href = '<?php echo $url_reenvio_completa; ?>';
    }
    
    // Función para cerrar banner temporalmente
    function cerrarBanner() {
        // Ocultar banner
        const banner = document.getElementById('banner-verificacion');
        banner.style.opacity = '0';
        banner.style.transform = 'translateY(-20px)';
        
        // Guardar en localStorage para no mostrar por 2 horas
        const ahora = new Date().getTime();
        const expiracion = ahora + (2 * 60 * 60 * 1000); // 2 horas
        localStorage.setItem('banner-verificacion-oculto-hasta', expiracion.toString());
        
        // Remover después de la animación
        setTimeout(() => {
            banner.style.display = 'none';
        }, 300);
    }
    
    // Verificar si el banner debe mostrarse al cargar
    document.addEventListener('DOMContentLoaded', function() {
        const banner = document.getElementById('banner-verificacion');
        const ocultoHasta = localStorage.getItem('banner-verificacion-oculto-hasta');
        
        if (ocultoHasta) {
            const ahora = new Date().getTime();
            if (ahora < parseInt(ocultoHasta)) {
                banner.style.display = 'none';
            } else {
                // Limpiar localStorage expirado
                localStorage.removeItem('banner-verificacion-oculto-hasta');
            }
        }
    });
    </script>
    
    <?php
}

// Ejemplo de uso en página
if (isset($_GET['ejemplo'])) {
    // Variables de ejemplo para demostración
    $usuario_verificado = false;
    $email_usuario = 'usuario@example.com';
    $url_reenvio = 'reenviar-verificacion.php';
    
    echo '<!DOCTYPE html><html><head><title>Banner de Verificación - Demo</title><link rel="stylesheet" href="recordatorios-style.css"></head><body>';
    echo '<h1>Demo del Banner de Verificación</h1>';
    echo '<p>Este es un ejemplo de cómo se ve el banner cuando el usuario no ha verificado su email.</p>';
    
    mostrar_banner_verificacion($usuario_verificado, $email_usuario, $url_reenvio);
    
    echo '</body></html>';
}
?>