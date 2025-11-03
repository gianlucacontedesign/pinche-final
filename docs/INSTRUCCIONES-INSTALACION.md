# 🚀 PINCHE SUPPLIES - INSTALACIÓN COMPLETA

## 📦 CONTENIDO DEL ZIP

Este archivo contiene el sistema completo de tu tienda con:
- ✅ Sistema de emails funcionando
- ✅ Verificación por email obligatoria
- ✅ Base de datos configurada
- ✅ Páginas de registro y login actualizadas
- ✅ Interfaz principal (index.php)

## 📋 PASOS DE INSTALACIÓN

### PASO 1: Subir archivos
1. **Descomprime este ZIP** en tu computadora
2. **Sube TODO el contenido** a la carpeta `public_html` de tu hosting DonWeb
3. **Reemplaza archivos existentes** si te pregunta

### PASO 2: Base de datos
1. **Ve a phpMyAdmin** en tu panel DonWeb
2. **Selecciona tu base de datos**: `a0030995_pinche`
3. **Ve a la pestaña SQL**
4. **Copia y pega** este código:

```sql
ALTER TABLE `users` 
ADD COLUMN `email_verified` TINYINT(1) DEFAULT 0 AFTER `email`,
ADD COLUMN `verification_token` VARCHAR(64) NULL AFTER `email_verified`,
ADD COLUMN `verification_token_expires` DATETIME NULL AFTER `verification_token`,
ADD INDEX (`verification_token`);
```

5. **Haz clic en "Continuar"**

### PASO 3: Configurar emails
1. **Edita el archivo**: `email-config/config-email.php`
2. **Actualiza los datos SMTP**:
   - Host SMTP de tu dominio DonWeb
   - Usuario y contraseña del email
   - Emails de administrador y contacto

### PASO 4: Probar el sistema
1. **Visita tu sitio**: https://pinchesupplies.com.ar
2. **Prueba el registro** con un email real
3. **Revisa tu email** y haz clic en el enlace de verificación
4. **Inicia sesión** con el usuario verificado

## 📧 FUNCIONALIDADES ACTIVAS

### ✅ Sistema de Email
- **Registro**: Envía email de verificación automático
- **Login**: Verifica que el email esté confirmado
- **Reenvío**: Opción para reenviar verificación si no llega el email
- **Limpieza**: Elimina automáticamente tokens expirados

### ✅ Base de datos actualizada
- **Campo**: `email_verified` (0=no, 1=sí)
- **Token**: `verification_token` (único por usuario)
- **Expiración**: `verification_token_expires` (24 horas)

### ✅ Nuevas páginas
- **verificar-email.php**: Procesa los enlaces de verificación
- **reenviar-verificacion.php**: Permite reenviar emails
- **includes/functions.php**: Funciones actualizadas con email

## 🎯 FLUJO DEL USUARIO

1. **Registro** → Usuario se registra
2. **Email** → Recibe email de verificación
3. **Verificación** → Hace clic en enlace del email
4. **Login** → Puede iniciar sesión normalmente
5. **Reenvío** → Si no recibió email, puede solicitar reenvío

## 🔧 ARCHIVOS IMPORTANTES

### Configuración
- `email-config/config-email.php` → Configuración SMTP
- `includes/functions.php` → Funciones de la tienda

### Páginas principales
- `index.php` → Página de inicio
- `registro.php` → Registro con verificación
- `login.php` → Login con verificación
- `verificar-email.php` → Procesa verificación
- `reenviar-verificacion.php` → Reenvía emails

### Sistema de emails
- `email-config/includes/email-sender.php` → Envío de emails
- `email-config/templates/` → Plantillas HTML

## 🚨 IMPORTANTE

1. **Configura SMTP** antes de probar (Paso 3)
2. **Ejecuta el SQL** de la base de datos (Paso 2)
3. **Usa emails reales** para las pruebas
4. **Revisa spam** si no recibes emails

## 📞 SOPORTE

Si tienes problemas:
1. Revisa que el SQL se ejecutó correctamente
2. Verifica la configuración SMTP
3. Comprueba los logs de error en DonWeb
4. Asegúrate de que los permisos de archivos sean 644

¡Tu tienda está lista para funcionar! 🎉
