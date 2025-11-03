<?php
/**
 * Logout de Cliente
 */

require_once 'config/config.php';
require_once 'includes/class.database.php';
require_once 'includes/class.auth.php';

// La sesión ya fue iniciada en config.php

$auth = new Auth();
$auth->customerLogout();

// Redirigir a home con mensaje
header('Location: ' . SITE_URL . '/?logged_out=1');
exit;
