<?php
require_once __DIR__ . '/../config/Database.php';

class PayrollModel {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function getAllStores() {
        $sql = "SELECT id, store_name FROM stores ORDER BY store_name ASC";
        return $this->conn->query($sql)->fetchAll();
    }

    // Lấy danh sách bảng lương theo tháng và cửa hàng
    public function getPayrolls($monthYear, $storeId = null) {
        $sql = "SELECT p.id, p.user_id, p.month_year, p.total_hours, p.bonus, p.deduction,
                       p.final_salary, p.payment_status, p.created_at,
                       u.employee_code, u.full_name, u.role, u.hourly_rate,
                       s.store_name
                FROM payrolls p
                JOIN users u ON p.user_id = u.id
                JOIN stores s ON u.store_id = s.id
                WHERE p.month_year = :month_year";
        $params = [':month_year' => $monthYear];

        if (!empty($storeId)) {
            $sql .= " AND u.store_id = :store_id";
            $params[':store_id'] = (int) $storeId;
        }

        $sql .= " ORDER BY s.store_name ASC, u.employee_code ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // Tính & Chốt bảng lương tự động từ các ca đã duyệt (approved) trong tháng
    public function generateMonthlyPayrolls($monthYear, $storeId = null) {
        $userSql = "SELECT id, hourly_rate FROM users WHERE status = 'active'";
        $userParams = [];
        if (!empty($storeId)) {
            $userSql .= " AND store_id = :store_id";
            $userParams[':store_id'] = (int) $storeId;
        }
        $userStmt = $this->conn->prepare($userSql);
        $userStmt->execute($userParams);
        $users = $userStmt->fetchAll();

        // Câu lệnh tính tổng giờ quy đổi hệ số từ các ca đã duyệt trong tháng
        $hoursSql = "SELECT 
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
        $hoursStmt = $this->conn->prepare($hoursSql);

        $checkSql = "SELECT id, bonus, deduction, payment_status 
                     FROM payrolls 
                     WHERE user_id = :user_id AND month_year = :month_year LIMIT 1";
        $checkStmt = $this->conn->prepare($checkSql);

        $insertSql = "INSERT INTO payrolls (user_id, month_year, total_hours, bonus, deduction, final_salary, payment_status)
                      VALUES (:user_id, :month_year, :total_hours, 0, 0, :final_salary, 'unpaid')";
        $insertStmt = $this->conn->prepare($insertSql);

        $updateSql = "UPDATE payrolls 
                      SET total_hours = :total_hours, final_salary = :final_salary 
                      WHERE id = :id";
        $updateStmt = $this->conn->prepare($updateSql);

        $processedCount = 0;

        foreach ($users as $u) {
            $userId     = (int) $u['id'];
            $hourlyRate = (float) $u['hourly_rate'];

            $hoursStmt->execute([
                ':user_id'    => $userId,
                ':month_year' => $monthYear
            ]);
            $weightedHours = round((float) $hoursStmt->fetchColumn(), 2);

            $checkStmt->execute([
                ':user_id'    => $userId,
                ':month_year' => $monthYear
            ]);
            $existing = $checkStmt->fetch();

            if ($existing) {
                // Chỉ cập nhật lại giờ công nếu kỳ lương chưa thanh toán
                if ($existing['payment_status'] === 'unpaid') {
                    $baseSalary  = round($weightedHours * $hourlyRate);
                    $finalSalary = max(0, $baseSalary + (float) $existing['bonus'] - (float) $existing['deduction']);
                    $updateStmt->execute([
                        ':total_hours'  => $weightedHours,
                        ':final_salary' => $finalSalary,
                        ':id'           => $existing['id']
                    ]);
                    $processedCount++;
                }
            } else {
                // Chỉ tạo mới nếu nhân viên có phát sinh giờ làm trong tháng
                if ($weightedHours > 0) {
                    $finalSalary = round($weightedHours * $hourlyRate);
                    $insertStmt->execute([
                        ':user_id'      => $userId,
                        ':month_year'   => $monthYear,
                        ':total_hours'  => $weightedHours,
                        ':final_salary' => $finalSalary
                    ]);
                    $processedCount++;
                }
            }
        }

        return $processedCount;
    }

    // Cập nhật Thưởng / Khấu trừ và tính lại Thực lãnh
    public function updateBonusDeduction($id, $bonus, $deduction) {
        $sql = "SELECT p.total_hours, p.payment_status, u.hourly_rate
                FROM payrolls p
                JOIN users u ON p.user_id = u.id
                WHERE p.id = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        if (!$row || $row['payment_status'] === 'paid') {
            return false;
        }

        $baseSalary  = round((float) $row['total_hours'] * (float) $row['hourly_rate']);
        $finalSalary = max(0, $baseSalary + $bonus - $deduction);

        $upSql = "UPDATE payrolls 
                  SET bonus = :bonus, deduction = :deduction, final_salary = :final_salary 
                  WHERE id = :id";
        return $this->conn->prepare($upSql)->execute([
            ':bonus'        => $bonus,
            ':deduction'    => $deduction,
            ':final_salary' => $finalSalary,
            ':id'           => $id
        ]);
    }

    // Chuyển trạng thái thanh toán (paid <-> unpaid)
    public function togglePaymentStatus($id) {
        $sql = "UPDATE payrolls 
                SET payment_status = CASE WHEN payment_status = 'paid' THEN 'unpaid' ELSE 'paid' END
                WHERE id = :id";
        return $this->conn->prepare($sql)->execute([':id' => $id]);
    }

    // Đánh dấu đã thanh toán tất cả trong tháng/cửa hàng đang lọc
    public function markAllPaid($monthYear, $storeId = null) {
        $sql = "UPDATE payrolls p
                JOIN users u ON p.user_id = u.id
                SET p.payment_status = 'paid'
                WHERE p.month_year = :month_year AND p.payment_status = 'unpaid'";
        $params = [':month_year' => $monthYear];

        if (!empty($storeId)) {
            $sql .= " AND u.store_id = :store_id";
            $params[':store_id'] = (int) $storeId;
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    // Xóa một dòng bảng lương chưa thanh toán
    public function delete($id) {
        $sql = "DELETE FROM payrolls WHERE id = :id AND payment_status = 'unpaid'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }
}