# 🔧 Configuración del Panel de Administración

Este documento te guía paso a paso para configurar correctamente el panel de administración.

## 📁 Archivos del Panel

El panel está compuesto por los siguientes archivos:

```
admin/
├── admin-verificaciones.php    # Panel principal (909 líneas)
├── config-admin.php            # Configuración principal
├── install.php                 # Instalador y verificador
├── README.md                   # Documentación completa
└── logs/                       # Directorio de logs (auto-creado)
```

## 🚀 Configuración Paso a Paso

### Paso 1: Acceder al Instalador

Primero, sube todos los archivos a tu servidor y ve a:
```
https://tudominio.com/admin/install.php
```

El instalador verificará automáticamente:
- ✅ Versión de PHP
- ✅ Extensiones necesarias
- ✅ Permisos de archivos
- ✅ Configuración de base de datos
- ✅ Estructura de tablas

### Paso 2: Configurar Base de Datos

Edita el archivo `config-admin.php` y cambia estos valores:

```php
// ===== CONFIGURACIÓN DE BASE DE DATOS =====
define('DB_HOST', 'localhost');
define('DB_NAME', 'mi_tienda_db');           // ← Nombre de tu base de datos
define('DB_USER', 'mi_usuario_db');          // ← Tu usuario de MySQL
define('DB_PASS', 'mi_password_db');         // ← Tu contraseña de MySQL
```

**¿Dónde encuentro estos datos?**
- **Host**: Usually `localhost` o la IP de tu servidor
- **Base de datos**: El nombre que le diste al crear la BD en phpMyAdmin
- **Usuario**: Tu usuario de MySQL (no el de admin del panel)
- **Contraseña**: La contraseña de tu usuario MySQL

### Paso 3: Configurar Administrador

```php
// ===== CONFIGURACIÓN DE ADMINISTRADOR =====
define('ADMIN_EMAIL', 'admin@mitienda.com');              // ← Tu email
define('ADMIN_PASSWORD', 'MiPasswordSeguro123!');         // ← Contraseña segura
```

**⚠️ IMPORTANTE**: Cambia la contraseña por defecto (`admin123`) por una segura.

**Para máxima seguridad (opcional)**, puedes usar hash de contraseña:

```php
// Generar hash (ejecutar una vez en PHP):
echo password_hash('MiPasswordSeguro123!', PASSWORD_DEFAULT);

// En config-admin.php:
define('ADMIN_PASSWORD_HASH', '$2y$10$hash_generado_aqui');
```

### Paso 4: Configurar Sitio y Emails

```php
// ===== CONFIGURACIÓN DEL SITIO =====
define('SITE_NAME', 'Mi Tienda Online');
define('SITE_URL', 'https://mitienda.com');               // ← Tu dominio
define('ADMIN_PANEL_NAME', 'Panel Admin - Verificaciones');

// ===== CONFIGURACIÓN DE EMAIL =====
define('EMAIL_FROM_NAME', 'Mi Tienda');
define('EMAIL_FROM_ADDRESS', 'no-reply@mitienda.com');    // ← Tu email para envío
define('EMAIL_ADMIN_ADDRESS', 'admin@mitienda.com');      // ← Email para notificaciones
```

### Paso 5: Verificar y Probar

1. **Re-ejecuta el instalador**: `https://tudominio.com/admin/install.php`
2. **Si todo está verde**: Ve al panel: `https://tudominio.com/admin/admin-verificaciones.php`
3. **Inicia sesión** con tus credenciales
4. **Explora las funciones** del dashboard

## 🔍 Verificación Manual

### Verificar Base de Datos

Ejecuta esta consulta en phpMyAdmin para verificar la estructura:

```sql
-- Verificar campos en tabla users
DESCRIBE users;

-- Debe mostrar campos como:
-- id, name, email, password, email_verified, verification_token, verification_expires, created_at
```

### Verificar Configuración de Email

Testa el envío de emails añadiendo este código temporal en `config-admin.php`:

```php
// TEST TEMPORAL - Eliminar después de probar
if (isset($_GET['test_email'])) {
    $test_result = mail(EMAIL_ADMIN_ADDRESS, 'Test Panel Admin', 'Si recibes esto, el email funciona.');
    echo $test_result ? "Email enviado ✓" : "Error en email ✗";
    exit;
}
```

Luego ve a: `https://tudominio.com/admin/config-admin.php?test_email`

### Generar Hash de Contraseña

Para crear una contraseña segura con hash:

```php
<?php
// Crear este archivo temporal: generar_hash.php
$password = 'MiPasswordSegura123!';
$hash = password_hash($password, PASSWORD_DEFAULT);
echo "Contraseña: $password\n";
echo "Hash: $hash\n";
?>
```

Ejecuta el archivo y copia el hash a tu `config-admin.php`.

## 🛡️ Seguridad Recomendada

### 1. Cambiar Credenciales por Defecto

```php
// ❌ NO hagas esto:
define('ADMIN_EMAIL', 'admin@ejemplo.com');
define('ADMIN_PASSWORD', 'admin123');

// ✅ SÍ haz esto:
define('ADMIN_EMAIL', 'tu_email_real@dominio.com');
define('ADMIN_PASSWORD', 'PasswordSeguroConNumerosYSimbolos123!');
```

### 2. Usar HTTPS

Siempre usa HTTPS en producción:

```php
// En config-admin.php:
define('SITE_URL', 'https://mitienda.com');  // ← Con HTTPS
```

### 3. Proteger Directorio Admin (Opcional)

Crea un archivo `.htaccess` en `/admin/`:

```apache
# Proteger acceso con contraseña adicional
AuthType Basic
AuthName "Panel de Administración"
AuthUserFile /path/to/password/file
Require valid-user
```

### 4. Monitorear Logs

Los logs se guardan automáticamente en:
```
admin/logs/admin_activity.log
```

Revisa periódicamente para detectar accesos sospechosos.

## 📊 Estructura de Base de Datos

### Tabla `users` - Campos Requeridos

```sql
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text,
  `email_verified` tinyint(1) DEFAULT 0,           -- ← REQUERIDO
  `verification_token` varchar(64) DEFAULT NULL,   -- ← REQUERIDO  
  `verification_expires` datetime DEFAULT NULL,    -- ← REQUERIDO
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP, -- ← RECOMENDADO
  PRIMARY KEY (`id`),
  KEY `email` (`email`),
  KEY `verification_token` (`verification_token`)
);
```

### Si te Faltan Campos

Ejecuta en phpMyAdmin:

```sql
-- Añadir campos faltantes a la tabla users
ALTER TABLE `users` 
ADD COLUMN IF NOT EXISTS `email_verified` TINYINT(1) DEFAULT 0,
ADD COLUMN IF NOT EXISTS `verification_token` VARCHAR(64) NULL,
ADD COLUMN IF NOT EXISTS `verification_expires` DATETIME NULL,
ADD COLUMN IF NOT EXISTS `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP;

-- Añadir índices para mejor rendimiento
ALTER TABLE `users` 
ADD INDEX IF NOT EXISTS `idx_email_verified` (`email_verified`),
ADD INDEX IF NOT EXISTS `idx_verification_token` (`verification_token`);
```

## 🔧 Configuración Avanzada

### Personalizar Tiempo de Sesión

```php
// En config-admin.php - El usuario será desconectado después de X minutos sin actividad
define('SESSION_TIMEOUT', 60); // 60 minutos
```

### Cambiar Cantidad de Usuarios por Página

```php
// Mostrar más o menos usuarios por página
define('USERS_PER_PAGE', 20); // 20 usuarios por página
```

### Configurar Auto-Refresh

```php
// El dashboard se actualiza automáticamente cada X segundos
define('AUTO_REFRESH_SECONDS', 30); // 30 segundos
```

### Personalizar Tiempo de Expiración de Tokens

```php
// Los enlaces de verificación expiran después de X horas
define('TOKEN_EXPIRY_HOURS', 24); // 24 horas
```

## 📞 Solución de Problemas Comunes

### Error: "No se puede conectar con la base de datos"

**Causa**: Credenciales incorrectas en `config-admin.php`

**Solución**:
1. Verifica que los datos sean correctos
2. Asegúrate de que el usuario tenga permisos en la BD
3. Testa la conexión desde phpMyAdmin

### Error: "Tabla users no encontrada"

**Causa**: El nombre de la tabla es diferente o no existe

**Solución**:
1. Verifica el nombre exacto de la tabla
2. Si es diferente, edita el código en `admin-verificaciones.php`
3. Asegúrate de que la tabla tenga los campos requeridos

### No llegan los emails de verificación

**Causa**: Configuración de email del servidor

**Solución**:
1. Verifica que `sendmail` esté configurado
2. Testa con el código de verificación temporal
3. Considera usar SMTP para mayor confiabilidad

### Panel no carga correctamente

**Causa**: Errores de PHP o configuración

**Solución**:
1. Revisa los logs de error de PHP
2. Verifica permisos de archivos (755 para directorios, 644 para archivos)
3. Asegúrate de que todas las extensiones estén instaladas

## ✅ Checklist de Configuración

- [ ] PHP 7.4+ instalado
- [ ] Extensiones: pdo, pdo_mysql, mysqli habilitadas
- [ ] Base de datos configurada con credenciales correctas
- [ ] Tabla `users` existe con campos requeridos
- [ ] Email de administrador configurado (no por defecto)
- [ ] Contraseña de administrador cambiada (no por defecto)
- [ ] URL del sitio configurada correctamente
- [ ] Email de envío configurado
- [ ] Permisos de archivos correctos
- [ ] Panel de administración funcionando

**¡Listo!** 🎉 Tu panel de administración está configurado y listo para usar.

Para soporte adicional, revisa el archivo `README.md` completo.