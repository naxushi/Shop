<?php
require_once 'database.php';

class Auth {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function register($name, $email, $password, $phone = '') {
        // Проверка существования email
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Пользователь с таким email уже существует'];
        }
        
        // Хэширование пароля
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Создание пользователя
        $stmt = $this->db->prepare("INSERT INTO users (name, email, password, phone) VALUES (:name, :email, :password, :phone)");
        $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':password' => $hashedPassword,
            ':phone' => $phone
        ]);
        
        $userId = $this->db->lastInsertId();
        
        // Автоматический вход после регистрации
        $this->login($email, $password);
        
        return ['success' => true, 'user_id' => $userId];
    }
    
    public function login($email, $password) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return ['success' => false, 'message' => 'Пользователь не найден'];
        }
        
        if (!password_verify($password, $user['password'])) {
            return ['success' => false, 'message' => 'Неверный пароль'];
        }
        
        // Установка сессии
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
        
        return ['success' => true, 'user' => $user];
    }
    
    public function logout() {
        session_destroy();
        return true;
    }
    
    public function getUser($id) {
        $stmt = $this->db->prepare("SELECT id, email, name, phone, role, created_at FROM users WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }
    
    public function updateProfile($userId, $name, $phone) {
        $stmt = $this->db->prepare("UPDATE users SET name = :name, phone = :phone WHERE id = :id");
        return $stmt->execute([
            ':id' => $userId,
            ':name' => $name,
            ':phone' => $phone
        ]);
    }
}
?>