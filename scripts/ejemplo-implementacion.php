<?php
/**
 * Ejemplo de Implementación de Banners y Recordatorios
 * Este archivo muestra cómo usar los componentes en diferentes contextos
 */

// Incluir los componentes
include_once 'banner-verificacion.php';
include_once 'recordatorio-usuario.php';
include_once 'recordatorios-style.css'; // O incluir desde HTML como <link>

// Función de ejemplo para obtener datos del usuario
function obtener_datos_usuario_demo($email_demo = false) {
    // Simular datos del usuario desde la base de datos
    return [
        'verificado' => $email_demo ? false : true, // false para demo
        'email' => 'usuario@pinchesupplies.com.ar',
        'nombre' => 'Usuario Demo'
    ];
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Banners de Verificación - Ejemplos de Implementación</title>
    <link rel="stylesheet" href="recordatorios-style.css">
    <style>
        /* Estilos adicionales para la demo */
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #374151;
            background-color: #f9fafb;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }
        .header h1 {
            margin: 0 0 10px 0;
            font-size: 2.5em;
            font-weight: 300;
        }
        .header p {
            margin: 0;
            font-size: 1.1em;
            opacity: 0.9;
        }
        .content {
            padding: 40px;
        }
        .section {
            margin-bottom: 60px;
        }
        .section h2 {
            color: #1f2937;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 10px;
            margin-bottom: 30px;
        }
        .demo-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 30px;
            margin: 20px 0;
        }
        .code-block {
            background: #1f2937;
            color: #e5e7eb;
            padding: 20px;
            border-radius: 8px;
            font-family: 'Monaco', 'Menlo', monospace;
            font-size: 14px;
            overflow-x: auto;
            margin: 15px 0;
        }
        .info-box {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            padding: 16px;
            margin: 20px 0;
        }
        .info-box h4 {
            margin: 0 0 8px 0;
            color: #1e40af;
        }
        .info-box p {
            margin: 0;
            color: #1e3a8a;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚨 Banners de Verificación</h1>
            <p>Sistema completo de recordatorios suaves y no invasivos</p>
        </div>

        <div class="content">
            <!-- Banner Principal -->
            <div class="section">
                <h2>📋 1. Banner Principal de Verificación</h2>
                <div class="info-box">
                    <h4>💡 Uso Recomendado</h4>
                    <p>Mostrar en páginas públicas cuando el usuario intenta acceder a funciones restringidas sin estar verificado.</p>
                </div>
                
                <div class="demo-box">
                    <h3>Ejemplo en Acción:</h3>
                    <?php
                    $usuario = obtener_datos_usuario_demo(true); // false = demo con usuario no verificado
                    mostrar_banner_verificacion(
                        $usuario['verificado'],
                        $usuario['email'],
                        'reenviar-verificacion.php'
                    );
                    ?>
                </div>
                
                <h4>Código de Implementación:</h4>
                <div class="code-block">
&lt;?php
// Incluir el componente
include_once 'banner-verificacion.php';

// Obtener datos del usuario desde tu sistema
$usuario = obtener_usuario_actual(); // Tu función

// Mostrar banner solo si no está verificado
mostrar_banner_verificacion(
    $usuario['verificado'],           // Boolean: está verificado
    $usuario['email'],                // String: email del usuario
    'reenviar-verificacion.php',      // String: URL para reenvío
    'Tu mensaje personalizado aquí'   // String: mensaje opcional
);
?&gt;
                </div>
            </div>

            <!-- Recordatorio Panel -->
            <div class="section">
                <h2>👤 2. Recordatorio para Panel de Usuario</h2>
                <div class="info-box">
                    <h4>💡 Uso Recomendado</h4>
                    <p>Mostrar en el dashboard/panel del usuario de forma discreta pero visible.</p>
                </div>
                
                <div class="demo-box">
                    <div style="position: relative; min-height: 120px; padding-top: 20px;">
                        <h3>Ejemplo en Panel de Usuario:</h3>
                        <?php
                        mostrar_recordatorio_usuario(
                            $usuario['verificado'],
                            $usuario['email'],
                            'verificar-email.php',
                            'perfil.php'
                        );
                        ?>
                        <div style="margin-top: 40px; padding: 20px; background: white; border-radius: 8px;">
                            <h4>Contenido del Dashboard...</h4>
                            <p>Aquí iría el contenido normal del panel del usuario.</p>
                        </div>
                    </div>
                </div>
                
                <h4>Código de Implementación:</h4>
                <div class="code-block">
&lt;?php
// En tu página de dashboard/panel de usuario
include_once 'recordatorio-usuario.php';

$usuario = obtener_usuario_actual();

// Mostrar recordatorio discreto
mostrar_recordatorio_usuario(
    $usuario['verificado'],          // Boolean: estado de verificación
    $usuario['email'],               // String: email del usuario
    'verificar-email.php',           // String: URL de verificación
    'perfil.php',                    // String: URL para cambiar email
    'top'                            // String: 'top' o 'bottom'
);
?&gt;
                </div>
            </div>

            <!-- Notificación Mínima -->
            <div class="section">
                <h2>📧 3. Notificación Mínima (Alternativa)</h2>
                <div class="info-box">
                    <h4>💡 Uso Recomendado</h4>
                    <p>Para espacios muy limitados o como recordatorio muy discreto.</p>
                </div>
                
                <div class="demo-box">
                    <h3>Ejemplo de Notificación Mínima:</h3>
                    <?php mostrar_notificacion_verificacion($usuario['email']); ?>
                </div>
                
                <h4>Código de Implementación:</h4>
                <div class="code-block">
&lt;?php
// Mostrar notificación muy discreta
mostrar_notificacion_verificacion(
    $usuario['email'],               // String: email del usuario
    'verificar-email.php'            // String: URL de verificación
);
?&gt;
                </div>
            </div>

            <!-- Características -->
            <div class="section">
                <h2>⭐ Características de los Banners</h2>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                    <div class="info-box">
                        <h4>🔇 No Invasivos</h4>
                        <p>Diseño suave que no interrumpe la experiencia del usuario.</p>
                    </div>
                    
                    <div class="info-box">
                        <h4>🔄 Cierre Temporal</h4>
                        <p>Se pueden cerrar temporalmente y no reaparecen inmediatamente.</p>
                    </div>
                    
                    <div class="info-box">
                        <h4>📱 Responsivos</h4>
                        <p>Se adaptan perfectamente a móviles y tablets.</p>
                    </div>
                    
                    <div class="info-box">
                        <h4>♿ Accesibles</h4>
                        <p>Cumplen estándares de accesibilidad web.</p>
                    </div>
                    
                    <div class="info-box">
                        <h4>🎨 Personalizables</h4>
                        <p>Colores y estilos modificables vía CSS.</p>
                    </div>
                    
                    <div class="info-box">
                        <h4>⚡ Animaciones Suaves</h4>
                        <p>Transiciones elegantes que mejoran la UX.</p>
                    </div>
                </div>
            </div>

            <!-- Instrucciones de Uso -->
            <div class="section">
                <h2>🚀 Instrucciones de Implementación</h2>
                
                <h3>Paso 1: Incluir Archivos</h3>
                <div class="code-block">
// En el &lt;head&gt; de tu página
&lt;link rel="stylesheet" href="recordatorios-style.css"&gt;

// Donde necesites los banners
&lt;?php include_once 'banner-verificacion.php'; ?&gt;
&lt;?php include_once 'recordatorio-usuario.php'; ?&gt;
                </div>
                
                <h3>Paso 2: Función para Obtener Usuario</h3>
                <div class="code-block">
function obtener_usuario_actual() {
    // Tu lógica para obtener datos del usuario
    return [
        'verificado' => $_SESSION['usuario_verificado'] ?? false,
        'email' => $_SESSION['usuario_email'] ?? '',
        'nombre' => $_SESSION['usuario_nombre'] ?? ''
    ];
}
                </div>
                
                <h3>Paso 3: Mostrar en Páginas</h3>
                <div class="code-block">
// En páginas públicas (login, checkout, etc.)
$usuario = obtener_usuario_actual();
if (!$usuario['verificado']) {
    mostrar_banner_verificacion(false, $usuario['email']);
}

// En panel de usuario (dashboard.php)
$usuario = obtener_usuario_actual();
mostrar_recordatorio_usuario(
    $usuario['verificado'],
    $usuario['email'],
    'verificar-email.php',
    'perfil.php'
);
                </div>
            </div>

            <!-- Demo Controls -->
            <div class="section">
                <h2>🎛️ Demo Interactivo</h2>
                <div class="info-box">
                    <h4>🔄 Probar Componentes</h4>
                    <p>Los demos arriba muestran los componentes con un usuario no verificado. ¡Puedes probar cerrar los banners!</p>
                </div>
                
                <div style="text-align: center; margin-top: 20px;">
                    <a href="?demo_dashboard=1" style="background: #3b82f6; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; font-weight: 500;">
                        Ver Demo de Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php if (isset($_GET['demo_dashboard'])): ?>
    <script>
        // Si se accedió al demo del dashboard, mostrar un panel simulado
        console.log('Demo de Dashboard cargado');
    </script>
    <?php endif; ?>
</body>
</html>