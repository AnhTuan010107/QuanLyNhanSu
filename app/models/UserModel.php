<?php
require_once __DIR__ . '/../config/Database.php';

class UserModel {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn =$db->getConnection();
    }

    public function findByEmail($email) {$sql = "SELECT u.*, s.store_name 
                FROM users u 
                JOIN stores s ON u.store_id = s.id 
                WHERE u.email = :email AND u.status = 1 LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':email' =>$email]);
        return $stmt->fetch();
    }

    public function getAll($storeId = null) {$sql = "SELECT u.id, u.store_id, u.employee_code, u.full_name, u.email, 
                       u.phone, u.role, u.hourly_rate, u.status, u.created_at, s.store_name
                FROM users u
                JOIN stores s ON u.store_id = s.id";
        if ($storeId !== null) {$sql .= " WHERE u.store_id = :store_id";
        }
        $sql .= " ORDER BY u.id DESC";

        $stmt = $this->conn->prepare($sql);
        if ($storeId !== null) {
            $stmt->execute([':store_id' =>$storeId]);
        } else {
            $stmt->execute();
        }
        return $stmt->fetchAll();
    }

    public function existsEmployeeCode($employeeCode, $excludeId = null) {$sql = "SELECT COUNT(*) FROM users WHERE employee_code = :code";
        $params = [':code' =>$employeeCode];
        if ($excludeId !== null) {$sql .= " AND id != :id";
            $params[':id'] =$excludeId;
        }
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function existsEmail($email, $excludeId = null) {$sql = "SELECT COUNT(*) FROM users WHERE email = :email";
        $params = [':email' =>$email];
        if ($excludeId !== null) {$sql .= " AND id != :id";
            $params[':id'] =$excludeId;
        }
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function create($data) {$sql = "INSERT INTO users (store_id, employee_code, full_name, email, password, phone, role, hourly_rate, status)
                VALUES (:store_id, :employee_code, :full_name, :email, :password, :phone, :role, :hourly_rate, 1)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':store_id'      => $data['store_id'],
            ':employee_code' => $data['employee_code'],
            ':full_name'     => $data['full_name'],
            ':email'         => $data['email'],
            ':password'      => $data['password'],
            ':phone'         => $data['phone'],
            ':role'          => $data['role'],
            ':hourly_rate'   => $data['hourly_rate']
        ]);
    }

    public function update($id,$data) {
        if (!empty($data['password'])) {$sql = "UPDATE users 
                    SET store_id = :store_id, employee_code = :employee_code, full_name = :full_name,
                        email = :email, password = :password, phone = :phone, role = :role,
                        hourly_rate = :hourly_rate, status = :status
                    WHERE id = :id";
            $params = [
                ':id'            => $id,
                ':store_id'      => $data['store_id'],
                ':employee_code' => $data['employee_code'],
                ':full_name'     => $data['full_name'],
                ':email'         => $data['email'],
                ':password'      => $data['password'],
                ':phone'         => $data['phone'],
                ':role'          => $data['role'],
                ':hourly_rate'   => $data['hourly_rate'],
                ':status'        => $data['status']
            ];
        } else {
            $sql = "UPDATE users 
                    SET store_id = :store_id, employee_code = :employee_code, full_name = :full_name,
                        email = :email, phone = :phone, role = :role,
                        hourly_rate = :hourly_rate, status = :status
                    WHERE id = :id";
            $params = [
                ':id'            => $id,
                ':store_id'      => $data['store_id'],
                ':employee_code' => $data['employee_code'],
                ':full_name'     => $data['full_name'],
                ':email'         => $data['email'],
                ':phone'         => $data['phone'],
                ':role'          => $data['role'],
                ':hourly_rate'   => $data['hourly_rate'],
                ':status'        => $data['status']
            ];
        }

        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete($id) {
        $stmt =$this->conn->prepare("DELETE FROM users WHERE id = :id");
        return $stmt->execute([':id' =>$id]);
    }
}