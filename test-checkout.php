<?php
/**
 * Script de Prueba del Sistema de Checkout
 * Este archivo permite probar el flujo completo sin necesidad de llenar el formulario
 */

session_start();

// Simular productos en el carrito
$_SESSION['cart'] = [
    [
        'id' => 1,
        'name' => 'Producto de Prueba 1',
        'price' => 1500.00,
        'quantity' => 2,
        'image' => 'https://via.placeholder.com/100'
    ],
    [
        'id' => 2,
        'name' => 'Producto de Prueba 2',
        'price' => 2500.00,
        'quantity' => 1,
        'image' => 'https://via.placeholder.com/100'
    ]
];

// Datos de prueba del pedido
$testOrderData = [
    'customer' => [
        'first_name' => 'Juan',
        'last_name' => 'Pérez',
        'email' => 'juan.perez@example.com',
        'phone' => '+54 11 1234-5678'
    ],
    'shipping' => [
        'address' => 'Av. Corrientes 1234',
        'city' => 'Buenos Aires',
        'province' => 'CABA',
        'postal_code' => '1043'
    ],
    'payment' => [
        'method' => 'cash',
        'notes' => 'Pedido de prueba del sistema'
    ],
    'cart' => $_SESSION['cart'],
    'total' => 5500.00,
    'order_date' => date('Y-m-d H:i:s')
];

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prueba de Checkout - Pinche Supplies</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }
        .test-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 900px;
            margin: 0 auto;
            padding: 40px;
        }
        .result-box {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
            display: none;
        }
        .result-box.success {
            background: #d1fae5;
            border: 2px solid #10b981;
        }
        .result-box.error {
            background: #fee2e2;
            border: 2px solid #ef4444;
        }
        pre {
            background: white;
            padding: 15px;
            border-radius: 5px;
            max-height: 400px;
            overflow-y: auto;
        }
        .btn-test {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 15px 40px;
            border-radius: 50px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .btn-test:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
        }
    </style>
</head>
<body>
    <div class="test-card">
        <h1 class="mb-4">🧪 Prueba del Sistema de Checkout</h1>
        <p class="text-muted mb-4">Este script permite probar el flujo completo de guardado de pedidos en la base de datos.</p>
        
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Datos del Pedido de Prueba</h5>
            </div>
            <div class="card-body">
                <pre><?php echo json_encode($testOrderData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); ?></pre>
            </div>
        </div>
        
        <div class="text-center mb-4">
            <button id="btnTest" class="btn btn-test">
                Enviar Pedido de Prueba
            </button>
        </div>
        
        <div id="resultBox" class="result-box">
            <h5 id="resultTitle"></h5>
            <pre id="resultContent"></pre>
            <div id="resultActions" style="margin-top: 15px;"></div>
        </div>
        
        <div class="mt-4">
            <h5>Instrucciones</h5>
            <ol>
                <li>Haz clic en "Enviar Pedido de Prueba" para crear un pedido de prueba</li>
                <li>El sistema enviará los datos a <code>save-order-db.php</code></li>
                <li>Si todo funciona correctamente, verás un mensaje de éxito</li>
                <li>Luego podrás verificar el pedido en el panel de administración</li>
            </ol>
        </div>
    </div>
    
    <script>
        document.getElementById('btnTest').addEventListener('click', function() {
            const btn = this;
            btn.disabled = true;
            btn.textContent = 'Enviando...';
            
            const orderData = <?php echo json_encode($testOrderData); ?>;
            
            fetch('save-order-db.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(orderData)
            })
            .then(response => response.json())
            .then(data => {
                const resultBox = document.getElementById('resultBox');
                const resultTitle = document.getElementById('resultTitle');
                const resultContent = document.getElementById('resultContent');
                const resultActions = document.getElementById('resultActions');
                
                resultBox.style.display = 'block';
                
                if (data.success) {
                    resultBox.className = 'result-box success';
                    resultTitle.textContent = '✅ Pedido guardado exitosamente';
                    resultContent.textContent = JSON.stringify(data, null, 2);
                    
                    resultActions.innerHTML = `
                        <a href="admin/orders.php" class="btn btn-primary" target="_blank">
                            Ver en Panel de Admin
                        </a>
                        <a href="order-confirmation.php" class="btn btn-secondary ms-2" target="_blank">
                            Ver Confirmación
                        </a>
                    `;
                } else {
                    resultBox.className = 'result-box error';
                    resultTitle.textContent = '❌ Error al guardar el pedido';
                    resultContent.textContent = JSON.stringify(data, null, 2);
                    
                    resultActions.innerHTML = `
                        <button class="btn btn-warning" onclick="location.reload()">
                            Intentar de nuevo
                        </button>
                    `;
                }
                
                btn.disabled = false;
                btn.textContent = 'Enviar Otro Pedido de Prueba';
            })
            .catch(error => {
                const resultBox = document.getElementById('resultBox');
                const resultTitle = document.getElementById('resultTitle');
                const resultContent = document.getElementById('resultContent');
                
                resultBox.style.display = 'block';
                resultBox.className = 'result-box error';
                resultTitle.textContent = '❌ Error de conexión';
                resultContent.textContent = 'Error: ' + error.message;
                
                btn.disabled = false;
                btn.textContent = 'Enviar Pedido de Prueba';
            });
        });
    </script>
</body>
</html>
