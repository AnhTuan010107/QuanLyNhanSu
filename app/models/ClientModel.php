<?php
require_once __DIR__ . '/../config/Database.php';

class ClientModel {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function getUserProfile($userId) {
        $sql = "SELECT u.id, u.store_id, u.employee_code, u.full_name, u.email, 
                       u.phone, u.role, u.hourly_rate, u.status, u.created_at,
                       s.store_name, s.address AS store_address, s.phone AS store_phone
                FROM users u
                JOIN stores s ON u.store_id = s.id
                WHERE u.id = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $userId]);
        return $stmt->fetch();
    }

    public function updateProfile($userId, $phone, $hashedPassword = null) {
        if (!empty($hashedPassword)) {
            $sql = "UPDATE users SET phone = :phone, password = :password WHERE id = :id";
            $params = [':id' => $userId, ':phone' => $phone, ':password' => $hashedPassword];
        } else {
            $sql = "UPDATE users SET phone = :phone WHERE id = :id";
            $params = [':id' => $userId, ':phone' => $phone];
        }
        return $this->conn->prepare($sql)->execute($params);
    }

    public function getAllShifts() {
        $sql = "SELECT id, shift_name, start_time, end_time, coefficient 
                FROM shifts ORDER BY start_time ASC";
        return $this->conn->query($sql)->fetchAll();
    }

    public function getUserSchedules($userId) {
        $sql = "SELECT sc.id, sc.work_date, sc.status, sc.note, sc.created_at,
                       sh.shift_name, sh.start_time, sh.end_time, sh.coefficient,
                       st.store_name
                FROM schedules sc
                JOIN shifts sh ON sc.shift_id = sh.id
                JOIN stores st ON sc.store_id = st.id
                WHERE sc.user_id = :user_id
                ORDER BY sc.work_date DESC, sh.start_time ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function cancelPendingSchedule($scheduleId, $userId) {
        $sql = "DELETE FROM schedules 
                WHERE id = :id AND user_id = :user_id AND status = 'pending'";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':id'      => $scheduleId,
            ':user_id' => $userId
        ]);
    }

    public function getDayScheduleSummary($userId, $storeId, $date) {
        $sql = "SELECT sc.id, sc.status, sc.note, sh.shift_name, sh.start_time, sh.end_time
                FROM schedules sc
                JOIN shifts sh ON sc.shift_id = sh.id
                WHERE sc.user_id = :user_id 
                  AND sc.work_date = :work_date
                  AND sc.status != 'rejected'
                ORDER BY FIELD(sc.status, 'approved', 'pending'), sh.start_time ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':user_id' => $userId, ':work_date' => $date]);
        $rows = $stmt->fetchAll();

        if (empty($rows)) {
            return null;
        }

        $coSql = "SELECT DISTINCT u.full_name
                  FROM schedules sc
                  JOIN users u ON sc.user_id = u.id
                  WHERE sc.store_id = :store_id
                    AND sc.work_date = :work_date
                    AND sc.user_id != :user_id
                    AND sc.status = 'approved'";
        $coStmt = $this->conn->prepare($coSql);
        $coStmt->execute([
            ':store_id'  => $storeId,
            ':work_date' => $date,
            ':user_id'   => $userId
        ]);
        $coworkers = [];
        foreach ($coStmt->fetchAll() as $cw) {
            $parts = preg_split('/\s+/', trim($cw['full_name']));
            $coworkers[] = count($parts) >= 2
                ? $parts[count($parts) - 2] . ' ' . $parts[count($parts) - 1]
                : $cw['full_name'];
        }

        $timeRanges = [];
        $status = 'pending';
        foreach ($rows as $r) {
            if ($r['status'] === 'approved') {
                $status = 'approved';
            }
            $timeRanges[] = $this->extractDisplayRange($r);
        }

        return [
            'time_text' => implode(', ', $timeRanges),
            'status'    => $status,
            'coworkers' => $coworkers
        ];
    }

    public function getWeekSlotsMap($userId, $startDate, $endDate) {
        $sql = "SELECT sc.id, sc.work_date, sc.status, sc.note, sh.start_time, sh.end_time
                FROM schedules sc
                JOIN shifts sh ON sc.shift_id = sh.id
                WHERE sc.user_id = :user_id
                  AND sc.work_date BETWEEN :start_date AND :end_date
                  AND sc.status != 'rejected'
                ORDER BY sc.work_date ASC, sc.id ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':user_id'    => $userId,
            ':start_date' => $startDate,
            ':end_date'   => $endDate
        ]);

        $map = [];
        $userNote = '';
        foreach ($stmt->fetchAll() as $row) {
            $d = $row['work_date'];
            if (!isset($map[$d])) {
                $map[$d] = ['slots' => [], 'locked' => false];
            }
            if ($row['status'] === 'approved') {
                $map[$d]['locked'] = true;
            }

            if (!empty($row['note']) && preg_match('/^\[(\d{2}:\d{2})-(\d{2}:\d{2})\]\s*(.*)$/u', $row['note'], $m)) {
                $map[$d]['slots'][] = ['start' => $m[1], 'end' => $m[2]];
                if (!empty($m[3]) && empty($userNote)) {
                    $userNote = $m[3];
                }
            } else {
                $map[$d]['slots'][] = [
                    'start' => substr($row['start_time'], 0, 5),
                    'end'   => substr($row['end_time'], 0, 5)
                ];
                if (!empty($row['note']) && empty($userNote)) {
                    $userNote = $row['note'];
                }
            }
        }

        return ['days' => $map, 'note' => $userNote];
    }

    public function saveWeeklyAvailability($userId, $storeId, $weekStart, $weekEnd, $daysData, $generalNote) {
        $shifts = $this->getAllShifts();
        if (empty($shifts)) {
            return false;
        }

        $delSql = "DELETE FROM schedules 
                   WHERE user_id = :user_id 
                     AND work_date BETWEEN :start_date AND :end_date 
                     AND status = 'pending'";
        $this->conn->prepare($delSql)->execute([
            ':user_id'    => $userId,
            ':start_date' => $weekStart,
            ':end_date'   => $weekEnd
        ]);

        $insSql = "INSERT INTO schedules (user_id, store_id, shift_id, work_date, status, note)
                   VALUES (:user_id, :store_id, :shift_id, :work_date, 'pending', :note)";
        $insStmt = $this->conn->prepare($insSql);

        foreach ($daysData as $date => $slots) {
            if ($date < $weekStart or $date > $weekEnd) {
                continue;
            }
            foreach ($slots as $slot) {
                $start = $slot['start'] ?? '';
                $end   = $slot['end'] ?? '';
                if (empty($start) or empty($end) or $end <= $start) {
                    continue;
                }

                $shiftId = $this->matchShiftId($shifts, $start);
                $noteStr = "[{$start}-{$end}]" . ($generalNote !== '' ? ' ' . $generalNote : '');

                $insStmt->execute([
                    ':user_id'   => $userId,
                    ':store_id'  => $storeId,
                    ':shift_id'  => $shiftId,
                    ':work_date' => $date,
                    ':note'      => $noteStr
                ]);
            }
        }
        return true;
    }

    private function matchShiftId($shifts, $startTime) {
        $hour = (int) substr($startTime, 0, 2);
        foreach ($shifts as $sh) {
            $shStart = (int) substr($sh['start_time'], 0, 2);
            $shEnd   = (int) substr($sh['end_time'], 0, 2);
            if ($shEnd === 0) $shEnd = 24;
            if ($hour >= $shStart && $hour < $shEnd) {
                return (int) $sh['id'];
            }
        }
        return (int) $shifts[0]['id'];
    }

    private function extractDisplayRange($row) {
        if (!empty($row['note']) && preg_match('/^\[(\d{2}):00-(\d{2}):00\]/', $row['note'], $m)) {
            return (int)$m[1] . 'h–' . (int)$m[2] . 'h';
        }
        if (!empty($row['note']) && preg_match('/^\[(\d{2}:\d{2})-(\d{2}:\d{2})\]/', $row['note'], $m)) {
            return $m[1] . '–' . $m[2];
        }
        $s = (int) substr($row['start_time'], 0, 2);
        $e = (int) substr($row['end_time'], 0, 2);
        return "{$s}h–{$e}h";
    }

    public function getMonthlyApprovedEstimate($userId, $monthYear, $hourlyRate) {
        $sql = "SELECT 
                    COUNT(sc.id) AS approved_shifts,
                    COALESCE(SUM(
                        (CASE 
                            WHEN sh.end_time >= sh.start_time 
                                THEN TIMESTAMPDIFF(MINUTE, sh.start_time, sh.end_time)
                            ELSE TIMESTAMPDIFF(MINUTE, sh.start_time, ADDTIME(sh.end_time, '24:00:00'))
                        END / 60.0)
                    ), 0) AS raw_hours,
                    COALESCE(SUM(
                        (CASE 
                            WHEN sh.end_time >= sh.start_time 
                                THEN TIMESTAMPDIFF(MINUTE, sh.start_time, sh.end_time)
                            ELSE TIMESTAMPDIFF(MINUTE, sh.start_time, ADDTIME(sh.end_time, '24:00:00'))
                        END / 60.0) * sh.coefficient
                    ), 0) AS weighted_hours
                FROM schedules sc
                JOIN shifts sh ON sc.shift_id = sh.id
                WHERE sc.user_id = :user_id
                  AND sc.status = 'approved'
                  AND DATE_FORMAT(sc.work_date, '%Y-%m') = :month_year";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':user_id' => $userId, ':month_year' => $monthYear]);
        $row = $stmt->fetch();

        return [
            'approved_shifts'  => (int) ($row['approved_shifts'] ?? 0),
            'raw_hours'        => round((float) ($row['raw_hours'] ?? 0), 2),
            'weighted_hours'   => round((float) ($row['weighted_hours'] ?? 0), 2),
            'estimated_salary' => round((float) ($row['weighted_hours'] ?? 0) * $hourlyRate)
        ];
    }

    public function getUserPayrolls($userId) {
        $sql = "SELECT id, month_year, total_hours, bonus, deduction, final_salary, payment_status, created_at
                FROM payrolls WHERE user_id = :user_id ORDER BY month_year DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }
}