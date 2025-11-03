# 📧 GUÍA COMPLETA DE EMAILS - PINCHE SUPPLIES

## 🎯 SISTEMA DE EMAILS REALES INSTALADO

Tu tienda ahora puede enviar emails reales para:
- ✅ **Registro de usuarios** (emails de bienvenida)
- ✅ **Recuperación de contraseñas** (reseteo seguro)
- ✅ **Notificaciones de pedidos** (al administrador)
- ✅ **Confirmaciones de pedido** (a los clientes)
- ✅ **Emails de contacto** profesionales

---

## 🚀 PASOS DE INSTALACIÓN

### PASO 1: SUBIR ARCHIVOS DE EMAILS 📤

1. **Crea las carpetas** en tu `public_html/`:
   ```
   public_html/
   ├── email-config/
   │   ├── config-email.php
   │   ├── includes/
   │   │   └── email-sender.php
   │   ├── templates/
   │   │   ├── welcome.html
   │   │   ├── password-reset.html
   │   │   ├── new-order.html
   │   │   └── order-confirmation.html
   │   ├── logs/
   │   │   └── email.log
   │   └── test-emails.php
   └── integration/
       └── functions-update.php
   ```

2. **Sube todos los archivos** de la carpeta `email-config` a tu servidor

### PASO 2: CONFIGURAR SMTP 🔧

**Edita el archivo `public_html/email-config/config-email.php`:**

```php
// ACTUALIZA ESTAS LÍNEAS CON TUS DATOS REALES:
define('SMTP_USERNAME', 'noreply@pinchesupplies.com.ar'); // Tu email real
define('SMTP_PASSWORD', 'tu_password_real_aqui');         // Tu contraseña real
define('SMTP_FROM_EMAIL', 'noreply@pinchesupplies.com.ar');
define('ADMIN_EMAIL', 'admin@pinchesupplies.com.ar');     // Tu email de admin
define('CONTACT_EMAIL', 'info@pinchesupplies.com.ar');    // Email de contacto
define('SALES_EMAIL', 'ventas@pinchesupplies.com.ar');    // Email de ventas
```

### PASO 3: INTEGRAR CON TU SISTEMA EXISTENTE 🔗

**Añade al archivo `public_html/includes/functions.php`:**

```php
// COPIA TODO EL CONTENIDO DE: integration/functions-update.php
// Y PÉGALO AL FINAL DE TU functions.php EXISTENTE
```

### PASO 4: ACTUALIZAR ARCHIVOS DE TU TIENDA ✏️

#### A) Registro de usuarios (`register.php`)
Añadir después del registro exitoso:

```php
// Después de crear el usuario exitosamente
if ($userCreated) {
    // Enviar email de bienvenida
    sendWelcomeEmail($userEmail, $userName, $tempPassword);
}
```

#### B) Recuperación de contraseña (`forgot-password.php`)
Añadir después de generar token:

```php
// Después de generar token de reset
if ($tokenCreated) {
    sendPasswordResetEmail($userEmail, $userName, $token);
}
```

#### C) Nuevos pedidos (`cart.php` o donde proceses pedidos)
Añadir después de confirmar pedido:

```php
// Después de crear pedido exitosamente
$orderData = [
    'order_number' => $orderNumber,
    'customer_name' => $customerName,
    'customer_email' => $customerEmail,
    // ... más datos del pedido
];

// Enviar notificaciones
notifyAdminNewOrder($orderData);
sendOrderConfirmation($customerEmail, $orderData);
```

### PASO 5: PROBAR EL SISTEMA 🧪

1. **Sube** el archivo `test-emails.php` a `public_html/`
2. **Ve a:** `https://pinchesupplies.com.ar/test-emails.php`
3. **Ejecuta todas las pruebas** y verifica que funcionen

---

## 🎨 TEMPLATES DE EMAILS

### Templates disponibles:
- **welcome.html** - Email de bienvenida al registrarse
- **password-reset.html** - Recuperación de contraseña
- **new-order.html** - Notificación al admin de nuevo pedido
- **order-confirmation.html** - Confirmación al cliente

### Personalizar templates:
1. **Edita los archivos** en `email-config/templates/`
2. **Modifica colores, logos, textos** según tu marca
3. **Actualiza números de WhatsApp** en todos los templates

---

## ⚙️ CONFIGURACIÓN DE CREDENCIALES SMTP

### Para Donweb:
```php
define('SMTP_HOST', 'mail.pinchesupplies.com.ar');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'noreply@pinchesupplies.com.ar');
define('SMTP_PASSWORD', 'tu_password_real');
```

### Para Gmail (alternativo):
```php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'tu_email@gmail.com');
define('SMTP_PASSWORD', 'tu_password_app');
```

---

## 🔍 VERIFICACIÓN Y SOLUCIÓN DE PROBLEMAS

### Si los emails no llegan:

1. **Verificar spam**: Revisar carpeta de spam
2. **Configurar SMTP**: Asegurar credenciales correctas
3. **Verificar logs**: Revisar `email-config/logs/email.log`
4. **Probar manualmente**: Usar `test-emails.php`

### Logs disponibles:
- **email.log** - Registro de todos los emails enviados
- **Test de configuración** - Verificar SMTP

---

## 📱 PERSONALIZACIÓN ADICIONAL

### Cambiar números de WhatsApp:
Buscar y reemplazar `5491123456789` por tu número real en:
- Todos los archivos `.html` en `templates/`
- `functions.php` (función getContactInfo)

### Cambiar colores de emails:
Editar los CSS en cada template:
```css
background: #6b46c1; /* Color principal */
background: #059669; /* Color de éxito */
background: #dc2626; /* Color de alerta */
```

### Añadir logo a emails:
Subir tu logo a `assets/images/logo-email.png` y añadir en templates:
```html
<img src="{{siteUrl}}/assets/images/logo-email.png" alt="Pinche Supplies">
```

---

## ✅ CHECKLIST FINAL

- [ ] Archivos subidos a `email-config/`
- [ ] Credenciales SMTP configuradas
- [ ] Integración en `functions.php` completada
- [ ] Templates personalizados (colores, logo, WhatsApp)
- [ ] Pruebas ejecutadas exitosamente
- [ ] Números de WhatsApp actualizados
- [ ] Logs funcionando

---

## 🆘 SOPORTE

Si necesitas ayuda:
1. **Ejecuta** `test-emails.php` para diagnóstico
2. **Revisa** los logs en `email-config/logs/email.log`
3. **Verifica** configuración SMTP en `config-email.php`

¡Tu sistema de emails está listo para funcionar! 🚀
