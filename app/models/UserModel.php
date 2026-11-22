<?php
require_once __DIR__ . '/../config/Database.php';

class UserModel {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function findByEmail($email) {
        $sql = "SELECT u.*, s.store_name 
                FROM users u 
                JOIN stores s ON u.store_id = s.id 
                WHERE u.email = :email AND u.status = 1 LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':email' => $email]);
        return $stmt->fetch();
    }
}