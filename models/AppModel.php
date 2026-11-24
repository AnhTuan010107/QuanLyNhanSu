<?php
class AppModel {
    private $db;

    public function __construct() {
        $this->db = DB::connect();
    }

    public function findUserByEmail($email) {
        $sql = "SELECT * FROM users WHERE email = :email AND status = 'active' LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => $email]);
        return $stmt->fetch();
    }

    public function getUserById($userId) {
        $sql = "SELECT u.*, s.store_name, s.address AS store_address
                FROM users u
                LEFT JOIN stores s ON u.store_id = s.id
                WHERE u.id = :uid LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':uid' => (int)$userId]);
        return $stmt->fetch();
    }

    public function getOverviewData($userId) {
        $sqlToday = "SELECT s.shift_code, s.shift_name, s.start_time, s.end_time
                     FROM schedules sc
                     JOIN shifts s ON sc.shift_id = s.id
                     WHERE sc.user_id = :uid AND sc.work_date = CURDATE()
                     ORDER BY s.start_time ASC LIMIT 1";
        $stmtToday = $this->db->prepare($sqlToday);
        $stmtToday->execute([':uid' => $userId]);
        $todayShift = $stmtToday->fetch();

        $sqlPending = "SELECT COUNT(*) AS total FROM leave_requests WHERE user_id = :uid AND status = 'pending'";
        $stmtPending = $this->db->prepare($sqlPending);
        $stmtPending->execute([':uid' => $userId]);
        $pendingRow = $stmtPending->fetch();
        $pendingLeaves = $pendingRow['total'];

        $sqlAtt = "SELECT SUM(work_hours) AS total_hours, COUNT(DISTINCT work_date) AS work_days
                   FROM attendances
                   WHERE user_id = :uid AND DATE_FORMAT(work_date, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')";
        $stmtAtt = $this->db->prepare($sqlAtt);
        $stmtAtt->execute([':uid' => $userId]);
        $attRow = $stmtAtt->fetch();

        $currentHours = $attRow['total_hours'] ? $attRow['total_hours'] : 0;
        $workDays = $attRow['work_days'] ? $attRow['work_days'] : 0;

        $sqlPay = "SELECT net_salary, total_hours FROM payrolls
                   WHERE user_id = :uid AND period_month = DATE_FORMAT(CURDATE(), '%Y-%m') LIMIT 1";
        $stmtPay = $this->db->prepare($sqlPay);
        $stmtPay->execute([':uid' => $userId]);
        $payRow = $stmtPay->fetch();

        $sqlRate = "SELECT hourly_rate FROM users WHERE id = :uid LIMIT 1";
        $stmtRate = $this->db->prepare($sqlRate);
        $stmtRate->execute([':uid' => $userId]);
        $rateRow = $stmtRate->fetch();
        $hourlyRate = $rateRow['hourly_rate'] ? $rateRow['hourly_rate'] : 25000;

        if ($payRow) {
            $estSalary = $payRow['net_salary'];
            if ($payRow['total_hours'] > 0) {
                $currentHours = $payRow['total_hours'];
            }
        } else {
            $estSalary = $currentHours * $hourlyRate;
        }

        $targetHours = 150;
        $remainingHours = $targetHours - $currentHours;
        if ($remainingHours < 0) {
            $remainingHours = 0;
        }

        $percent = ($currentHours / $targetHours) * 100;
        if ($percent > 100) {
            $percent = 100;
        }

        return [
            'today_shift'     => $todayShift,
            'pending_leaves'  => $pendingLeaves,
            'target_hours'    => $targetHours,
            'current_hours'   => $currentHours,
            'remaining_hours' => $remainingHours,
            'percent'         => $percent,
            'work_days'       => $workDays,
            'est_salary'      => $estSalary,
            'hourly_rate'     => $hourlyRate
        ];
    }

    public function getShiftDaySummary($dateStr, $userId) {
        $sql = "SELECT u.id, u.full_name, s.shift_code, s.start_time, s.end_time
                FROM schedules sc
                JOIN users u ON sc.user_id = u.id
                JOIN shifts s ON sc.shift_id = s.id
                WHERE sc.work_date = :wdate AND sc.status != 'rejected'
                ORDER BY s.start_time ASC, u.full_name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':wdate' => $dateStr]);
        $rows = $stmt->fetchAll();

        $mates = [];
        $myRange = "";

        foreach ($rows as $r) {
            $range = substr($r['start_time'], 0, 2) . "h-" . substr($r['end_time'], 0, 2) . "h";
            if ((int)$r['id'] === (int)$userId) {
                $myRange = $range;
            } else {
                $mates[] = $r['full_name'];
            }
        }

        return [
            'my_range' => $myRange,
            'mates'    => $mates
        ];
    }

    public function getUserWeekSchedules($userId, $startDate, $endDate) {
        $sql = "SELECT sc.work_date, s.shift_code, s.shift_name, s.start_time, s.end_time
                FROM schedules sc
                JOIN shifts s ON sc.shift_id = s.id
                WHERE sc.user_id = :uid
                  AND sc.work_date BETWEEN :sdate AND :edate
                  AND sc.status != 'rejected'
                ORDER BY sc.work_date ASC, s.start_time ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':uid'   => $userId,
            ':sdate' => $startDate,
            ':edate' => $endDate
        ]);
        $rows = $stmt->fetchAll();

        $byDate = [];
        foreach ($rows as $r) {
            $d = $r['work_date'];
            if (!isset($byDate[$d])) {
                $byDate[$d] = [];
            }
            $byDate[$d][] = $r;
        }
        return $byDate;
    }

    public function getStoreDayTimeline($dateStr, $userId, $storeId = null) {
        $sql = "SELECT u.id, u.full_name, u.role, s.shift_code, s.start_time, s.end_time, st.store_name
                FROM schedules sc
                JOIN users u ON sc.user_id = u.id
                JOIN shifts s ON sc.shift_id = s.id
                LEFT JOIN stores st ON sc.store_id = st.id
                WHERE sc.work_date = :wdate AND sc.status != 'rejected'";
        $params = [':wdate' => $dateStr];

        if ($storeId) {
            $sql .= " AND sc.store_id = :sid";
            $params[':sid'] = (int)$storeId;
        }

        $sql .= " ORDER BY s.start_time ASC, u.id ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        $people = [];
        foreach ($rows as $r) {
            $uid = $r['id'];
            if (!isset($people[$uid])) {
                $people[$uid] = [
                    'id'         => $uid,
                    'full_name'  => $r['full_name'],
                    'role'       => $r['role'],
                    'store_name' => $r['store_name'],
                    'is_me'      => ((int)$uid === (int)$userId),
                    'ranges'     => [],
                    'blocks'     => []
                ];
            }
            $startH = (int)substr($r['start_time'], 0, 2);
            $endH   = (int)substr($r['end_time'], 0, 2);
            $people[$uid]['ranges'][] = $startH . "h-" . $endH . "h";
            $people[$uid]['blocks'][] = ['start' => $startH, 'end' => $endH];
        }
        return array_values($people);
    }

    public function getStoreMonthSchedules($periodMonth, $storeId = null) {
        $startDate = $periodMonth . "-01";
        $endDate   = date("Y-m-t", strtotime($startDate));

        $sql = "SELECT sc.work_date, sc.status,
                       u.id AS user_id, u.full_name, u.role,
                       s.shift_code, s.start_time, s.end_time,
                       st.store_name,
                       COALESCE((
                           SELECT SUM(a.work_hours)
                           FROM attendances a
                           WHERE a.user_id = u.id
                             AND DATE_FORMAT(a.work_date, '%Y-%m') = :pm
                       ), 0) AS month_hours
                FROM schedules sc
                JOIN users u ON sc.user_id = u.id
                JOIN shifts s ON sc.shift_id = s.id
                LEFT JOIN stores st ON sc.store_id = st.id
                WHERE sc.work_date BETWEEN :sdate AND :edate
                  AND sc.status != 'rejected'";
        $params = [
            ':pm'    => $periodMonth,
            ':sdate' => $startDate,
            ':edate' => $endDate
        ];

        if ($storeId) {
            $sql .= " AND sc.store_id = :sid";
            $params[':sid'] = (int)$storeId;
        }

        $sql .= " ORDER BY sc.work_date ASC, s.start_time ASC, u.full_name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        $byDate = [];
        $staffHoursSummary = [];

        foreach ($rows as $r) {
            $d = $r['work_date'];
            if (!isset($byDate[$d])) {
                $byDate[$d] = [];
            }
            $byDate[$d][] = $r;

            $uid = $r['user_id'];
            if (!isset($staffHoursSummary[$uid])) {
                $staffHoursSummary[$uid] = [
                    'full_name'   => $r['full_name'],
                    'role'        => $r['role'],
                    'store_name'  => $r['store_name'],
                    'shift_count' => 0,
                    'month_hours' => (float)$r['month_hours']
                ];
            }
            $staffHoursSummary[$uid]['shift_count'] += 1;
        }

        return [
            'by_date'       => $byDate,
            'staff_summary' => array_values($staffHoursSummary)
        ];
    }

    public function getUserAvailabilities($userId, $startDate, $endDate) {
        $sql = "SELECT * FROM shift_availabilities
                WHERE user_id = :uid AND work_date BETWEEN :sdate AND :edate";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':uid'   => $userId,
            ':sdate' => $startDate,
            ':edate' => $endDate
        ]);
        $rows = $stmt->fetchAll();

        $map = [];
        foreach ($rows as $r) {
            $map[$r['work_date']] = $r;
        }
        return $map;
    }

    public function saveUserAvailabilities($userId, $dates, $starts, $ends, $slots, $note) {
        $sql = "INSERT INTO shift_availabilities (user_id, work_date, start_hour, end_hour, slot_type, note)
                VALUES (:uid, :wdate, :sh, :eh, :slot, :note)
                ON DUPLICATE KEY UPDATE
                    start_hour = VALUES(start_hour),
                    end_hour   = VALUES(end_hour),
                    slot_type  = VALUES(slot_type),
                    note       = VALUES(note)";
        $stmt = $this->db->prepare($sql);

        foreach ($dates as $i => $d) {
            $sh = (isset($starts[$i]) && $starts[$i] !== "") ? (int)$starts[$i] : null;
            $eh = (isset($ends[$i]) && $ends[$i] !== "") ? (int)$ends[$i] : null;
            $sl = isset($slots[$i]) ? trim($slots[$i]) : "";

            $stmt->execute([
                ':uid'   => $userId,
                ':wdate' => $d,
                ':sh'    => $sh,
                ':eh'    => $eh,
                ':slot'  => $sl,
                ':note'  => $note
            ]);
        }
    }

    public function getTodayAttendance($userId) {$sql = "SELECT * FROM attendances
                WHERE user_id = :uid AND work_date = CURDATE()
                ORDER BY id DESC LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':uid' =>$userId]);
        return $stmt->fetch();
    }

    public function getUserAttendances($userId) {$sql = "SELECT * FROM attendances
                WHERE user_id = :uid
                ORDER BY work_date DESC, id DESC LIMIT 10";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':uid' =>$userId]);
        return $stmt->fetchAll();
    }

    public function getStoreAttendances($dateStr, $storeId = null) {$sql = "SELECT a.*, u.full_name, u.role, s.store_name
                FROM attendances a
                JOIN users u ON a.user_id = u.id
                LEFT JOIN stores s ON a.store_id = s.id
                WHERE a.work_date = :wdate";
        $params = [':wdate' =>$dateStr];

        if ($storeId) {$sql .= " AND a.store_id = :sid";
            $params[':sid'] = (int)$storeId;
        }

        $sql .= " ORDER BY a.check_in DESC, u.full_name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function checkInUser($userId, $storeId) {$sId = $storeId ? (int)$storeId : 1;
        $sql = "INSERT INTO attendances (user_id, store_id, work_date, check_in, work_hours, status)
                VALUES (:uid, :sid, CURDATE(), NOW(), 0.00, 'missing_out')";
        $stmt =$this->db->prepare($sql);$stmt->execute([
            ':uid' => $userId,
            ':sid' => $sId
        ]);
    }

    public function checkOutUser($attendanceId, $userId) {$sql = "UPDATE attendances
                SET check_out = NOW(),
                    work_hours = ROUND(GREATEST(0, TIMESTAMPDIFF(MINUTE, check_in, NOW())) / 60, 2),
                    status = 'valid'
                WHERE id = :id AND user_id = :uid";
        $stmt =$this->db->prepare($sql);$stmt->execute([
            ':id'  => $attendanceId,
            ':uid' => $userId
        ]);
    }

    public function createLeaveRequest($userId,$leaveDate, $reason) {$sql = "INSERT INTO leave_requests (user_id, leave_date, reason, status)
                VALUES (:uid, :ldate, :reason, 'pending')";
        $stmt =$this->db->prepare($sql);$stmt->execute([
            ':uid'    => $userId,
            ':ldate'  => $leaveDate,
            ':reason' => $reason
        ]);
    }

    public function getLeaveRequests($userId,$canApprove) {
        if ($canApprove) {$sql = "SELECT lr.*, u.full_name
                    FROM leave_requests lr
                    JOIN users u ON lr.user_id = u.id
                    ORDER BY lr.created_at DESC LIMIT 20";
            $stmt =$this->db->prepare($sql);$stmt->execute();
            return $stmt->fetchAll();
        }

        $sql = "SELECT lr.*, u.full_name
                FROM leave_requests lr
                JOIN users u ON lr.user_id = u.id
                WHERE lr.user_id = :uid
                ORDER BY lr.created_at DESC LIMIT 15";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':uid' =>$userId]);
        return $stmt->fetchAll();
    }

    public function reviewLeaveRequest($leaveId, $reviewerId,$newStatus) {
        if ($newStatus !== 'approved' && $newStatus !== 'rejected') {
            return;
        }$sql = "UPDATE leave_requests
                SET status = :st, reviewed_by = :rev
                WHERE id = :id";
        $stmt =$this->db->prepare($sql);$stmt->execute([
            ':st'  => $newStatus,
            ':rev' => $reviewerId,
            ':id'  => $leaveId
        ]);
    }

    public function getAllStores() {
        $sql = "SELECT s.*,
                       (SELECT COUNT(*) FROM users u WHERE u.store_id = s.id AND u.status = 'active') AS staff_count
                FROM stores s
                ORDER BY s.id ASC";
        $stmt =$this->db->prepare($sql);$stmt->execute();
        return $stmt->fetchAll();
    }

    public function getAllStaff($storeId = null) {$sql = "SELECT u.*, s.store_name
                FROM users u
                LEFT JOIN stores s ON u.store_id = s.id";
        $params = [];

        if ($storeId) {$sql .= " WHERE u.store_id = :sid";
            $params[':sid'] = (int)$storeId;
        }

        $sql .= " ORDER BY u.id ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function emailExists($email) {$sql = "SELECT id FROM users WHERE email = :email LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' =>$email]);
        return (bool)$stmt->fetch();
    }

    public function createStaffAccount($fullName, $email,$plainPassword, $role,$storeId, $hourlyRate) {$hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);$sql = "INSERT INTO users (full_name, email, password, role, store_id, hourly_rate, status)
                VALUES (:fname, :email, :pass, :role, :sid, :rate, 'active')";
        $stmt =$this->db->prepare($sql);$stmt->execute([
            ':fname' => $fullName,
            ':email' => $email,
            ':pass'  => $hashedPassword,
            ':role'  => $role,
            ':sid'   => $storeId ? (int)$storeId : null,
            ':rate'  => $hourlyRate
        ]);
    }

    public function updateStaffInfo($targetUserId, $fullName,$role, $storeId,$hourlyRate, $status) {$sql = "UPDATE users
                SET full_name = :fname,
                    role = :role,
                    store_id = :sid,
                    hourly_rate = :rate,
                    status = :st
                WHERE id = :uid";
        $stmt =$this->db->prepare($sql);$stmt->execute([
            ':fname' => $fullName,
            ':role'  => $role,
            ':sid'   => $storeId ? (int)$storeId : null,
            ':rate'  => $hourlyRate,
            ':st'    => $status,
            ':uid'   => $targetUserId
        ]);
    }

    public function deleteStaffAccount($targetUserId) {
        $uid = (int)$targetUserId;
        $this->db->prepare("DELETE FROM shift_availabilities WHERE user_id = :uid")->execute([':uid' => $uid]);
        $this->db->prepare("DELETE FROM schedules WHERE user_id = :uid")->execute([':uid' => $uid]);
        $this->db->prepare("DELETE FROM attendances WHERE user_id = :uid")->execute([':uid' => $uid]);
        $this->db->prepare("DELETE FROM leave_requests WHERE user_id = :uid")->execute([':uid' => $uid]);
        $this->db->prepare("DELETE FROM payrolls WHERE user_id = :uid")->execute([':uid' => $uid]);
        $this->db->prepare("UPDATE leave_requests SET reviewed_by = NULL WHERE reviewed_by = :uid")->execute([':uid' => $uid]);
        $this->db->prepare("UPDATE schedules SET approved_by = NULL WHERE approved_by = :uid")->execute([':uid' => $uid]);
        $this->db->prepare("DELETE FROM users WHERE id = :uid")->execute([':uid' => $uid]);
    }

    public function getAllShifts() {
        $sql = "SELECT * FROM shifts ORDER BY start_time ASC";
        $stmt =$this->db->prepare($sql);$stmt->execute();
        return $stmt->fetchAll();
    }

    public function createShift($code, $name,$startTime, $endTime) {$sql = "INSERT INTO shifts (shift_code, shift_name, start_time, end_time)
                VALUES (:code, :name, :stime, :etime)";
        $stmt =$this->db->prepare($sql);$stmt->execute([
            ':code'  => $code,
            ':name'  => $name,
            ':stime' => $startTime,
            ':etime' => $endTime
        ]);
    }

    public function assignSchedule($userId,$storeId, $shiftId,$workDate, $approverId) {$sId = $storeId ? (int)$storeId : 1;
        $sql = "INSERT INTO schedules (user_id, store_id, shift_id, work_date, status, approved_by)
                VALUES (:uid, :sid, :shid, :wdate, 'approved', :app)
                ON DUPLICATE KEY UPDATE
                    status = 'approved',
                    approved_by = VALUES(approved_by)";
        $stmt =$this->db->prepare($sql);$stmt->execute([
            ':uid'   => $userId,
            ':sid'   => $sId,
            ':shid'  => $shiftId,
            ':wdate' => $workDate,
            ':app'   => $approverId
        ]);
    }

    public function getUpcomingAvailabilities() {
        $sql = "SELECT sa.*, u.full_name
                FROM shift_availabilities sa
                JOIN users u ON sa.user_id = u.id
                WHERE sa.work_date >= CURDATE() AND sa.slot_type != ''
                ORDER BY sa.work_date ASC, u.full_name ASC
                LIMIT 30";
        $stmt =$this->db->prepare($sql);$stmt->execute();
        return $stmt->fetchAll();
    }
}