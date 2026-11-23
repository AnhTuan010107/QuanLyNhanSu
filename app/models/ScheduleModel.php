<?php
require_once __DIR__ . '/../config/Database.php';

class ScheduleModel {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function getAllStores() {
        $sql = "SELECT id, store_name FROM stores ORDER BY store_name ASC";
        return $this->conn->query($sql)->fetchAll();
    }

    public function getAllShifts() {
        $sql = "SELECT id, shift_name, start_time, end_time, coefficient 
                FROM shifts ORDER BY start_time ASC";
        return $this->conn->query($sql)->fetchAll();
    }

    // Lấy danh sách nhân viên đang hoạt động để xếp ca trực tiếp
    public function getActiveEmployees($storeId = null) {
        $sql = "SELECT u.id, u.employee_code, u.full_name, u.role, u.store_id, s.store_name
                FROM users u
                JOIN stores s ON u.store_id = s.id
                WHERE u.status = 'active'
                  AND u.role NOT IN ('Quản lý')";
        $params = [];
        if (!empty($storeId)) {
            $sql .= " AND u.store_id = :store_id";
            $params[':store_id'] = $storeId;
        }
        $sql .= " ORDER BY s.store_name ASC, u.full_name ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // Lấy danh sách đăng ký ca / giờ rảnh theo bộ lọc
    public function getSchedules($filters = []) {
        $sql = "SELECT sc.id, sc.user_id, sc.store_id, sc.shift_id, sc.work_date, sc.status, sc.note,
                       u.employee_code, u.full_name, u.role AS user_role,
                       st.store_name,
                       sh.shift_name, sh.start_time, sh.end_time, sh.coefficient
                FROM schedules sc
                JOIN users u ON sc.user_id = u.id
                JOIN stores st ON sc.store_id = st.id
                JOIN shifts sh ON sc.shift_id = sh.id
                WHERE 1=1";
        $params = [];

        if (!empty($filters['store_id'])) {
            $sql .= " AND sc.store_id = :store_id";
            $params[':store_id'] = (int) $filters['store_id'];
        }

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $sql .= " AND sc.work_date BETWEEN :start_date AND :end_date";
            $params[':start_date'] = $filters['start_date'];
            $params[':end_date']   = $filters['end_date'];
        }

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $sql .= " AND sc.status = :status";
            $params[':status'] = $filters['status'];
        }

        $sql .= " ORDER BY sc.work_date ASC, FIELD(sc.status, 'pending', 'approved', 'rejected'), sh.start_time ASC, u.full_name ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        foreach ($rows as &$r) {
            $parsed = $this->parseNoteAndRange($r);
            $r['display_range'] = $parsed['range'];
            $r['clean_note']    = $parsed['note'];
        }
        return $rows;
    }

    // Tách khung giờ [08:00-13:00] và lời nhắn gửi Trưởng ca từ cột note
    private function parseNoteAndRange($row) {
        $note = trim($row['note'] ?? '');
        if ($note !== '' && preg_match('/^\[(\d{2}:\d{2})-(\d{2}:\d{2})\]\s*(.*)$/u', $note, $m)) {
            $s = (int) substr($m[1], 0, 2);
            $e = (int) substr($m[2], 0, 2);
            return [
                'range' => "{$s}h – {$e}h",
                'note'  => trim($m[3])
            ];
        }
        $s = (int) substr($row['start_time'], 0, 2);
        $e = (int) substr($row['end_time'], 0, 2);
        return [
            'range' => "{$s}h – {$e}h",
            'note'  => $note
        ];
    }

    // Duyệt 1 ca (cho phép Trưởng ca điều chỉnh lại shift_id nếu cần)
    public function approveSchedule($id, $shiftId = null, $storeScope = null) {
        if (!empty($shiftId)) {
            $sql = "UPDATE schedules SET status = 'approved', shift_id = :shift_id WHERE id = :id";
            $params = [':id' => $id, ':shift_id' => $shiftId];
        } else {
            $sql = "UPDATE schedules SET status = 'approved' WHERE id = :id";
            $params = [':id' => $id];
        }

        if (!empty($storeScope)) {
            $sql .= " AND store_id = :store_id";
            $params[':store_id'] = $storeScope;
        }

        return $this->conn->prepare($sql)->execute($params);
    }

    // Từ chối 1 ca
    public function rejectSchedule($id, $storeScope = null) {
        $sql = "UPDATE schedules SET status = 'rejected' WHERE id = :id";
        $params = [':id' => $id];

        if (!empty($storeScope)) {
            $sql .= " AND store_id = :store_id";
            $params[':store_id'] = $storeScope;
        }

        return $this->conn->prepare($sql)->execute($params);
    }

    // Duyệt hàng loạt tất cả ca đang chờ (pending) trong tuần/cửa hàng đang lọc
    public function approveAllPending($startDate, $endDate, $storeId = null) {
        $sql = "UPDATE schedules 
                SET status = 'approved' 
                WHERE status = 'pending' 
                  AND work_date BETWEEN :start_date AND :end_date";
        $params = [
            ':start_date' => $startDate,
            ':end_date'   => $endDate
        ];

        if (!empty($storeId)) {
            $sql .= " AND store_id = :store_id";
            $params[':store_id'] = (int) $storeId;
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    // Quản lý / Trưởng ca xếp ca trực tiếp cho nhân viên
    public function assignDirectSchedule($userId, $storeId, $shiftId, $workDate, $noteText = '') {
        // Lấy khung giờ của shift để lưu đồng bộ định dạng [HH:MM-HH:MM]
        $shStmt = $this->conn->prepare("SELECT start_time, end_time FROM shifts WHERE id = :id LIMIT 1");
        $shStmt->execute([':id' => $shiftId]);
        $shift = $shStmt->fetch();

        if (!$shift) {
            return false;
        }

        $s = substr($shift['start_time'], 0, 5);
        $e = substr($shift['end_time'], 0, 5);
        $formattedNote = "[{$s}-{$e}]" . ($noteText !== '' ? ' ' . $noteText : ' Trưởng ca xếp lịch');

        $sql = "INSERT INTO schedules (user_id, store_id, shift_id, work_date, status, note)
                VALUES (:user_id, :store_id, :shift_id, :work_date, 'approved', :note)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':user_id'   => $userId,
            ':store_id'  => $storeId,
            ':shift_id'  => $shiftId,
            ':work_date' => $workDate,
            ':note'      => $formattedNote
        ]);
    }

    // Xóa một dòng lịch làm việc
    public function deleteSchedule($id, $storeScope = null) {
        $sql = "DELETE FROM schedules WHERE id = :id";
        $params = [':id' => $id];

        if (!empty($storeScope)) {
            $sql .= " AND store_id = :store_id";
            $params[':store_id'] = $storeScope;
        }

        return $this->conn->prepare($sql)->execute($params);
    }
}