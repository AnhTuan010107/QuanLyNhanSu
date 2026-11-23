<?php
require_once __DIR__ . '/../config/Database.php';

class ShiftModel {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn =$db->getConnection();
    }

    // Lấy danh sách tất cả ca làm kèm số lượt đã được xếp lịch
    public function getAllShifts() {
        $sql = "SELECT sh.id, sh.shift_name, sh.start_time, sh.end_time, sh.coefficient,
                       COUNT(sc.id) AS total_schedules
                FROM shifts sh
                LEFT JOIN schedules sc ON sh.id = sc.shift_id
                GROUP BY sh.id, sh.shift_name, sh.start_time, sh.end_time, sh.coefficient
                ORDER BY sh.start_time ASC";
        return $this->conn->query($sql)->fetchAll();
    }

    public function create($shiftName, $startTime,$endTime, $coefficient) {$sql = "INSERT INTO shifts (shift_name, start_time, end_time, coefficient)
                VALUES (:shift_name, :start_time, :end_time, :coefficient)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':shift_name'  => $shiftName,
            ':start_time'  => $startTime,
            ':end_time'    => $endTime,
            ':coefficient' => $coefficient
        ]);
    }

    public function update($id,$shiftName, $startTime,$endTime, $coefficient) {$sql = "UPDATE shifts 
                SET shift_name = :shift_name,
                    start_time = :start_time,
                    end_time = :end_time,
                    coefficient = :coefficient
                WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':id'          => $id,
            ':shift_name'  => $shiftName,
            ':start_time'  => $startTime,
            ':end_time'    => $endTime,
            ':coefficient' => $coefficient
        ]);
    }

    public function isShiftInUse($id) {$sql = "SELECT COUNT(*) FROM schedules WHERE shift_id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' =>$id]);
        return ((int) $stmt->fetchColumn()) > 0;
    }

    public function delete($id) {$sql = "DELETE FROM shifts WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':id' =>$id]);
    }
}