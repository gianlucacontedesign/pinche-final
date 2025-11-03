<?php
/**
 * Clase Customer
 * Maneja la gestión de clientes/usuarios del sitio web
 */

class Customer {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Registrar un nuevo cliente
     * @param array $data Datos del cliente
     * @return array ['success' => bool, 'message' => string, 'customer_id' => int]
     */
    public function register($data) {
        // Validar campos requeridos
        $required = ['email', 'password', 'first_name', 'last_name'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return ['success' => false, 'message' => 'Todos los campos obligatorios deben completarse'];
            }
        }
        
        // Validar formato de email
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'El formato del email no es válido'];
        }
        
        // Validar longitud de contraseña
        if (strlen($data['password']) < 6) {
            return ['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres'];
        }
        
        // Verificar si el email ya existe
        if ($this->emailExists($data['email'])) {
            return ['success' => false, 'message' => 'Este email ya está registrado'];
        }
        
        // Hash de la contraseña
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        
        // Generar token de verificación (para futuro uso)
        $verificationToken = bin2hex(random_bytes(32));
        
        // Preparar datos
        $sql = "INSERT INTO customers (
            email, password, first_name, last_name, phone, 
            address, city, state, zip_code, country,
            verification_token, verification_token_expires
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 24 HOUR))";
        
        $params = [
            $data['email'],
            $hashedPassword,
            $data['first_name'],
            $data['last_name'],
            $data['phone'] ?? null,
            $data['address'] ?? null,
            $data['city'] ?? null,
            $data['state'] ?? null,
            $data['zip_code'] ?? null,
            $data['country'] ?? 'Argentina',
            $verificationToken
        ];
        
        try {
            $customerId = $this->db->execute($sql, $params);
            
            return [
                'success' => true,
                'message' => 'Registro exitoso',
                'customer_id' => $customerId,
                'verification_token' => $verificationToken
            ];
        } catch (Exception $e) {
            error_log("Error al registrar cliente: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al crear la cuenta. Intenta nuevamente.'];
        }
    }
    
    /**
     * Autenticar un cliente
     * @param string $email Email del cliente
     * @param string $password Contraseña
     * @return array|false Datos del cliente o false si falla
     */
    public function authenticate($email, $password) {
        $sql = "SELECT * FROM customers WHERE email = ? LIMIT 1";
        $customer = $this->db->fetchOne($sql, [$email]);
        
        if ($customer && password_verify($password, $customer['password'])) {
            // Actualizar último login
            $this->updateLastLogin($customer['id']);
            return $customer;
        }
        
        return false;
    }
    
    /**
     * Verificar si un email ya existe
     * @param string $email Email a verificar
     * @return bool
     */
    public function emailExists($email) {
        $sql = "SELECT COUNT(*) as count FROM customers WHERE email = ?";
        $result = $this->db->fetchOne($sql, [$email]);
        return $result && $result['count'] > 0;
    }
    
    /**
     * Obtener cliente por ID
     * @param int $id ID del cliente
     * @return array|false Datos del cliente o false
     */
    public function getById($id) {
        $sql = "SELECT * FROM customers WHERE id = ? LIMIT 1";
        return $this->db->fetchOne($sql, [$id]);
    }
    
    /**
     * Obtener cliente por email
     * @param string $email Email del cliente
     * @return array|false Datos del cliente o false
     */
    public function getByEmail($email) {
        $sql = "SELECT * FROM customers WHERE email = ? LIMIT 1";
        return $this->db->fetchOne($sql, [$email]);
    }
    
    /**
     * Actualizar perfil del cliente
     * @param int $customerId ID del cliente
     * @param array $data Datos a actualizar
     * @return array ['success' => bool, 'message' => string]
     */
    public function updateProfile($customerId, $data) {
        // Campos permitidos para actualizar
        $allowedFields = ['first_name', 'last_name', 'phone', 'address', 'city', 'state', 'zip_code', 'country'];
        $updates = [];
        $params = [];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updates[] = "$field = ?";
                $params[] = $data[$field];
            }
        }
        
        if (empty($updates)) {
            return ['success' => false, 'message' => 'No hay datos para actualizar'];
        }
        
        $params[] = $customerId;
        $sql = "UPDATE customers SET " . implode(', ', $updates) . " WHERE id = ?";
        
        try {
            $this->db->execute($sql, $params);
            return ['success' => true, 'message' => 'Perfil actualizado correctamente'];
        } catch (Exception $e) {
            error_log("Error al actualizar perfil: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al actualizar el perfil'];
        }
    }
    
    /**
     * Cambiar contraseña del cliente
     * @param int $customerId ID del cliente
     * @param string $currentPassword Contraseña actual
     * @param string $newPassword Nueva contraseña
     * @return array ['success' => bool, 'message' => string]
     */
    public function changePassword($customerId, $currentPassword, $newPassword) {
        // Obtener cliente
        $customer = $this->getById($customerId);
        
        if (!$customer) {
            return ['success' => false, 'message' => 'Cliente no encontrado'];
        }
        
        // Verificar contraseña actual
        if (!password_verify($currentPassword, $customer['password'])) {
            return ['success' => false, 'message' => 'La contraseña actual es incorrecta'];
        }
        
        // Validar nueva contraseña
        if (strlen($newPassword) < 6) {
            return ['success' => false, 'message' => 'La nueva contraseña debe tener al menos 6 caracteres'];
        }
        
        // Actualizar contraseña
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $sql = "UPDATE customers SET password = ? WHERE id = ?";
        
        try {
            $this->db->execute($sql, [$hashedPassword, $customerId]);
            return ['success' => true, 'message' => 'Contraseña actualizada correctamente'];
        } catch (Exception $e) {
            error_log("Error al cambiar contraseña: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al cambiar la contraseña'];
        }
    }
    
    /**
     * Solicitar reset de contraseña
     * @param string $email Email del cliente
     * @return array ['success' => bool, 'message' => string, 'reset_token' => string]
     */
    public function requestPasswordReset($email) {
        $customer = $this->getByEmail($email);
        
        if (!$customer) {
            // Por seguridad, no revelar si el email existe
            return ['success' => true, 'message' => 'Si el email existe, recibirás instrucciones para resetear tu contraseña'];
        }
        
        // Generar token único
        $resetToken = bin2hex(random_bytes(32));
        
        // Guardar token en BD (expira en 1 hora)
        $sql = "UPDATE customers SET 
                reset_token = ?, 
                reset_token_expires = DATE_ADD(NOW(), INTERVAL 1 HOUR) 
                WHERE id = ?";
        
        try {
            $this->db->execute($sql, [$resetToken, $customer['id']]);
            
            return [
                'success' => true,
                'message' => 'Si el email existe, recibirás instrucciones para resetear tu contraseña',
                'reset_token' => $resetToken,
                'customer_id' => $customer['id']
            ];
        } catch (Exception $e) {
            error_log("Error al solicitar reset: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al procesar la solicitud'];
        }
    }
    
    /**
     * Resetear contraseña con token
     * @param string $token Token de reset
     * @param string $newPassword Nueva contraseña
     * @return array ['success' => bool, 'message' => string]
     */
    public function resetPassword($token, $newPassword) {
        // Buscar cliente con token válido
        $sql = "SELECT * FROM customers 
                WHERE reset_token = ? 
                AND reset_token_expires > NOW() 
                LIMIT 1";
        
        $customer = $this->db->fetchOne($sql, [$token]);
        
        if (!$customer) {
            return ['success' => false, 'message' => 'Token inválido o expirado'];
        }
        
        // Validar nueva contraseña
        if (strlen($newPassword) < 6) {
            return ['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres'];
        }
        
        // Actualizar contraseña y limpiar token
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $sql = "UPDATE customers SET 
                password = ?, 
                reset_token = NULL, 
                reset_token_expires = NULL 
                WHERE id = ?";
        
        try {
            $this->db->execute($sql, [$hashedPassword, $customer['id']]);
            return ['success' => true, 'message' => 'Contraseña actualizada correctamente'];
        } catch (Exception $e) {
            error_log("Error al resetear contraseña: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al actualizar la contraseña'];
        }
    }
    
    /**
     * Verificar email con token (para futuro uso)
     * @param string $token Token de verificación
     * @return array ['success' => bool, 'message' => string]
     */
    public function verifyEmail($token) {
        $sql = "SELECT * FROM customers 
                WHERE verification_token = ? 
                AND verification_token_expires > NOW() 
                LIMIT 1";
        
        $customer = $this->db->fetchOne($sql, [$token]);
        
        if (!$customer) {
            return ['success' => false, 'message' => 'Token inválido o expirado'];
        }
        
        // Marcar email como verificado
        $sql = "UPDATE customers SET 
                email_verified = 1, 
                verification_token = NULL, 
                verification_token_expires = NULL 
                WHERE id = ?";
        
        try {
            $this->db->execute($sql, [$customer['id']]);
            return ['success' => true, 'message' => 'Email verificado correctamente'];
        } catch (Exception $e) {
            error_log("Error al verificar email: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al verificar el email'];
        }
    }
    
    /**
     * Actualizar último login
     * @param int $customerId ID del cliente
     */
    private function updateLastLogin($customerId) {
        $sql = "UPDATE customers SET last_login = NOW() WHERE id = ?";
        $this->db->execute($sql, [$customerId]);
    }
    
    /**
     * Obtener pedidos del cliente
     * @param int $customerId ID del cliente
     * @param int $limit Límite de resultados
     * @return array Lista de pedidos
     */
    public function getOrders($customerId, $limit = 10) {
        $sql = "SELECT * FROM orders 
                WHERE customer_id = ? 
                ORDER BY created_at DESC 
                LIMIT ?";
        
        return $this->db->fetchAll($sql, [$customerId, $limit]);
    }
    
    /**
     * Obtener estadísticas del cliente
     * @param int $customerId ID del cliente
     * @return array Estadísticas
     */
    public function getStats($customerId) {
        $sql = "SELECT 
                COUNT(*) as total_orders,
                SUM(total) as total_spent,
                MAX(created_at) as last_order_date
                FROM orders 
                WHERE customer_id = ?";
        
        $stats = $this->db->fetchOne($sql, [$customerId]);
        
        return [
            'total_orders' => $stats['total_orders'] ?? 0,
            'total_spent' => $stats['total_spent'] ?? 0,
            'last_order_date' => $stats['last_order_date'] ?? null
        ];
    }
}
