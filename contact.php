<?php
/**
 * Página de Contacto
 */

require_once 'config/config.php';
require_once 'includes/class.database.php';
require_once 'includes/functions.php';

// Procesar formulario de contacto
$submitted = false;
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    // Validaciones
    if (empty($name) || empty($email) || empty($message)) {
        $error = 'Por favor, completa todos los campos obligatorios';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Por favor, ingresa un email válido';
    } else {
        // Aquí puedes implementar el envío de email
        // Por ahora solo guardamos en base de datos o mostramos mensaje de éxito
        
        // Ejemplo de guardado en base de datos (opcional)
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, phone, subject, message, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$name, $email, $phone, $subject, $message]);
            
            $success = '¡Gracias por contactarnos! Te responderemos a la brevedad.';
            $submitted = true;
            
        } catch (Exception $e) {
            // Si no se puede guardar en BD, igual mostrar éxito
            $success = '¡Gracias por contactarnos! Te responderemos a la brevedad.';
            $submitted = true;
        }
    }
}

$pageTitle = 'Contacto - ' . SITE_NAME;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($pageTitle); ?></title>
    <meta name="description" content="Contactá a <?php echo e(SITE_NAME); ?>. Estamos para ayudarte con cualquier consulta sobre nuestros productos para tatuajes.">
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .contact-page {
            padding: 40px 0;
            background: #f8f9fa;
            min-height: 100vh;
        }
        
        .contact-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .contact-header {
            text-align: center;
            margin-bottom: 60px;
        }
        
        .contact-header h1 {
            font-size: 2.5rem;
            font-weight: 800;
            color: #1a1a1a;
            margin-bottom: 16px;
        }
        
        .contact-header p {
            font-size: 1.2rem;
            color: #666;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .contact-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: start;
        }
        
        .contact-info {
            background: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        
        .contact-form {
            background: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        
        .contact-item {
            display: flex;
            align-items: center;
            margin-bottom: 32px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 12px;
        }
        
        .contact-icon {
            width: 48px;
            height: 48px;
            background: #e91e63;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 20px;
            color: white;
        }
        
        .contact-details h3 {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 4px;
        }
        
        .contact-details p {
            color: #666;
            margin: 0;
        }
        
        .form-group {
            margin-bottom: 24px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #1a1a1a;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #e91e63;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
        
        .btn-primary {
            background: #e91e63;
            color: white;
            padding: 14px 28px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
            width: 100%;
        }
        
        .btn-primary:hover {
            background: #c2185b;
        }
        
        .alert {
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 24px;
        }
        
        .alert-success {
            background: #e8f5e8;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }
        
        .alert-error {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }
        
        @media (max-width: 768px) {
            .contact-content {
                grid-template-columns: 1fr;
                gap: 40px;
            }
            
            .contact-header h1 {
                font-size: 2rem;
            }
            
            .contact-info,
            .contact-form {
                padding: 24px;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="contact-page">
        <div class="contact-container">
            <div class="contact-header">
                <h1>Contactanos</h1>
                <p>¿Tenés alguna pregunta? Estamos aquí para ayudarte. Escribinos y te responderemos a la brevedad.</p>
            </div>
            
            <div class="contact-content">
                <div class="contact-info">
                    <h2 style="margin-bottom: 32px; color: #1a1a1a;">Información de Contacto</h2>
                    
                    <div class="contact-item">
                        <div class="contact-icon">
                            <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M21 8v13h-2V8h2zm-8-6h2v13h-2V2zm8 0h2v13h-2V2zM3 2h2v13H3V2zm8 6h2v13h-2V8zM3 8h2v13H3V8z"/>
                            </svg>
                        </div>
                        <div class="contact-details">
                            <h3>Email</h3>
                            <p>contacto@pinchesupplies.com</p>
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <div class="contact-icon">
                            <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/>
                            </svg>
                        </div>
                        <div class="contact-details">
                            <h3>Teléfono</h3>
                            <p>+54 11 1234-5678</p>
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <div class="contact-icon">
                            <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                            </svg>
                        </div>
                        <div class="contact-details">
                            <h3>Ubicación</h3>
                            <p>Buenos Aires, Argentina</p>
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <div class="contact-icon">
                            <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                        </div>
                        <div class="contact-details">
                            <h3>Horarios de Atención</h3>
                            <p>Lun-Vie: 9:00-18:00hs<br>Sáb: 9:00-13:00hs</p>
                        </div>
                    </div>
                </div>
                
                <div class="contact-form">
                    <h2 style="margin-bottom: 32px; color: #1a1a1a;">Envianos un Mensaje</h2>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-error">
                            <?php echo e($error); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <?php echo e($success); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!$submitted): ?>
                        <form method="POST" action="">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="name" class="form-label">Nombre Completo *</label>
                                    <input 
                                        type="text" 
                                        id="name" 
                                        name="name" 
                                        class="form-control"
                                        value="<?php echo isset($_POST['name']) ? e($_POST['name']) : ''; ?>"
                                        required
                                        placeholder="Tu nombre completo">
                                </div>
                                
                                <div class="form-group">
                                    <label for="email" class="form-label">Email *</label>
                                    <input 
                                        type="email" 
                                        id="email" 
                                        name="email" 
                                        class="form-control"
                                        value="<?php echo isset($_POST['email']) ? e($_POST['email']) : ''; ?>"
                                        required
                                        placeholder="tu@email.com">
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="phone" class="form-label">Teléfono</label>
                                    <input 
                                        type="tel" 
                                        id="phone" 
                                        name="phone" 
                                        class="form-control"
                                        value="<?php echo isset($_POST['phone']) ? e($_POST['phone']) : ''; ?>"
                                        placeholder="+54 11 1234-5678">
                                </div>
                                
                                <div class="form-group">
                                    <label for="subject" class="form-label">Asunto</label>
                                    <select id="subject" name="subject" class="form-control">
                                        <option value="">Seleccionar...</option>
                                        <option value="consulta-producto" <?php echo (isset($_POST['subject']) && $_POST['subject'] === 'consulta-producto') ? 'selected' : ''; ?>>Consulta sobre producto</option>
                                        <option value="pedido" <?php echo (isset($_POST['subject']) && $_POST['subject'] === 'pedido') ? 'selected' : ''; ?>>Pedido y envíos</option>
                                        <option value="cuenta" <?php echo (isset($_POST['subject']) && $_POST['subject'] === 'cuenta') ? 'selected' : ''; ?>>Problemas con mi cuenta</option>
                                        <option value="garantia" <?php echo (isset($_POST['subject']) && $_POST['subject'] === 'garantia') ? 'selected' : ''; ?>>Garantía y devoluciones</option>
                                        <option value="mayorista" <?php echo (isset($_POST['subject']) && $_POST['subject'] === 'mayorista') ? 'selected' : ''; ?>>Ventas mayoristas</option>
                                        <option value="otro" <?php echo (isset($_POST['subject']) && $_POST['subject'] === 'otro') ? 'selected' : ''; ?>>Otro</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="message" class="form-label">Mensaje *</label>
                                <textarea 
                                    id="message" 
                                    name="message" 
                                    class="form-control"
                                    rows="6"
                                    required
                                    placeholder="Contanos en qué podemos ayudarte..."><?php echo isset($_POST['message']) ? e($_POST['message']) : ''; ?></textarea>
                            </div>
                            
                            <button type="submit" class="btn-primary">
                                Enviar Mensaje
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>