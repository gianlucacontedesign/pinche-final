# Arquitectura del Sistema de Checkout - Pinche Supplies

## Análisis del Sistema Actual

### Problemas Identificados

1. **Desconexión entre Checkout y Admin**
   - El checkout guarda pedidos en `orders.json` (archivo plano)
   - El panel de administración lee pedidos desde la base de datos MySQL
   - Los pedidos no aparecen en el admin porque están en sistemas diferentes

2. **Estructura de Base de Datos Existente**
   - Tablas `orders` y `order_items` ya definidas en `database-completa.sql`
   - Clase `Order` en `includes/class.order.php` con métodos para crear pedidos
   - Panel admin en `admin/orders.php` configurado para leer de la base de datos

3. **Flujo Actual del Checkout**
   ```
   checkout.php → save-order.php → orders.json
   ```

## Solución Propuesta

### Arquitectura Objetivo

```
checkout.php → save-order-db.php → MySQL Database → admin/orders.php
```

### Componentes a Modificar

1. **save-order-db.php** (nuevo archivo)
   - Reemplaza `save-order.php`
   - Guarda pedidos directamente en la base de datos
   - Utiliza la clase `Order` existente
   - Maneja transacciones para garantizar integridad

2. **checkout.php** (modificación)
   - Cambiar la URL del endpoint de `save-order.php` a `save-order-db.php`
   - Mantener la misma estructura de datos
   - Mejorar validaciones

3. **Configuración de Base de Datos**
   - Verificar que `config.php` tenga las credenciales correctas
   - Asegurar que las tablas estén creadas
   - Verificar que la clase `Database` funcione correctamente

### Estructura de Datos

#### Tabla `orders`
```sql
- id (PK)
- order_number (único)
- customer_id (FK a customers, nullable)
- customer_email
- customer_name
- customer_phone
- subtotal
- tax_amount
- shipping_amount
- discount_amount
- total_amount
- payment_method
- order_status (pending, processing, shipped, delivered, cancelled)
- shipping_address
- billing_address
- notes
- created_at
- updated_at
```

#### Tabla `order_items`
```sql
- id (PK)
- order_id (FK a orders)
- product_id (FK a products)
- product_name
- quantity
- price
- subtotal
- created_at
```

### Flujo de Datos

1. **Usuario completa el checkout**
   - Llena formulario con datos personales y de envío
   - Selecciona método de pago
   - Confirma el pedido

2. **Procesamiento del pedido**
   - `checkout.php` valida los datos
   - Envía datos a `save-order-db.php` vía POST/JSON
   - `save-order-db.php` inicia transacción
   - Crea registro en tabla `orders`
   - Crea registros en tabla `order_items` para cada producto
   - Actualiza stock de productos
   - Confirma transacción
   - Limpia el carrito de sesión

3. **Confirmación**
   - Redirige a `order-confirmation.php`
   - Muestra detalles del pedido
   - Envía email de confirmación (opcional)

4. **Visualización en Admin**
   - `admin/orders.php` lista todos los pedidos
   - `admin/order-details.php` muestra detalles de cada pedido
   - Permite cambiar estados de pedidos
   - Genera reportes y estadísticas

### Mapeo de Datos del Checkout a la Base de Datos

```php
// Datos del formulario → Tabla orders
[
    'order_number' => generate_order_number(),
    'customer_id' => NULL, // Para clientes invitados
    'customer_email' => $_POST['email'],
    'customer_name' => $_POST['first_name'] . ' ' . $_POST['last_name'],
    'customer_phone' => $_POST['phone'],
    'subtotal' => $cart_total,
    'tax_amount' => 0, // Calcular si es necesario
    'shipping_amount' => 0, // Calcular según método
    'discount_amount' => 0,
    'total_amount' => $cart_total,
    'payment_method' => $_POST['payment_method'],
    'order_status' => 'pending',
    'payment_status' => 'pending',
    'shipping_address' => json_encode([
        'address' => $_POST['address'],
        'city' => $_POST['city'],
        'province' => $_POST['province'],
        'postal_code' => $_POST['postal_code']
    ]),
    'notes' => $_POST['payment_notes'] ?? ''
]

// Items del carrito → Tabla order_items
foreach ($cart_items as $item) {
    [
        'order_id' => $order_id,
        'product_id' => $item['id'],
        'product_name' => $item['name'],
        'quantity' => $item['quantity'],
        'price' => $item['price'],
        'subtotal' => $item['price'] * $item['quantity']
    ]
}
```

## Ventajas de la Solución

1. **Integridad de Datos**: Uso de transacciones SQL para garantizar consistencia
2. **Escalabilidad**: Base de datos relacional permite consultas complejas y reportes
3. **Seguridad**: Validaciones en múltiples niveles y protección contra inyección SQL
4. **Mantenibilidad**: Código organizado en clases y funciones reutilizables
5. **Funcionalidad Completa**: El admin puede gestionar estados, buscar, filtrar y generar reportes

## Plan de Implementación

### Fase 1: Preparación
- [x] Analizar estructura actual
- [ ] Verificar base de datos y tablas
- [ ] Probar conexión a base de datos

### Fase 2: Desarrollo
- [ ] Crear `save-order-db.php`
- [ ] Modificar `checkout.php`
- [ ] Agregar validaciones adicionales
- [ ] Implementar manejo de errores

### Fase 3: Pruebas
- [ ] Probar flujo completo de checkout
- [ ] Verificar que los pedidos aparezcan en el admin
- [ ] Probar actualización de stock
- [ ] Validar manejo de errores

### Fase 4: Documentación
- [ ] Crear guía de uso
- [ ] Documentar configuración necesaria
- [ ] Preparar instrucciones de instalación
