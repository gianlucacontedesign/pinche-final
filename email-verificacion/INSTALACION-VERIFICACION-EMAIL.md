# 📧 SISTEMA DE VERIFICACIÓN POR EMAIL - INSTALACIÓN COMPLETA

## 🎯 DESCRIPCIÓN DEL SISTEMA

Este sistema agrega verificación por email a tu tienda Pinche Supplies, asegurando que todos los usuarios verifiquen su dirección de email antes de poder iniciar sesión y realizar compras.

### ✅ CARACTERÍSTICAS PRINCIPALES

- 🔒 **Verificación Obligatoria**: Los usuarios deben verificar su email antes del primer login
- 📧 **Email de Verificación**: Envío automático de enlaces de verificación personalizados
- 🔑 **Tokens Seguros**: Tokens únicos que expiran en 24 horas
- 🔄 **Reenvío de Emails**: Opción para reenviar enlaces de verificación
- 🧹 **Limpieza Automática**: Eliminación automática de tokens expirados
- 📊 **Estadísticas**: Panel de administración para monitorear verificaciones

---

## 📁 ARCHIVOS DEL SISTEMA

```
email-verificacion/
├── database-update.sql              # Script de actualización de BD
├── verificar-email.php              # Página de verificación
├── reenviar-verificacion.php        # Reenvío de emails
├── limpiar-tokens.php               # Limpieza automática
├── login-actualizado.php            # Login con verificación
├── includes/
│   ├── email-sender.php             # Sistema de emails actualizado
│   └── funciones-registro-actualizado.php # Funciones de registro
└── templates/
    └── email-verification.html      # Template de verificación
```

---

## 🚀 PASOS DE INSTALACIÓN

### PASO 1: ACTUALIZAR BASE DE DATOS

1. **Acceder a phpMyAdmin** en tu panel de Donweb
2. **Seleccionar base de datos**: `a0030995_pinche`
3. **Ejecutar SQL**: Ir a la pestaña "SQL" y pegar el contenido de `database-update.sql`
4. **Ejecutar**: Hacer clic en "Continuar"

**Resultado esperado**: La tabla `users` tendrá nuevos campos:
- `email_verified` (0/1)
- `verification_token` (string)
- `verification_expires` (datetime)
- `created_at` (timestamp)

### PASO 2: SUBIR ARCHIVOS AL SERVIDOR

1. **Subir todos los archivos** a la carpeta `public_html` de tu servidor
2. **Organizar en carpetas** según la estructura mostrada arriba
3. **Verificar permisos**:
   - Archivos PHP: 644
   - Carpetas: 755
   - Archivos de configuración: 600

### PASO 3: ACTUALIZAR CONFIGURACIÓN DE EMAILS

**Editar `includes/config-email.php`**:

```php
<?php
// Configuración SMTP para Donweb
define('SMTP_HOST', 'mail.pinchesupplies.com.ar');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'tu-email@pinchesupplies.com.ar'); // ← CAMBIAR
define('SMTP_PASSWORD', 'tu-password-email'); // ← CAMBIAR
define('ADMIN_EMAIL', 'admin@pinchesupplies.com.ar'); // ← CAMBIAR
define('CONTACT_EMAIL', 'info@pinchesupplies.com.ar'); // ← CAMBIAR
?>
```

**⚠️ IMPORTANTE**: Reemplazar con tus credenciales reales de email

### PASO 4: INTEGRAR FUNCIONES EN TU TIENDA

**Editar `includes/functions.php`** y agregar al final:

```php
// ===== VERIFICACIÓN POR EMAIL - AGREGAR AL FINAL =====
require_once __DIR__ . '/funciones-registro-actualizado.php';

// Función de conveniencia para registro con verificación
function registrarUsuario($name, $email, $password, $phone = '', $address = '') {
    return registrarUsuarioConVerificacion($name, $email, $password, $phone, $address);
}

// Función de conveniencia para login con verificación
function loginUsuario($email, $password) {
    return verificarLogin($email, $password);
}
```

### PASO 5: ACTUALIZAR PROCESO DE REGISTRO

**En tu archivo `registro.php` (o similar)**, cambiar la función de registro:

```php
// ANTES (código original)
// $result = registrarUsuario($name, $email, $password, $phone, $address);

// DESPUÉS (nuevo código)
$result = registrarUsuarioConVerificacion($name, $email, $password, $phone, $address);

if ($result['success']) {
    // Redirigir a página de verificación con mensaje
    header('Location: verificar-email.php?email=' . urlencode($email) . '&msg=' . urlencode($result['message']) . '&type=success');
    exit;
}
```

### PASO 6: CONFIGURAR LIMPIEZA AUTOMÁTICA (OPCIONAL)

**Crear archivo `cron.php`** en la raíz:

```php
<?php
require_once 'limpiar-tokens.php';
?>
```

**Configurar cronjob** (en panel Donweb):
- **Frecuencia**: Diaria
- **Hora**: 2:00 AM
- **Comando**: `php /ruta/a/tu/sitio/cron.php`

---

## ⚙️ CONFIGURACIÓN ADICIONAL

### PERSONALIZAR WHATSAPP Y EMAILS

**Editar archivos de template** para cambiar:
- Número de WhatsApp: Buscar `5491123456789` y reemplazar
- Emails de contacto: Reemplazar `admin@pinchesupplies.com.ar` e `info@pinchesupplies.com.ar`

### PERSONALIZAR MENSAJES

**En `verificar-email.php`** puedes modificar:
- Textos de éxito y error
- Colores y estilos
- Botones y enlaces

### CONFIGURAR DOMINIO

**Cambiar todas las referencias** de `pinchesupplies.com.ar` por tu dominio real si es diferente.

---

## 🧪 PRUEBAS DEL SISTEMA

### PRUEBA 1: REGISTRO CON VERIFICACIÓN

1. **Ir a tu página de registro**
2. **Llenar formulario** con datos válidos
3. **Verificar** que se muestre mensaje de "revisa tu email"
4. **Revisar bandeja de entrada** del email registrado
5. **Hacer clic** en el enlace de verificación
6. **Verificar** redirección a página de éxito

### PRUEBA 2: LOGIN CON EMAIL NO VERIFICADO

1. **Intentar login** con credenciales recién registradas
2. **Verificar** que aparezca mensaje "debes verificar tu email"
3. **Probar** botón de reenvío de verificación

### PRUEBA 3: LOGIN CON EMAIL VERIFICADO

1. **Hacer clic** en enlace de verificación recibido
2. **Intentar login** con las mismas credenciales
3. **Verificar** acceso exitoso al dashboard

### PRUEBA 4: REENVÍO DE VERIFICACIÓN

1. **Usar formulario** de reenvío desde página de login
2. **Verificar** que se reciba nuevo email
3. **Probar** nuevo enlace de verificación

---

## 📊 PANEL DE ESTADÍSTICAS (ADMIN)

Agregar a tu panel de administración:

```php
<?php
// Mostrar estadísticas de verificación
require_once 'includes/funciones-registro-actualizado.php';
$stats = obtenerEstadisticasVerificacion();

echo "<h3>📧 Estadísticas de Verificación</h3>";
echo "<p>Total usuarios: " . $stats['total_usuarios'] . "</p>";
echo "<p>Verificados: " . $stats['usuarios_verificados'] . "</p>";
echo "<p>No verificados: " . $stats['usuarios_no_verificados'] . "</p>";
echo "<p>Tokens activos: " . $stats['tokens_activos'] . "</p>";
echo "<p>Porcentaje: " . $stats['porcentaje_verificacion'] . "%</p>";
?>
```

---

## 🔧 SOLUCIÓN DE PROBLEMAS

### PROBLEMA: No se envían emails

**Soluciones**:
1. ✅ Verificar credenciales SMTP en `config-email.php`
2. ✅ Comprobar que el email existe en Donweb
3. ✅ Revisar carpeta de spam
4. ✅ Verificar logs de error en servidor

### PROBLEMA: Token expira muy rápido

**Solución**: Cambiar en `funciones-registro-actualizado.php`:
```php
// Línea ~95: Cambiar de 24 horas a 48 horas
$expiresAt = date('Y-m-d H:i:s', strtotime('+48 hours'));
```

### PROBLEMA: Usuario no puede hacer login después de verificar

**Verificar**:
1. ✅ Campo `email_verified = 1` en base de datos
2. ✅ Campo `active = 1` en base de datos
3. ✅ Función `verificarLogin()` funcionando correctamente

### PROBLEMA: Error de base de datos

**Soluciones**:
1. ✅ Verificar conexión en `config.php`
2. ✅ Ejecutar `database-update.sql` correctamente
3. ✅ Revisar permisos de base de datos

---

## 📞 SOPORTE

Si necesitas ayuda adicional:

1. **Revisar logs**: Buscar errores en `logs/` del servidor
2. **Verificar configuración**: Comprobar todos los archivos de configuración
3. **Probar paso a paso**: Seguir orden de instalación exactamente
4. **Contactar soporte**: [tu-email@pinchesupplies.com.ar]

---

## ✅ CHECKLIST DE INSTALACIÓN

- [ ] Base de datos actualizada con nuevos campos
- [ ] Todos los archivos subidos al servidor
- [ ] Configuración de email SMTP actualizada
- [ ] Funciones integradas en `functions.php`
- [ ] Página de registro actualizada
- [ ] Login actualizado funcionando
- [ ] Templates de email configurados
- [ ] Limpieza automática configurada (opcional)
- [ ] Pruebas completas realizadas
- [ ] Estadísticas funcionando en admin

**¡Sistema de verificación por email listo para usar!** 🚀✨