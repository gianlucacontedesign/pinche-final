<?php
session_start();

// Verificar si hay una orden reciente
if (!isset($_SESSION['last_order'])) {
    header('Location: index.php');
    exit;
}

$order = $_SESSION['last_order'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido Confirmado - Pinche Supplies</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .confirmation-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            max-width: 800px;
            width: 100%;
        }
        .success-header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            text-align: center;
            padding: 40px 20px;
        }
        .order-details {
            padding: 40px;
        }
        .order-item {
            border-bottom: 1px solid #eee;
            padding: 15px 0;
        }
        .order-item:last-child {
            border-bottom: none;
        }
        .btn-primary-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 50px;
            padding: 12px 30px;
            color: white;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }
        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
            color: white;
        }
        .order-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }
        .order-info h5 {
            color: #667eea;
            margin-bottom: 15px;
        }
        .order-info p {
            margin-bottom: 8px;
        }
        .total-amount {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            margin: 20px 0;
        }
        .total-amount h3 {
            margin: 0;
            font-size: 2rem;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="confirmation-card">
            <!-- Encabezado de éxito -->
            <div class="success-header">
                <i class="fas fa-check-circle fa-4x mb-3"></i>
                <h1 class="mb-2">¡Pedido Confirmado!</h1>
                <p class="mb-0">Tu pedido ha sido procesado exitosamente</p>
            </div>
            
            <!-- Detalles del pedido -->
            <div class="order-details">
                <div class="row">
                    <div class="col-md-6">
                        <div class="order-info">
                            <h5><i class="fas fa-user me-2"></i>Información del Cliente</h5>
                            <p><strong>Nombre:</strong> <?= htmlspecialchars($order['customer']['first_name'] . ' ' . $order['customer']['last_name']) ?></p>
                            <p><strong>Email:</strong> <?= htmlspecialchars($order['customer']['email']) ?></p>
                            <p><strong>Teléfono:</strong> <?= htmlspecialchars($order['customer']['phone']) ?></p>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="order-info">
                            <h5><i class="fas fa-truck me-2"></i>Dirección de Envío</h5>
                            <p><?= htmlspecialchars($order['shipping']['address']) ?></p>
                            <p><?= htmlspecialchars($order['shipping']['city']) ?>, <?= htmlspecialchars($order['shipping']['province']) ?></p>
                            <p><strong>CP:</strong> <?= htmlspecialchars($order['shipping']['postal_code']) ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="order-info">
                    <h5><i class="fas fa-shopping-bag me-2"></i>Productos del Pedido</h5>
                    <?php if (!empty($order['cart'])): ?>
                        <?php foreach ($order['cart'] as $item): ?>
                            <div class="order-item d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1"><?= htmlspecialchars($item['name']) ?></h6>
                                    <small class="text-muted">Cantidad: <?= $item['quantity'] ?> x $<?= number_format($item['price'], 2) ?></small>
                                </div>
                                <div class="text-end">
                                    <strong>$<?= number_format($item['price'] * $item['quantity'], 2) ?></strong>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">No se encontraron productos en el carrito</p>
                    <?php endif; ?>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="order-info">
                            <h5><i class="fas fa-credit-card me-2"></i>Método de Pago</h5>
                            <?php
                            $payment_methods = [
                                'card' => 'Tarjeta de Crédito/Débito',
                                'transfer' => 'Transferencia Bancaria',
                                'cash' => 'Efectivo'
                            ];
                            ?>
                            <p><strong>Método:</strong> <?= $payment_methods[$order['payment']['method']] ?? $order['payment']['method'] ?></p>
                            <?php if (!empty($order['payment']['notes'])): ?>
                                <p><strong>Notas:</strong> <?= htmlspecialchars($order['payment']['notes']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="order-info">
                            <h5><i class="fas fa-clock me-2"></i>Información del Pedido</h5>
                            <p><strong>Fecha:</strong> <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></p>
                            <p><strong>Estado:</strong> <span class="badge bg-success">Confirmado</span></p>
                        </div>
                    </div>
                </div>
                
                <!-- Total -->
                <div class="total-amount">
                    <h3>$<?= number_format($order['total'], 2) ?></h3>
                    <p class="mb-0">Total del Pedido</p>
                </div>
                
                <!-- Próximos pasos -->
                <div class="alert alert-info">
                    <h6><i class="fas fa-info-circle me-2"></i>Próximos Pasos</h6>
                    <ul class="mb-0">
                        <li>Recibirás un email de confirmación en tu correo electrónico</li>
                        <li>Nos pondremos en contacto contigo para coordinar el envío</li>
                        <li>El tiempo de preparación es de 24-48 horas hábiles</li>
                        <li>Para consultas puedes contactarnos por WhatsApp o email</li>
                    </ul>
                </div>
                
                <!-- Botones de acción -->
                <div class="text-center mt-4">
                    <a href="index.php" class="btn btn-primary-custom me-3">
                        <i class="fas fa-home me-2"></i>Volver al Inicio
                    </a>
                    <a href="cart.php" class="btn btn-outline-primary-custom">
                        <i class="fas fa-shopping-cart me-2"></i>Ver Carrito
                    </a>
                </div>
                
                <!-- Información de contacto -->
                <div class="text-center mt-4 p-3 bg-light rounded">
                    <h6 class="text-primary">¿Necesitas ayuda?</h6>
                    <p class="mb-2">Contáctanos:</p>
                    <div class="d-flex justify-content-center gap-3">
                        <a href="https://wa.me/541112345678" class="text-decoration-none">
                            <i class="fab fa-whatsapp text-success me-1"></i>WhatsApp
                        </a>
                        <a href="mailto:contacto@pinchesupplies.com" class="text-decoration-none">
                            <i class="fas fa-envelope text-primary me-1"></i>Email
                        </a>
                        <a href="tel:+541112345678" class="text-decoration-none">
                            <i class="fas fa-phone text-secondary me-1"></i>Llamar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Reproducir sonido de éxito (opcional)
        function playSuccessSound() {
            const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhAz+L0fPYeDMCGXfH8N2QQAoUXrTp66hVFApGn+DyvmwhAz+L0fPYeDMCGXfH8N2QQAoUXrTp66hVFApGn+DyvmwhAz+L0fPYeDMCGQ==');
            audio.play().catch(e => console.log('Audio play failed:', e));
        }
        
        // Reproducir sonido al cargar la página
        window.addEventListener('load', playSuccessSound);
    </script>
</body>
</html>