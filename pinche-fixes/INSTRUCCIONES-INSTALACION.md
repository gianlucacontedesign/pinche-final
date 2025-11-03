# 📦 INSTALACIÓN - Archivos Críticos Faltantes

## ✅ ARCHIVOS INCLUIDOS EN ESTE PAQUETE

Este ZIP contiene todos los archivos que faltaban en tu sistema:

```
pinche-fixes/
├── classes/
│   ├── Database.php      ✅ Conexión a base de datos (PDO)
│   ├── Category.php      ✅ Modelo de categorías
│   ├── Product.php       ✅ Modelo de productos
│   └── Cart.php          ✅ Carrito de compras
├── config/
│   └── database.php      ✅ Inicialización de BD
├── includes/
│   └── functions.php     ✅ Funciones auxiliares
└── INSTRUCCIONES-INSTALACION.md (este archivo)
```

---

## 🚀 INSTALACIÓN - 3 PASOS (5 MINUTOS)

### **PASO 1: Subir Archivos**

1. **Descarga** y **descomprime** `pinche-archivos-faltantes.zip`
2. **Accede** a tu cPanel → File Manager
3. **Navega** a `public_html/`
4. **Sube** las carpetas:
   - `classes/` (los 4 archivos PHP)
   - `config/database.php` (dentro de la carpeta config existente)
   - `includes/functions.php` (dentro de la carpeta includes existente)

**IMPORTANTE:** NO reemplaces archivos existentes, solo agrega los nuevos.

---

### **PASO 2: Modificar config.php**

Abre `public_html/config/config.php` y **agrega al FINAL** (antes del `?>`):

```php
// Cargar funciones auxiliares
require_once __DIR__ . '/../includes/functions.php';

// Cargar Database
require_once __DIR__ . '/../classes/Database.php';

// Cargar modelos
require_once __DIR__ . '/../classes/Category.php';
require_once __DIR__ . '/../classes/Product.php';
require_once __DIR__ . '/../classes/Cart.php';
```

**Archivo completo debe quedar así:**

```php
<?php
// Configuración de Base de Datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'a0030995_pinche');
define('DB_USER', 'a0030995_pinche');
define('DB_PASSWORD', 'vawuDU97zu');

// Configuración del Sitio
define('SITE_NAME', 'Pinche Supplies');
define('SITE_URL', 'https://pinchesupplies.com.ar');
define('ASSETS_URL', SITE_URL . '/assets');

// Configuración de Errores
error_reporting(E_ALL);
ini_set('display_errors', 0); // Cambiar a 1 para debug
ini_set('log_errors', 1);

// Zona horaria
date_default_timezone_set('America/Argentina/Buenos_Aires');

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cargar funciones auxiliares
require_once __DIR__ . '/../includes/functions.php';

// Cargar Database
require_once __DIR__ . '/../classes/Database.php';

// Cargar modelos
require_once __DIR__ . '/../classes/Category.php';
require_once __DIR__ . '/../classes/Product.php';
require_once __DIR__ . '/../classes/Cart.php';
?>
```

---

### **PASO 3: Verificar que Funciona**

1. **Visita** tu sitio: `https://pinchesupplies.com.ar`
2. **Deberías ver:**
   - ✅ Página cargando correctamente (sin blanco)
   - ✅ Header y footer visibles
   - ✅ Estructura del sitio

**Si aún aparece en blanco:**
- Verifica que subiste TODOS los archivos
- Verifica que modificaste `config/config.php` correctamente
- Ejecuta nuevamente `diagnostico-index.php` para ver si hay otros errores

---

## 🔍 VERIFICACIÓN RÁPIDA

Después de subir los archivos, verifica que existan en estas rutas:

```
✅ public_html/classes/Database.php
✅ public_html/classes/Category.php
✅ public_html/classes/Product.php
✅ public_html/classes/Cart.php
✅ public_html/config/database.php
✅ public_html/includes/functions.php
```

Usa cPanel File Manager para confirmar.

---

## 📊 ¿QUÉ HACEN ESTOS ARCHIVOS?

### **Database.php**
- Establece conexión PDO a MySQL
- Patrón Singleton (una sola instancia)
- Manejo de errores de conexión

### **Category.php**
- Obtener categorías (todas, principales, por slug)
- Contar productos por categoría
- Gestionar jerarquía de categorías

### **Product.php**
- Obtener productos (con filtros: destacados, nuevos, por categoría)
- Búsqueda de productos
- Gestión de stock
- Productos relacionados

### **Cart.php**
- Agregar/remover productos del carrito
- Actualizar cantidades
- Calcular totales
- Validar stock disponible
- Usa sesiones PHP

### **functions.php**
- `e()` - Escapar HTML (seguridad)
- `formatPrice()` - Formatear precios
- `getSetting()` - Obtener configuraciones
- `redirect()` - Redireccionar con mensajes
- Y 15+ funciones auxiliares más

---

## ⚠️ IMPORTANTE - TABLAS DE BASE DE DATOS

Estos archivos asumen que existen las siguientes tablas en tu BD:

- ✅ `categories` (id, name, slug, description, image, parent_id, active, display_order)
- ✅ `products` (id, name, slug, description, price, image, category_id, featured, is_new, stock, active, created_at)
- ⚠️ `users` (para login/registro)
- ⚠️ `orders` (para pedidos)
- ⚠️ `settings` (para configuraciones)

**Si no existen estas tablas:**

1. Ve a cPanel → phpMyAdmin
2. Selecciona tu base de datos `a0030995_pinche`
3. Verifica qué tablas existen
4. Si faltan, necesitarás crear las tablas o importar un SQL

**Si necesitas ayuda para crear las tablas, avísame y te genero el SQL completo.**

---

## 🆘 SOLUCIÓN DE PROBLEMAS

### **Aún aparece página en blanco**

1. Habilita errores temporalmente en `config/config.php`:
   ```php
   ini_set('display_errors', 1);
   ```

2. Recarga la página y verás el error exacto

3. Cópiame el error completo

### **Error: "Table 'categories' doesn't exist"**

Necesitas crear las tablas de la base de datos. Avísame y te paso el SQL.

### **Error: "Class 'Database' not found"**

Verifica que agregaste las líneas `require_once` en `config/config.php` correctamente.

### **Error: "Call to undefined function e()"**

Verifica que `includes/functions.php` esté cargado en `config/config.php`.

---

## ✅ CHECKLIST DE INSTALACIÓN

- [ ] Descargué y descomprimí el ZIP
- [ ] Subí la carpeta `classes/` completa
- [ ] Subí `config/database.php`
- [ ] Subí `includes/functions.php`
- [ ] Modifiqué `config/config.php` agregando los `require_once`
- [ ] Visité el sitio y funciona correctamente

---

## 📞 SOPORTE

Si después de seguir estos pasos el sitio aún no funciona:

1. Ejecuta nuevamente: `https://pinchesupplies.com.ar/diagnostico-index.php`
2. Copia TODO el resultado del diagnóstico
3. Envíame el resultado completo
4. Te daré la siguiente solución

---

**¡Con estos archivos tu index.php debería funcionar perfectamente!** 🎉

Si tienes algún problema durante la instalación, avísame y te ayudo paso a paso.
