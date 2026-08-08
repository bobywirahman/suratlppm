<?php
class User {
    private $pdo;
    
    public function __construct() {
        require_once __DIR__ . '/../config/db.php';
        $this->pdo = db();
    }
    
    public function login($email, $password) {
        $stmt = $this->pdo->prepare("SELECT u.*, r.name as role FROM users u LEFT JOIN user_roles ur ON u.id = ur.user_id LEFT JOIN roles r ON ur.role_id = r.id WHERE (u.email = ? OR u.username = ?) AND u.is_active = 1");
        $stmt->execute([$email, $email]);
        
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['nip'] = $user['nip'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['department_id'] = $user['department_id'];
            
            // Update last login
            $updateStmt = $this->pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $updateStmt->execute([$user['id']]);
            
            return true;
        }
        
        return false;
    }
    
    public function logout() {
        session_destroy();
    }
    
    public function checkAuth() {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = "Silakan login terlebih dahulu";
            redirect('auth/login.php');
            return false;
        }
        
        $userId = $_SESSION['user_id'];
        $stmt = $this->pdo->prepare("SELECT u.*, r.name as role FROM users u LEFT JOIN user_roles ur ON u.id = ur.user_id LEFT JOIN roles r ON ur.role_id = r.id WHERE u.id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if (!$user || !$user['is_active']) {
            $_SESSION['error'] = "Akun tidak aktif";
            redirect('auth/login.php');
            return false;
        }
        
        return $user;
    }
}
