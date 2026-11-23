<?php
require_once __DIR__ . '/../config/Database.php';

class StoreModel {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function getAll() {
        $sql = "SELECT s.id, s.store_name, s.address, s.phone, s.status, s.created_at,
                       COUNT(u.id) AS total_staff
                FROM stores s
                LEFT JOIN users u ON s.id = u.store_id AND u.status = 1
                GROUP BY s.id, s.store_name, s.address, s.phone, s.status, s.created_at
                ORDER BY s.id DESC";
        return $this->conn->query($sql)->fetchAll();
    }

    public function getActiveStores() {
        $sql = "SELECT id, store_name FROM stores WHERE status = 1 ORDER BY store_name ASC";
        return $this->conn->query($sql)->fetchAll();
    }

    public function create($storeName, $address, $phone) {
        $sql = "INSERT INTO stores (store_name, address, phone, status) VALUES (:store_name, :address, :phone, 1)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':store_name' => $storeName,
            ':address'    => $address,
            ':phone'      => $phone
        ]);
    }

    public function update($id, $storeName, $address, $phone, $status) {
        $sql = "UPDATE stores 
                SET store_name = :store_name, address = :address, phone = :phone, status = :status 
                WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':id'         => $id,
            ':store_name' => $storeName,
            ':address'    => $address,
            ':phone'      => $phone,
            ':status'     => $status
        ]);
    }

    public function countUsersInStore($id) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM users WHERE store_id = :id");
        $stmt->execute([':id' => $id]);
        return (int) $stmt->fetchColumn();
    }

    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM stores WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}