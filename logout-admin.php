<?php
/**
 * Logout de Administrador
 */

require_once 'config/config.php';
require_once 'includes/class.database.php';
require_once 'includes/class.auth.php';

// La sesión ya fue iniciada en config.php

$auth = new Auth();
$auth->adminLogout();

// Redirigir a login con mensaje
header('Location: ' . SITE_URL . '/login-admin.php?logged_out=1');
exit;