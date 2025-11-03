# Panel de Administración - Verificaciones de Email

Sistema completo para gestionar las verificaciones de email de usuarios en Pinche Supplies.

## 📋 Características

- ✅ **Estadísticas en tiempo real**: Total usuarios, verificados, no verificados, porcentaje
- ✅ **Lista de usuarios pendientes** con opciones de gestión
- ✅ **Reenvío de email manual** para usuarios no verificados
- ✅ **Marcar como verificado** manualmente
- ✅ **Búsqueda y filtros** avanzados
- ✅ **Diseño profesional tipo dashboard** responsive
- ✅ **Autenticación segura** para administradores
- ✅ **Logs de actividad** para auditoría
- ✅ **Notificaciones en tiempo real** con toast messages
- ✅ **Paginación automática** para grandes volúmenes de datos

## 🚀 Instalación

### 1. Subir Archivos

Sube los siguientes archivos a tu servidor en el directorio `admin/`:

```
admin/
├── admin-verificaciones.php    # Panel principal
├── config-admin.php            # Configuración
└── README.md                   # Este archivo
```

### 2. Configurar Base de Datos

Edita el archivo `config-admin.php` y ajusta estos valores:

```php
// CONFIGURACIÓN DE BASE DE DATOS
define('DB_HOST', 'localhost');
define('DB_NAME', 'tu_base_datos');     // ← CAMBIAR
define('DB_USER', 'tu_usuario');        // ← CAMBIAR
define('DB_PASS', 'tu_password');       // ← CAMBIAR
```

### 3. Configurar Administrador

En el mismo archivo `config-admin.php`:

```php
// CONFIGURACIÓN DE ADMINISTRADOR
define('ADMIN_EMAIL', 'admin@tudominio.com');  // ← Tu email
define('ADMIN_PASSWORD', 'tu_password_segura'); // ← Contraseña segura

// OPCIONAL: Hash de contraseña para mayor seguridad
define('ADMIN_PASSWORD_HASH', '$2y$10$...'); // ← Generar con password_hash()
```

### 4. Verificar Estructura de Base de Datos

Tu tabla `users` debe tener estos campos:

```sql
ALTER TABLE `users` 
ADD COLUMN `email_verified` TINYINT(1) DEFAULT 0,
ADD COLUMN `verification_token` VARCHAR(64) NULL,
ADD COLUMN `verification_expires` DATETIME NULL,
ADD COLUMN `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP;
```

### 5. Configurar URLs y Emails

```php
define('SITE_URL', 'https://tudominio.com');              // ← Tu dominio
define('EMAIL_FROM_ADDRESS', 'no-reply@tudominio.com');   // ← Tu email
define('EMAIL_ADMIN_ADDRESS', 'admin@tudominio.com');     // ← Tu email admin
```

## 🔧 Configuración Avanzada

### Generar Hash de Contraseña

Para mayor seguridad, puedes usar hashes en lugar de contraseñas en texto plano:

```php
// En PHP, ejecutar una vez para generar el hash:
echo password_hash('tu_password', PASSWORD_DEFAULT);

// Luego en config-admin.php:
define('ADMIN_PASSWORD_HASH', '$2y$10$hash_generado_aqui');
```

### Configurar Logs

Los logs de actividad se guardan automáticamente en:
```
admin/logs/admin_activity.log
```

### Ajustar Configuración

En `config-admin.php` puedes personalizar:

```php
// Tiempo de sesión (minutos)
define('SESSION_TIMEOUT', 60);

// Usuarios por página
define('USERS_PER_PAGE', 20);

// Auto-refresh del dashboard (segundos)
define('AUTO_REFRESH_SECONDS', 30);

// Tiempo de expiración del token (horas)
define('TOKEN_EXPIRY_HOURS', 24);
```

## 🎯 Uso del Panel

### 1. Acceder al Panel

Ve a: `https://tudominio.com/admin/admin-verificaciones.php`

### 2. Iniciar Sesión

Usa las credenciales configuradas en `config-admin.php`.

### 3. Dashboard Principal

El panel muestra:

- **Estadísticas**: Cards con métricas principales
- **Progreso de verificación**: Barra de progreso visual
- **Lista de usuarios**: Tabla con todos los usuarios registrados
- **Filtros**: Búsqueda por nombre/email y estado

### 4. Gestionar Verificaciones

**Para usuarios NO verificados:**

- **Reenviar Email**: Envía un nuevo email de verificación
- **Marcar Verificado**: Marca manualmente como verificado

**Para usuarios verificados:**

- Muestra estado "Completado" sin acciones adicionales

### 5. Búsqueda y Filtros

- **Búsqueda**: Introduce nombre o email para filtrar
- **Estado**: Filtra por "Verificados" o "No verificados"
- **Paginación**: Navega entre páginas de resultados

## 🔒 Seguridad

### Medidas Implementadas

- ✅ **Sesiones seguras** con timeout configurable
- ✅ **CSRF Protection** (opcional, se puede añadir)
- ✅ **Logs de actividad** para auditoría
- ✅ **Validación de entrada** para prevenir inyección SQL
- ✅ **Escape de HTML** para prevenir XSS
- ✅ **Manejo seguro de errores** sin exponer información sensible

### Recomendaciones de Seguridad

1. **Cambia las credenciales por defecto** inmediatamente
2. **Usa HTTPS** en producción
3. **Configura un firewall** para restringir acceso al directorio admin
4. **Monitorea los logs** regularmente
5. **Haz backups** regulares de la base de datos
6. **Actualiza PHP** y las librerías regularmente

## 📊 Estadísticas Disponibles

- **Total Usuarios**: Número total de usuarios registrados
- **Usuarios Verificados**: Cantidad de emails verificados
- **Usuarios No Verificados**: Pendientes de verificación
- **Tokens Activos**: Tokens de verificación no expirados
- **Porcentaje de Verificación**: Tasa de éxito general

## 🔧 Solución de Problemas

### Error de Conexión a BD

```
Error: No se puede conectar con la base de datos
```

**Solución**: Verifica los datos en `config-admin.php`:
- Host de base de datos
- Nombre de base de datos
- Usuario y contraseña

### Error de Permisos

Si ves errores de permisos al crear logs:

```bash
chmod 755 admin/
chmod 755 admin/logs/
chmod 644 admin/config-admin.php
```

### Emails No Se Envían

Verifica:
1. Que `sendmail` esté configurado en tu servidor
2. Que la función `mail()` esté habilitada
3. Que las URLs en el email sean correctas

### No Aparecen Usuarios

Verifica:
1. Que la tabla `users` tenga los campos necesarios
2. Que haya usuarios en la base de datos
3. Los nombres de campo coincidan con el código

## 📁 Estructura de Archivos

```
admin/
├── admin-verificaciones.php     # Panel principal (909 líneas)
├── config-admin.php             # Configuración (132 líneas)
├── README.md                    # Documentación
└── logs/                        # Directorio de logs (auto-creado)
    └── admin_activity.log       # Log de actividad
```

## 🎨 Personalización

### Cambiar Colores

Edita las variables CSS en `admin-verificaciones.php`:

```css
:root {
    --primary: #667eea;      # Color principal
    --success: #10b981;      # Color de éxito
    --warning: #f59e0b;      # Color de advertencia
    --danger: #ef4444;       # Color de peligro
    --info: #3b82f6;         # Color de información
}
```

### Modificar Campos Mostrados

Busca la sección de la tabla HTML y añade/quita columnas según necesites.

### Añadir Nuevas Funcionalidades

El código está estructurado de forma modular para facilitar ampliaciones.

## 📞 Soporte

Para soporte técnico:

1. **Revisa este README** completamente
2. **Verifica la configuración** en `config-admin.php`
3. **Revisa los logs** en `admin/logs/admin_activity.log`
4. **Testa la conexión** a la base de datos manualmente

## 🔄 Actualizaciones

### Versión 1.0 - Características Básicas
- Panel de administración completo
- Gestión de verificaciones
- Estadísticas en tiempo real
- Búsqueda y filtros
- Autenticación segura

### Futuras Versiones Planeadas
- Exportar datos a CSV
- Configuración de emails SMTP
- Notificaciones push
- Dashboard con gráficos
- Gestión de múltiples administradores

---

**¡Panel de Administración listo para usar!** 🚀

Configura los archivos, sube al servidor y comienza a gestionar las verificaciones de email de tus usuarios de forma profesional.