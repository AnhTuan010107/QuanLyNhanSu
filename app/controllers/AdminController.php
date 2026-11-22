<?php
require_once __DIR__ . '/../config/Database.php';

class AdminController {
    private $conn;

    public function __construct() {
        if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin', 'manager'])) {
            header("Location: " . BASE_URL . "/login");
            exit;
        }
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function dashboard() {
        $stats = [
            'stores'            => $this->conn->query("SELECT COUNT(*) FROM stores WHERE status = 1")->fetchColumn(),
            'users'             => $this->conn->query("SELECT COUNT(*) FROM users WHERE status = 1")->fetchColumn(),
            'pending_schedules' => $this->conn->query("SELECT COUNT(*) FROM schedules WHERE status = 'pending'")->fetchColumn(),
            'trainings'         => $this->conn->query("SELECT COUNT(*) FROM trainings")->fetchColumn(),
        ];

        require_once __DIR__ . '/../views/admin/dashboard.php';
    }
}