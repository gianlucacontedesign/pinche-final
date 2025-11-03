# 🚀 GUÍA ESPECÍFICA DONWEB - SISTEMA EMAIL

## 🎯 ARCHIVOS ADAPTADOS PARA DONWEB

He creado archivos **100% compatibles con DonWeb** que evitan errores 500:

### 📁 Archivos de diagnóstico y corrección:

1. **`donweb-diagnostico.php`** - Diagnóstico completo específico para DonWeb
2. **`donweb-fix-email.php`** - Corrección automática sin errores 500
3. **`donweb-htaccess.txt`** - .htaccess compatible con DonWeb

## ⚡ INSTALACIÓN RÁPIDA DONWEB (3 MINUTOS)

### PASO 1: Descargar archivos de corrección
- Descarga los 3 archivos de DonWeb específicos

### PASO 2: Subir y ejecutar
1. **Sube** `donweb-diagnostico.php` a `public_html`
2. **Visita** en tu navegador: `https://pinchesupplies.com.ar/donweb-diagnostico.php`
3. **Revisa** el diagnóstico (todo debe estar ✅)

### PASO 3: Ejecutar corrección automática
1. **Sube** `donweb-fix-email.php` a `public_html`
2. **Visita** en tu navegador: `https://pinchesupplies.com.ar/donweb-fix-email.php`
3. **El script automáticamente**:
   - ✅ Actualiza la base de datos
   - ✅ Crea las funciones optimizadas
   - ✅ Genera páginas de registro y verificación
   - ✅ Configura .htaccess compatible

### PASO 4: Configurar SMTP
1. **Edita** `email-config/config-email.php`
2. **Actualiza** con datos SMTP de tu dominio DonWeb

### PASO 5: ¡Probar!
- Ve a `https://pinchesupplies.com.ar/registro.php`
- Regístrate con un email real
- ¡El sistema ya pedirá verificación!

## 🔧 CONFIGURACIONES ESPECÍFICAS DONWEB

### ✅ Versión PHP recomendada
- **En cPanel → Selector de PHP**: Usar **PHP 7.4** o superior
- **Evitar** versiones muy antiguas (pueden tener funciones obsoletas)
- **Compatible** con PHP 8.0, 8.1, 8.2

### ✅ Permisos de archivos (cPanel)
- **Archivos PHP**: `644`
- **Carpetas**: `755`
- **Usar** Administrador de archivos de cPanel para cambiar permisos

### ✅ Configuración .htaccess
- **Reemplaza** tu .htaccess actual con el contenido de `donweb-htaccess.txt`
- **Sin configuraciones** que causen error 500
- **Optimizado** para el entorno DonWeb

### ✅ Base de datos DonWeb
```
Host: localhost
Base de datos: a0030995_pinche
Usuario: a0030995_pinche
Password: vawuDU97zu
```

## 📧 CONFIGURACIÓN SMTP DONWEB

### Datos SMTP típicos de DonWeb:
```
Host: mail.tudominio.com
Puerto: 587 (TLS) o 465 (SSL)
Usuario: noreply@tudominio.com
Password: [la contraseña de tu email]
```

### Ejemplo config-email.php:
```php
<?php
// Configuración SMTP para DonWeb
define('SMTP_HOST', 'mail.pinchesupplies.com.ar');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'noreply@pinchesupplies.com.ar');
define('SMTP_PASSWORD', 'tu-password-email');
define('SMTP_ENCRYPTION', 'tls');

// Emails del sistema
define('ADMIN_EMAIL', 'admin@pinchesupplies.com.ar');
define('CONTACT_EMAIL', 'info@pinchesupplies.com.ar');
define('FROM_NAME', 'Pinche Supplies');
?>
```

## 🚨 SI PERSISTE EL ERROR 500

### Pasos de diagnóstico DonWeb:

1. **Activar errores**:
   - cPanel → Editor MultiPHP INI
   - Cambiar `display_errors` a `On` temporalmente

2. **Revisar logs**:
   - cPanel → Registros de errores
   - Buscar errores recientes

3. **Verificar permisos**:
   - Administrador de archivos → Seleccionar archivo → Cambiar permisos
   - Archivos PHP = 644, Carpetas = 755

4. **Simplificar .htaccess**:
   - Renombrar `.htaccess` a `.htaccess_backup`
   - Ver si desaparece el error

5. **Version PHP**:
   - cPanel → Selector de PHP
   - Cambiar a PHP 7.4 o superior

## 🎯 RESULTADO FINAL

Después de seguir estos pasos tendrás:

✅ **Registro funcional** con verificación obligatoria  
✅ **Emails automáticos** (una vez configurado SMTP)  
✅ **Enlaces de verificación** operativos  
✅ **Login que verifica** antes de permitir acceso  
✅ **Sin errores 500** en el entorno DonWeb  
✅ **Sistema optimizado** para el hosting DonWeb  

## 📞 SOPORTE DONWEB

Si necesitas ayuda adicional:
1. **Panel cPanel** de DonWeb tiene documentación completa
2. **Soporte técnico** de DonWeb para configuraciones avanzadas
3. **Logs de error** en cPanel para diagnóstico específico

---
**💡 Los archivos creados están específicamente optimizados para el entorno DonWeb y evitan los problemas comunes de error 500.**
