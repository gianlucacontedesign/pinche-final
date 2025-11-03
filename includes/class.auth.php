<?php
/**
 * Clase Auth
 * Maneja la autenticación y autorización de usuarios
 */

class Auth {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function login($username, $password) {
        $sql = "SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1";
        $user = $this->db->fetchOne($sql, [$username, $username]);
        
        if ($user && password_verify($password, $user['password'])) {
            // Regenerar ID de sesión por seguridad
            session_regenerate_id(true);
            
            // Guardar datos en sesión
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['logged_in'] = true;
            $_SESSION['last_activity'] = time();
            
            return true;
        }
        
        return false;
    }
    
    public function logout() {
        session_unset();
        session_destroy();
        session_write_close();
        setcookie(session_name(), '', 0, '/');
        session_regenerate_id(true);
    }
    
    public function isLoggedIn() {
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            return false;
        }
        
        // Verificar timeout de sesión
        if (isset($_SESSION['last_activity']) && 
            (time() - $_SESSION['last_activity'] > SESSION_LIFETIME)) {
            $this->logout();
            return false;
        }
        
        $_SESSION['last_activity'] = time();
        return true;
    }
    
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: ' . ADMIN_URL . '/login.php');
            exit;
        }
    }
    
    public function requireAdmin() {
        $this->requireLogin();
        if ($_SESSION['role'] !== 'admin') {
            header('Location: ' . ADMIN_URL . '/index.php');
            exit;
        }
    }
    
    public function getCurrentUser() {
        if ($this->isLoggedIn()) {
            return [
                'id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'full_name' => $_SESSION['full_name'],
                'role' => $_SESSION['role']
            ];
        }
        return null;
    }
    
    public function createUser($username, $email, $password, $fullName, $role = 'manager') {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $data = [
            'username' => $username,
            'email' => $email,
            'password' => $hashedPassword,
            'full_name' => $fullName,
            'role' => $role
        ];
        
        return $this->db->insert('users', $data);
    }
    
    public function changePassword($userId, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        return $this->db->update('users', 
            ['password' => $hashedPassword], 
            'id = ?', 
            [$userId]
        );
    }
    
    // ========================================
    // MÉTODOS PARA CLIENTES (CUSTOMER)
    // ========================================
    
    /**
     * Login de cliente
     * @param string $email Email del cliente
     * @param string $password Contraseña
     * @param bool $remember Recordar sesión
     * @return bool
     */
    public function customerLogin($email, $password, $remember = false) {
        require_once __DIR__ . '/class.customer.php';
        $customerModel = new Customer();
        
        $customer = $customerModel->authenticate($email, $password);
        
        if ($customer) {
            // Regenerar ID de sesión por seguridad
            session_regenerate_id(true);
            
            // Guardar datos en sesión con prefijo 'customer_'
            $_SESSION['customer_id'] = $customer['id'];
            $_SESSION['customer_email'] = $customer['email'];
            $_SESSION['customer_name'] = $customer['first_name'] . ' ' . $customer['last_name'];
            $_SESSION['customer_first_name'] = $customer['first_name'];
            $_SESSION['customer_last_name'] = $customer['last_name'];
            $_SESSION['customer_logged_in'] = true;
            $_SESSION['customer_last_activity'] = time();
            
            // Si activó "recordarme", extender sesión
            if ($remember) {
                $_SESSION['customer_remember'] = true;
            }
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Logout de cliente
     */
    public function customerLogout() {
        // Limpiar solo las variables de sesión del cliente
        unset($_SESSION['customer_id']);
        unset($_SESSION['customer_email']);
        unset($_SESSION['customer_name']);
        unset($_SESSION['customer_first_name']);
        unset($_SESSION['customer_last_name']);
        unset($_SESSION['customer_logged_in']);
        unset($_SESSION['customer_last_activity']);
        unset($_SESSION['customer_remember']);
        
        // Si no hay sesión de admin activa, destruir sesión completa
        if (!$this->isLoggedIn()) {
            session_unset();
            session_destroy();
            session_write_close();
            setcookie(session_name(), '', 0, '/');
        }
    }
    
    /**
     * Verificar si un cliente está logueado
     * @return bool
     */
    public function isCustomerLoggedIn() {
        if (!isset($_SESSION['customer_logged_in']) || $_SESSION['customer_logged_in'] !== true) {
            return false;
        }
        
        // Verificar timeout de sesión (solo si no activó "recordarme")
        if (!isset($_SESSION['customer_remember']) || $_SESSION['customer_remember'] !== true) {
            if (isset($_SESSION['customer_last_activity']) && 
                (time() - $_SESSION['customer_last_activity'] > SESSION_LIFETIME)) {
                $this->customerLogout();
                return false;
            }
        }
        
        $_SESSION['customer_last_activity'] = time();
        return true;
    }
    
    /**
     * Verificar si un admin está logueado (para evitar confusiones)
     * @return bool
     */
    public function isAdminLoggedIn() {
        return $this->isLoggedIn();
    }
    
    /**
     * Requiere que el cliente esté logueado
     * @param string $redirectUrl URL de redirección si no está logueado
     */
    public function requireCustomerLogin($redirectUrl = null) {
        if (!$this->isCustomerLoggedIn()) {
            if ($redirectUrl === null) {
                $redirectUrl = SITE_URL . '/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']);
            }
            header('Location: ' . $redirectUrl);
            exit;
        }
    }
    
    /**
     * Obtener datos del cliente actual
     * @return array|null
     */
    public function getCurrentCustomer() {
        if ($this->isCustomerLoggedIn()) {
            return [
                'id' => $_SESSION['customer_id'],
                'email' => $_SESSION['customer_email'],
                'name' => $_SESSION['customer_name'],
                'first_name' => $_SESSION['customer_first_name'],
                'last_name' => $_SESSION['customer_last_name']
            ];
        }
        return null;
    }
    
    /**
     * Obtener ID del cliente actual
     * @return int|null
     */
    public function getCustomerId() {
        if ($this->isCustomerLoggedIn()) {
            return $_SESSION['customer_id'];
        }
        return null;
    }
}
