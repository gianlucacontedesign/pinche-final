# Sistema de Checkout - Pinche Supplies

## 🎯 Descripción

Sistema completo de checkout para e-commerce que guarda los pedidos en base de datos MySQL y permite su gestión desde un panel de administración.

## ✨ Características

- ✅ Checkout funcional con validación de datos
- ✅ Guardado de pedidos en MySQL
- ✅ Panel de administración integrado
- ✅ Gestión de estados de pedidos
- ✅ Búsqueda y filtros avanzados
- ✅ Estadísticas de pedidos
- ✅ Transacciones SQL seguras
- ✅ Sistema de pruebas incluido

## 📁 Archivos Principales

- `save-order-db.php` - Endpoint para guardar pedidos
- `checkout.php` - Página de checkout
- `install-checkout.php` - Script de instalación
- `test-checkout.php` - Script de pruebas
- `admin/orders.php` - Panel de gestión de pedidos

## 📚 Documentación

- [Guía de Instalación](GUIA-INSTALACION-CHECKOUT.md)
- [Arquitectura del Sistema](ARQUITECTURA-CHECKOUT.md)
- [Cambios Realizados](CAMBIOS-REALIZADOS.md)

## 🚀 Inicio Rápido

1. Importar base de datos: `database/database-completa.sql`
2. Configurar credenciales en `includes/config.php`
3. Ejecutar `install-checkout.php` para verificar
4. Probar con `test-checkout.php`
5. Verificar pedidos en `admin/orders.php`

## 🔧 Requisitos

- PHP 7.4+
- MySQL 5.7+
- Extensiones: PDO, PDO_MySQL, JSON, cURL

## 📞 Soporte

Ver documentación completa en `GUIA-INSTALACION-CHECKOUT.md`
