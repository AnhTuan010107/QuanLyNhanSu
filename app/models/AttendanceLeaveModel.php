<?php
require_once __DIR__ . '/../config/Database.php';

class AttendanceLeaveModel {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn =$db->getConnection();
    }

    public function getAllStores() {
        return $this->conn->query("SELECT id, store_name FROM stores ORDER BY store_name ASC")->fetchAll();
    }

    public function getActiveEmployees($storeId = null) {$sql = "SELECT u.id, u.employee_code, u.full_name, u.role, u.store_id, s.store_name
                FROM users u
                JOIN stores s ON u.store_id = s.id
                WHERE u.status = 'active' AND u.role != 'Quản lý'";
        $params = [];
        if (!empty($storeId)) {$sql .= " AND u.store_id = :store_id";
            $params[':store_id'] = (int)$storeId;
        }
        $sql .= " ORDER BY s.store_name ASC, u.full_name ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // ==================== CHẤM CÔNG (NHÂN VIÊN) ====================

    public function getTodayApprovedShifts($userId, $today) {$sql = "SELECT sc.id AS schedule_id, sc.work_date, sh.shift_name, sh.start_time, sh.end_time,
                       a.id AS attendance_id, a.check_in, a.check_out, a.status AS att_status, a.note AS att_note
                FROM schedules sc
                JOIN shifts sh ON sc.shift_id = sh.id
                LEFT JOIN attendances a ON a.schedule_id = sc.id
                WHERE sc.user_id = :user_id
                  AND sc.work_date = :work_date
                  AND sc.status = 'approved'
                ORDER BY sh.start_time ASC";
        $stmt =$this->conn->prepare($sql);$stmt->execute([':user_id' => $userId, ':work_date' =>$today]);
        return $stmt->fetchAll();
    }

    public function getTodayAttendances($userId, $today) {$sql = "SELECT a.*, sh.shift_name, sh.start_time, sh.end_time
                FROM attendances a
                LEFT JOIN schedules sc ON a.schedule_id = sc.id
                LEFT JOIN shifts sh ON sc.shift_id = sh.id
                WHERE a.user_id = :user_id AND a.work_date = :work_date
                ORDER BY a.id ASC";
        $stmt =$this->conn->prepare($sql);$stmt->execute([':user_id' => $userId, ':work_date' =>$today]);
        return $stmt->fetchAll();
    }

    public function checkIn($userId, $storeId,$scheduleId = null) {
        $today = date('Y-m-d');$now   = date('H:i:s');

        if (!empty($scheduleId)) {
            $chk =$this->conn->prepare("SELECT id FROM attendances WHERE user_id = :u AND schedule_id = :s LIMIT 1");
            $chk->execute([':u' => $userId, ':s' =>$scheduleId]);
            if ($chk->fetch()) return false;
        } else {
            $chk =$this->conn->prepare("SELECT id FROM attendances WHERE user_id = :u AND work_date = :d AND schedule_id IS NULL LIMIT 1");
            $chk->execute([':u' => $userId, ':d' =>$today]);
            if ($chk->fetch()) return false;
        }

        // Tự động đánh dấu đi trễ nếu check-in muộn hơn giờ vào ca > 10 phút
        $status = 'on_time';
        if (!empty($scheduleId)) {
            $shStmt =$this->conn->prepare("SELECT sh.start_time 
                                            FROM schedules sc 
                                            JOIN shifts sh ON sc.shift_id = sh.id 
                                            WHERE sc.id = :id LIMIT 1");
            $shStmt->execute([':id' =>$scheduleId]);
            $startTime =$shStmt->fetchColumn();
            if ($startTime) {
                $shiftStartTs = strtotime($today . ' ' . $startTime);$nowTs        = strtotime($today . ' ' .$now);
                if (($nowTs - $shiftStartTs) > 600) {$status = 'late';
                }
            }
        }

        $sql = "INSERT INTO attendances (user_id, store_id, schedule_id, work_date, check_in, status)
                VALUES (:user_id, :store_id, :schedule_id, :work_date, :check_in, :status)";
        return $this->conn->prepare($sql)->execute([
            ':user_id'     => $userId,
            ':store_id'    => $storeId,
            ':schedule_id' => !empty($scheduleId) ?$scheduleId : null,
            ':work_date'   => $today,
            ':check_in'    => $now,
            ':status'      => $status
        ]);
    }

    public function checkOut($attendanceId,$userId) {
        $now = date('H:i:s');$sql = "UPDATE attendances 
                SET check_out = :check_out 
                WHERE id = :id AND user_id = :user_id AND check_out IS NULL";
        $stmt =$this->conn->prepare($sql);$stmt->execute([
            ':check_out' => $now,
            ':id'        => $attendanceId,
            ':user_id'   => $userId
        ]);
        return $stmt->rowCount() > 0;
    }

    public function getUserAttendanceHistory($userId, $monthYear) {$sql = "SELECT a.*, sh.shift_name, sh.start_time, sh.end_time
                FROM attendances a
                LEFT JOIN schedules sc ON a.schedule_id = sc.id
                LEFT JOIN shifts sh ON sc.shift_id = sh.id
                WHERE a.user_id = :user_id
                  AND DATE_FORMAT(a.work_date, '%Y-%m') = :month_year
                ORDER BY a.work_date DESC, a.check_in DESC";
        $stmt =$this->conn->prepare($sql);$stmt->execute([':user_id' => $userId, ':month_year' =>$monthYear]);
        return $stmt->fetchAll();
    }

    // ==================== CHẤM CÔNG (ADMIN / TRƯỞNG CA) ====================

    public function getAttendancesByDate($workDate, $storeId = null) {$sql = "SELECT a.*, u.employee_code, u.full_name, u.role, st.store_name,
                       sh.shift_name, sh.start_time, sh.end_time
                FROM attendances a
                JOIN users u ON a.user_id = u.id
                JOIN stores st ON a.store_id = st.id
                LEFT JOIN schedules sc ON a.schedule_id = sc.id
                LEFT JOIN shifts sh ON sc.shift_id = sh.id
                WHERE a.work_date = :work_date";
        $params = [':work_date' =>$workDate];

        if (!empty($storeId)) {$sql .= " AND a.store_id = :store_id";
            $params[':store_id'] = (int)$storeId;
        }

        $sql .= " ORDER BY st.store_name ASC, a.check_in ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function createManualAttendance($userId,$storeId, $workDate,$checkIn, $checkOut,$status, $note) {
        $sql = "INSERT INTO attendances (user_id, store_id, work_date, check_in, check_out, status, note)
                VALUES (:user_id, :store_id, :work_date, :check_in, :check_out, :status, :note)";
        return $this->conn->prepare($sql)->execute([
            ':user_id'   => $userId,
            ':store_id'  => $storeId,
            ':work_date' => $workDate,
            ':check_in'  => $checkIn !== '' ?$checkIn : null,
            ':check_out' => $checkOut !== '' ?$checkOut : null,
            ':status'    => $status,
            ':note'      => $note !== '' ? $note : null
        ]);
    }

    public function updateAttendance($id,$checkIn, $checkOut,$status, $note, $storeScope = null) {$sql = "UPDATE attendances 
                SET check_in = :check_in, check_out = :check_out, status = :status, note = :note 
                WHERE id = :id";
        $params = [
            ':id'        => $id,
            ':check_in'  => $checkIn !== '' ?$checkIn : null,
            ':check_out' => $checkOut !== '' ?$checkOut : null,
            ':status'    => $status,
            ':note'      => $note !== '' ? $note : null
        ];
        if (!empty($storeScope)) {$sql .= " AND store_id = :store_id";
            $params[':store_id'] =$storeScope;
        }
        return $this->conn->prepare($sql)->execute($params);
    }

    public function deleteAttendance($id, $storeScope = null) {$sql = "DELETE FROM attendances WHERE id = :id";
        $params = [':id' =>$id];
        if (!empty($storeScope)) {$sql .= " AND store_id = :store_id";
            $params[':store_id'] =$storeScope;
        }
        return $this->conn->prepare($sql)->execute($params);
    }

    // ==================== NGHỈ PHÉP ====================

    public function createLeaveRequest($userId, $storeId,$leaveType, $startDate,$endDate, $reason) {$sql = "INSERT INTO leave_requests (user_id, store_id, leave_type, start_date, end_date, reason, status)
                VALUES (:user_id, :store_id, :leave_type, :start_date, :end_date, :reason, 'pending')";
        return $this->conn->prepare($sql)->execute([
            ':user_id'    => $userId,
            ':store_id'   => $storeId,
            ':leave_type' => $leaveType,
            ':start_date' => $startDate,
            ':end_date'   => $endDate,
            ':reason'     => $reason
        ]);
    }

    public function getUserLeaveRequests($userId) {$sql = "SELECT * FROM leave_requests WHERE user_id = :user_id ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':user_id' =>$userId]);
        return $stmt->fetchAll();
    }

    public function cancelUserLeaveRequest($id, $userId) {$sql = "DELETE FROM leave_requests WHERE id = :id AND user_id = :user_id AND status = 'pending'";
        $stmt =$this->conn->prepare($sql);$stmt->execute([':id' => $id, ':user_id' =>$userId]);
        return $stmt->rowCount() > 0;
    }

    public function getAllLeaveRequests($storeId = null, $status = 'all') {$sql = "SELECT lr.*, u.employee_code, u.full_name, u.role, st.store_name
                FROM leave_requests lr
                JOIN users u ON lr.user_id = u.id
                JOIN stores st ON lr.store_id = st.id
                WHERE 1=1";
        $params = [];

        if (!empty($storeId)) {$sql .= " AND lr.store_id = :store_id";
            $params[':store_id'] = (int)$storeId;
        }
        if ($status !== 'all') {$sql .= " AND lr.status = :status";
            $params[':status'] =$status;
        }

        $sql .= " ORDER BY FIELD(lr.status, 'pending', 'approved', 'rejected'), lr.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function reviewLeaveRequest($id, $status,$adminNote, $storeScope = null) {$sql = "UPDATE leave_requests 
                SET status = :status, admin_note = :admin_note 
                WHERE id = :id";
        $params = [
            ':id'         => $id,
            ':status'     => $status,
            ':admin_note' => $adminNote !== '' ?$adminNote : null
        ];
        if (!empty($storeScope)) {$sql .= " AND store_id = :store_id";
            $params[':store_id'] =$storeScope;
        }
        return $this->conn->prepare($sql)->execute($params);
    }
}