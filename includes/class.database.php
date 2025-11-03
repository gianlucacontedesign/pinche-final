<?php
/**
 * Clase Database
 * Maneja la conexión y operaciones con la base de datos usando PDO
 */

class Database {
    private static $instance = null;
    private $conn;
    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ];
            
            $this->conn = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->conn;
    }
    
    public function query($sql, $params = []) {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Database query error: " . $e->getMessage());
            return false;
        }
    }
    
    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt ? $stmt->fetchAll() : [];
    }
    
    public function fetchOne($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt ? $stmt->fetch() : null;
    }
    
    public function insert($table, $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute(array_values($data));
            return $this->conn->lastInsertId();
        } catch (PDOException $e) {
            error_log("Database insert error: " . $e->getMessage() . " | SQL: " . $sql);
            throw new Exception("Error al insertar: " . $e->getMessage());
        }
    }
    
    public function update($table, $data, $where, $whereParams = []) {
        $set = [];
        foreach (array_keys($data) as $column) {
            $set[] = "{$column} = ?";
        }
        $set = implode(', ', $set);
        
        $sql = "UPDATE {$table} SET {$set} WHERE {$where}";
        $params = array_merge(array_values($data), $whereParams);
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return true;
        } catch (PDOException $e) {
            error_log("Database update error: " . $e->getMessage() . " | SQL: " . $sql);
            throw new Exception("Error al actualizar: " . $e->getMessage());
        }
    }
    
    public function delete($table, $where, $whereParams = []) {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($whereParams);
            return true;
        } catch (PDOException $e) {
            error_log("Database delete error: " . $e->getMessage() . " | SQL: " . $sql);
            throw new Exception("Error al eliminar: " . $e->getMessage());
        }
    }
    
    /**
     * Ejecutar una consulta SQL genérica (INSERT, UPDATE, DELETE)
     * @param string $sql Query SQL
     * @param array $params Parámetros para prepared statement
     * @return int|bool Para INSERT retorna lastInsertId, para UPDATE/DELETE retorna true/false
     */
    public function execute($sql, $params = []) {
        try {
            $stmt = $this->conn->prepare($sql);
            $result = $stmt->execute($params);
            
            // Si es un INSERT, retornar el ID insertado
            if (stripos(trim($sql), 'INSERT') === 0) {
                return $this->conn->lastInsertId();
            }
            
            // Para UPDATE/DELETE retornar true si fue exitoso
            return $result;
        } catch (PDOException $e) {
            error_log("Database execute error: " . $e->getMessage());
            throw $e; // Re-lanzar para que Customer pueda capturar con try-catch
        }
    }
    
    public function beginTransaction() {
        return $this->conn->beginTransaction();
    }
    
    public function commit() {
        return $this->conn->commit();
    }
    
    public function rollback() {
        return $this->conn->rollback();
    }
}
