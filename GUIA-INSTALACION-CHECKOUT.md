# Guía de Instalación y Configuración del Sistema de Checkout

## 📋 Descripción General

Este sistema de checkout permite a los clientes realizar pedidos en tu tienda online y guarda toda la información en una base de datos MySQL. Los pedidos se pueden gestionar desde el panel de administración.

## 🎯 Características Principales

- ✅ Checkout completo con validación de datos
- ✅ Guardado de pedidos en base de datos MySQL
- ✅ Panel de administración para gestionar pedidos
- ✅ Visualización de detalles de cada pedido
- ✅ Filtros y búsqueda de pedidos
- ✅ Estadísticas de pedidos
- ✅ Gestión de estados de pedidos
- ✅ Actualización automática de stock (opcional)

## 📦 Archivos Modificados y Creados

### Archivos Nuevos

1. **save-order-db.php** - Endpoint que guarda pedidos en la base de datos
2. **install-checkout.php** - Script de instalación y verificación
3. **test-checkout.php** - Script para probar el sistema
4. **config-local.php** - Configuración para entorno local
5. **ARQUITECTURA-CHECKOUT.md** - Documentación técnica
6. **GUIA-INSTALACION-CHECKOUT.md** - Esta guía

### Archivos Modificados

1. **checkout.php** - Modificado para usar `save-order-db.php` en lugar de `save-order.php`

## 🔧 Requisitos del Sistema

- PHP 7.4 o superior
- MySQL 5.7 o superior
- Extensiones PHP requeridas:
  - PDO
  - PDO_MySQL
  - JSON
  - cURL
  - Session

## 📥 Instalación Paso a Paso

### Paso 1: Configurar la Base de Datos

1. **Crear la base de datos** (si no existe):
   ```sql
   CREATE DATABASE pinche_supplies CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

2. **Importar las tablas**:
   - Ejecuta el archivo `database/database-completa.sql` en tu base de datos
   - Esto creará todas las tablas necesarias: `orders`, `order_items`, `products`, `categories`, etc.

   ```bash
   mysql -u tu_usuario -p pinche_supplies < database/database-completa.sql
   ```

### Paso 2: Configurar las Credenciales

1. **Editar `includes/config.php`**:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'pinche_supplies');
   define('DB_USER', 'tu_usuario');
   define('DB_PASS', 'tu_contraseña');
   define('DB_CHARSET', 'utf8mb4');
   
   define('SITE_URL', 'https://tudominio.com');
   ```

2. **Editar `admin/config-admin.php`** (si usas el panel de admin):
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'pinche_supplies');
   define('DB_USER', 'tu_usuario');
   define('DB_PASS', 'tu_contraseña');
   ```

### Paso 3: Verificar la Instalación

1. **Ejecutar el script de instalación**:
   - Abre en tu navegador: `https://tudominio.com/install-checkout.php`
   - Este script verificará:
     - Conexión a la base de datos
     - Existencia de las tablas necesarias
     - Estructura correcta de las tablas
     - Archivos del sistema
     - Permisos de escritura

2. **Verificar los resultados**:
   - Si todo está correcto, verás un mensaje de éxito ✅
   - Si hay errores, el script te indicará qué corregir ❌

### Paso 4: Probar el Sistema

1. **Ejecutar prueba automática**:
   - Abre en tu navegador: `https://tudominio.com/test-checkout.php`
   - Haz clic en "Enviar Pedido de Prueba"
   - Verifica que el pedido se guarde correctamente

2. **Verificar en el panel de admin**:
   - Accede a: `https://tudominio.com/admin/orders.php`
   - Deberías ver el pedido de prueba en la lista

### Paso 5: Configurar el Checkout en Producción

1. **Actualizar la URL en checkout.php** (si es necesario):
   - El archivo ya está configurado para usar `save-order-db.php`
   - Verifica que la URL sea correcta en la línea 9:
   ```php
   curl_setopt($ch, CURLOPT_URL, 'https://tudominio.com/save-order-db.php');
   ```

2. **Configurar el envío de emails** (opcional):
   - Edita las credenciales SMTP en `includes/config.php`
   - Descomentar el código de envío de emails en `save-order-db.php` si lo deseas

## 🎨 Flujo del Sistema

```
1. Cliente agrega productos al carrito
   ↓
2. Cliente va a checkout.php
   ↓
3. Cliente llena el formulario de datos
   ↓
4. checkout.php envía datos a save-order-db.php
   ↓
5. save-order-db.php valida los datos
   ↓
6. Se crea registro en tabla 'orders'
   ↓
7. Se crean registros en tabla 'order_items'
   ↓
8. Se actualiza el stock (opcional)
   ↓
9. Se limpia el carrito
   ↓
10. Se redirige a order-confirmation.php
   ↓
11. Admin puede ver el pedido en admin/orders.php
```

## 📊 Estructura de la Base de Datos

### Tabla: orders

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT | ID único del pedido |
| order_number | VARCHAR(50) | Número de orden único |
| customer_email | VARCHAR(255) | Email del cliente |
| customer_name | VARCHAR(255) | Nombre completo del cliente |
| customer_phone | VARCHAR(50) | Teléfono del cliente |
| subtotal | DECIMAL(10,2) | Subtotal sin envío |
| shipping_amount | DECIMAL(10,2) | Costo de envío |
| total_amount | DECIMAL(10,2) | Total final |
| payment_method | VARCHAR(100) | Método de pago |
| order_status | VARCHAR(50) | Estado del pedido |
| shipping_address | TEXT | Dirección de envío (JSON) |
| notes | TEXT | Notas del cliente |
| created_at | TIMESTAMP | Fecha de creación |

### Tabla: order_items

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT | ID único del item |
| order_id | INT | ID del pedido (FK) |
| product_id | INT | ID del producto |
| product_name | VARCHAR(255) | Nombre del producto |
| quantity | INT | Cantidad |
| price | DECIMAL(10,2) | Precio unitario |
| subtotal | DECIMAL(10,2) | Subtotal del item |

## 🔐 Seguridad

El sistema incluye las siguientes medidas de seguridad:

1. **Prepared Statements**: Todas las consultas SQL usan prepared statements para prevenir inyección SQL
2. **Validación de Datos**: Validación exhaustiva de todos los datos del formulario
3. **Transacciones**: Uso de transacciones SQL para garantizar integridad de datos
4. **Sanitización**: Limpieza de datos de entrada con `htmlspecialchars()`
5. **Logging**: Registro de errores en archivos de log
6. **HTTPS**: Se recomienda usar HTTPS en producción

## 🛠️ Personalización

### Calcular Envío Personalizado

Edita `save-order-db.php` líneas 73-77:

```php
// Calcular envío (puedes personalizar esta lógica)
$shippingAmount = 0;
if ($subtotal < 5000) {
    $shippingAmount = 800; // Costo de envío estándar
}
```

### Actualizar Stock Automáticamente

Descomenta las líneas 163-170 en `save-order-db.php`:

```php
if ($item['product_id'] > 0) {
    $db->execute(
        "UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?",
        [$item['quantity'], $item['product_id']]
    );
}
```

### Enviar Email de Confirmación

Agrega después de la línea 191 en `save-order-db.php`:

```php
// Enviar email de confirmación
$to = $data['customer']['email'];
$subject = 'Confirmación de Pedido #' . $orderData['order_number'];
$message = 'Tu pedido ha sido recibido correctamente...';
$headers = 'From: ' . ADMIN_EMAIL;
mail($to, $subject, $message, $headers);
```

## 📱 Panel de Administración

### Acceso al Panel

- URL: `https://tudominio.com/admin/orders.php`
- Credenciales: Configuradas en `admin/config-admin.php`

### Funcionalidades del Panel

1. **Lista de Pedidos**: Ver todos los pedidos con filtros
2. **Detalles de Pedido**: Ver información completa de cada pedido
3. **Cambiar Estados**: Actualizar el estado de los pedidos
4. **Búsqueda**: Buscar pedidos por número, cliente o email
5. **Filtros**: Filtrar por estado, fecha, etc.
6. **Estadísticas**: Ver métricas de pedidos

## 🐛 Solución de Problemas

### Error: "No se recibieron datos del pedido"

**Causa**: El servidor no está recibiendo los datos JSON correctamente.

**Solución**:
1. Verifica que `php://input` esté habilitado en tu servidor
2. Verifica que el Content-Type sea `application/json`
3. Revisa los logs de PHP para más detalles

### Error: "Error de conexión a la base de datos"

**Causa**: Credenciales incorrectas o base de datos no existe.

**Solución**:
1. Verifica las credenciales en `includes/config.php`
2. Asegúrate de que la base de datos existe
3. Verifica que el usuario tenga permisos correctos

### Los pedidos no aparecen en el admin

**Causa**: El admin está leyendo de una base de datos diferente.

**Solución**:
1. Verifica que `admin/config-admin.php` tenga las mismas credenciales que `includes/config.php`
2. Asegúrate de que ambos archivos apunten a la misma base de datos

### Error: "Call to undefined function"

**Causa**: Falta cargar alguna clase o archivo de configuración.

**Solución**:
1. Verifica que todos los `require_once` estén correctos
2. Asegúrate de que los archivos de clases existan en `includes/`

## 📝 Mantenimiento

### Logs

Los logs se guardan en:
- `logs/errores.log` - Errores del sistema
- `admin/logs/admin_activity.log` - Actividad del admin

### Backup

Realiza backups regulares de:
1. Base de datos: `mysqldump -u usuario -p pinche_supplies > backup.sql`
2. Archivos del sitio
3. Configuraciones

### Actualización

Para actualizar el sistema:
1. Realiza un backup completo
2. Sube los archivos nuevos
3. Ejecuta `install-checkout.php` para verificar
4. Prueba con `test-checkout.php`

## 📞 Soporte

Si tienes problemas:

1. Revisa los logs de errores
2. Ejecuta `install-checkout.php` para diagnóstico
3. Verifica la configuración de la base de datos
4. Asegúrate de que todos los archivos estén en su lugar

## 🎉 ¡Listo!

Tu sistema de checkout está configurado y listo para recibir pedidos. Los clientes pueden realizar compras y tú puedes gestionarlas desde el panel de administración.

## 📄 Archivos de Referencia

- `ARQUITECTURA-CHECKOUT.md` - Documentación técnica detallada
- `database/database-completa.sql` - Estructura completa de la base de datos
- `install-checkout.php` - Script de instalación y verificación
- `test-checkout.php` - Script de prueba del sistema
