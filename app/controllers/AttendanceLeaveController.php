<?php
require_once __DIR__ . '/../models/AttendanceLeaveModel.php';

class AttendanceLeaveController {
    private $model;

    public function __construct() {
        if (!isset($_SESSION['user'])) {
            header("Location: " . BASE_URL . "/login");
            exit;
        }
        $this->model = new AttendanceLeaveModel();
    }

    // ==================== PHÍA NHÂN VIÊN ====================

    public function clientIndex() {
        if (in_array($_SESSION['user']['role'], ['Quản lý', 'Trưởng ca (TC)'])) {
            header("Location: " . BASE_URL . "/admin/attendance");
            exit;
        }

        $userId        = (int) $_SESSION['user']['id'];
        $today         = date('Y-m-d');
        $selectedMonth = $_GET['month'] ?? date('Y-m');
        if (!preg_match('/^\d{4}-\d{2}$/', $selectedMonth)) {
            $selectedMonth = date('Y-m');
        }

        $todayShifts      = $this->model->getTodayApprovedShifts($userId, $today);
        $todayAttendances = $this->model->getTodayAttendances($userId, $today);
        $history          = $this->model->getUserAttendanceHistory($userId, $selectedMonth);
        $leaveRequests    = $this->model->getUserLeaveRequests($userId);

        require_once __DIR__ . '/../views/client/attendance_leave.php';
    }

    public function clientCheckIn() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId     = (int) $_SESSION['user']['id'];
            $storeId    = (int) $_SESSION['user']['store_id'];
            $scheduleId = !empty($_POST['schedule_id']) ? (int) $_POST['schedule_id'] : null;

            if ($this->model->checkIn($userId, $storeId, $scheduleId)) {
                $_SESSION['success'] = "Check-in vào ca thành công lúc " . date('H:i') . "!";
            } else {
                $_SESSION['error'] = "Bạn đã check-in cho ca làm này rồi.";
            }
        }
        header("Location: " . BASE_URL . "/client/attendance");
        exit;
    }

    public function clientCheckOut() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId       = (int) $_SESSION['user']['id'];
            $attendanceId = (int) ($_POST['attendance_id'] ?? 0);

            if ($attendanceId > 0 && $this->model->checkOut($attendanceId, $userId)) {
                $_SESSION['success'] = "Check-out tan ca thành công lúc " . date('H:i') . "!";
            } else {
                $_SESSION['error'] = "Không thể check-out hoặc ca này đã kết thúc.";
            }
        }
        header("Location: " . BASE_URL . "/client/attendance");
        exit;
    }

    public function clientSubmitLeave() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId    = (int) $_SESSION['user']['id'];
            $storeId   = (int) $_SESSION['user']['store_id'];
            $leaveType = trim($_POST['leave_type'] ?? '');
            $startDate = trim($_POST['start_date'] ?? '');
            $endDate   = trim($_POST['end_date'] ?? '');
            $reason    = trim($_POST['reason'] ?? '');

            if ($leaveType === '' || $startDate === '' || $endDate === '' || $reason === '') {
                $_SESSION['error'] = "Vui lòng điền đầy đủ thông tin đơn xin nghỉ phép.";
            } elseif ($endDate < $startDate) {
                $_SESSION['error'] = "Ngày kết thúc nghỉ không được nhỏ hơn ngày bắt đầu.";
            } elseif ($this->model->createLeaveRequest($userId, $storeId, $leaveType, $startDate, $endDate, $reason)) {
                $_SESSION['success'] = "Đã gửi đơn xin nghỉ phép thành công! Vui lòng chờ Quản lý / Trưởng ca duyệt.";
            } else {
                $_SESSION['error'] = "Không thể gửi đơn xin nghỉ phép.";
            }
        }
        header("Location: " . BASE_URL . "/client/attendance#leave-tab");
        exit;
    }

    public function clientCancelLeave() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id     = (int) ($_POST['id'] ?? 0);
            $userId = (int) $_SESSION['user']['id'];

            if ($id > 0 && $this->model->cancelUserLeaveRequest($id, $userId)) {
                $_SESSION['success'] = "Đã hủy đơn xin nghỉ phép.";
            } else {
                $_SESSION['error'] = "Chỉ có thể hủy đơn đang ở trạng thái Chờ duyệt.";
            }
        }
        header("Location: " . BASE_URL . "/client/attendance#leave-tab");
        exit;
    }

    // ==================== PHÍA QUẢN LÝ & TRƯỞNG CA ====================

    private function checkAdminAccess() {
        if (!in_array($_SESSION['user']['role'], ['Quản lý', 'Trưởng ca (TC)'])) {
            header("Location: " . BASE_URL . "/client/home");
            exit;
        }
    }

    public function adminIndex() {
        $this->checkAdminAccess();

        $isManager   = ($_SESSION['user']['role'] === 'Quản lý');
        $userStoreId = (int) $_SESSION['user']['store_id'];

        $selectedDate = $_GET['date'] ?? date('Y-m-d');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $selectedDate)) {
            $selectedDate = date('Y-m-d');
        }

        if ($isManager) {
            $selectedStoreId = isset($_GET['store_id']) && $_GET['store_id'] !== ''
                ? (int) $_GET['store_id']
                : null;
        } else {
            $selectedStoreId = $userStoreId;
        }

        $leaveStatus = $_GET['leave_status'] ?? 'all';
        if (!in_array($leaveStatus, ['all', 'pending', 'approved', 'rejected'])) {
            $leaveStatus = 'all';
        }

        $attendances   = $this->model->getAttendancesByDate($selectedDate, $selectedStoreId);
        $leaveRequests = $this->model->getAllLeaveRequests($selectedStoreId, $leaveStatus);
        $stores        = $this->model->getAllStores();
        $employees     = $this->model->getActiveEmployees($selectedStoreId);

        $pendingLeavesCount = 0;
        foreach ($leaveRequests as $lr) {
            if ($lr['status'] === 'pending') {
                $pendingLeavesCount++;
            }
        }

        $activePage = 'attendance';
        require_once __DIR__ . '/../views/admin/attendance_leave.php';
    }

    public function adminStoreAttendance() {
        $this->checkAdminAccess();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $isManager   = ($_SESSION['user']['role'] === 'Quản lý');
            $userStoreId = (int) $_SESSION['user']['store_id'];

            $userId   = (int) ($_POST['user_id'] ?? 0);
            $workDate = trim($_POST['work_date'] ?? date('Y-m-d'));
            $checkIn  = trim($_POST['check_in'] ?? '');
            $checkOut = trim($_POST['check_out'] ?? '');
            $status   = trim($_POST['status'] ?? 'manual');
            $note     = trim($_POST['note'] ?? '');

            $employees = $this->model->getActiveEmployees($isManager ? null : $userStoreId);
            $targetStoreId = 0;
            foreach ($employees as $emp) {
                if ((int) $emp['id'] === $userId) {
                    $targetStoreId = (int) $emp['store_id'];
                    break;
                }
            }

            if ($userId <= 0 || $targetStoreId <= 0) {
                $_SESSION['error'] = "Vui lòng chọn nhân viên hợp lệ.";
            } elseif ($this->model->createManualAttendance($userId, $targetStoreId, $workDate, $checkIn, $checkOut, $status, $note)) {
                $_SESSION['success'] = "Đã ghi nhận dữ liệu chấm công cho nhân viên!";
            } else {
                $_SESSION['error'] = "Không thể lưu dữ liệu chấm công.";
            }
        }
        $this->redirectAdminBack();
    }

    public function adminUpdateAttendance() {
        $this->checkAdminAccess();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $isManager = ($_SESSION['user']['role'] === 'Quản lý');
            $scope     = $isManager ? null : (int) $_SESSION['user']['store_id'];

            $id       = (int) ($_POST['id'] ?? 0);
            $checkIn  = trim($_POST['check_in'] ?? '');
            $checkOut = trim($_POST['check_out'] ?? '');
            $status   = trim($_POST['status'] ?? 'on_time');
            $note     = trim($_POST['note'] ?? '');

            if ($id > 0 && $this->model->updateAttendance($id, $checkIn, $checkOut, $status, $note, $scope)) {
                $_SESSION['success'] = "Đã cập nhật bản ghi chấm công!";
            } else {
                $_SESSION['error'] = "Không thể cập nhật bản ghi chấm công.";
            }
        }
        $this->redirectAdminBack();
    }

    public function adminDeleteAttendance() {
        $this->checkAdminAccess();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $isManager = ($_SESSION['user']['role'] === 'Quản lý');
            $scope     = $isManager ? null : (int) $_SESSION['user']['store_id'];
            $id        = (int) ($_POST['id'] ?? 0);

            if ($id > 0 && $this->model->deleteAttendance($id, $scope)) {
                $_SESSION['success'] = "Đã xóa bản ghi chấm công.";
            } else {
                $_SESSION['error'] = "Không thể xóa bản ghi chấm công.";
            }
        }
        $this->redirectAdminBack();
    }

    public function adminReviewLeave() {
        $this->checkAdminAccess();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $isManager = ($_SESSION['user']['role'] === 'Quản lý');
            $scope     = $isManager ? null : (int) $_SESSION['user']['store_id'];

            $id        = (int) ($_POST['id'] ?? 0);
            $status    = $_POST['status'] ?? '';
            $adminNote = trim($_POST['admin_note'] ?? '');

            if ($id > 0 && in_array($status, ['approved', 'rejected']) && $this->model->reviewLeaveRequest($id, $status, $adminNote, $scope)) {
                $_SESSION['success'] = $status === 'approved'
                    ? "Đã duyệt đơn xin nghỉ phép!"
                    : "Đã từ chối đơn xin nghỉ phép.";
            } else {
                $_SESSION['error'] = "Không thể xử lý đơn xin nghỉ phép này.";
            }
        }
        $this->redirectAdminBack('#leaves');
    }

    private function redirectAdminBack($hash = '') {
        $query = http_build_query([
            'date'         => $_POST['filter_date'] ?? date('Y-m-d'),
            'store_id'     => $_POST['filter_store_id'] ?? '',
            'leave_status' => $_POST['filter_leave_status'] ?? 'all'
        ]);
        header("Location: " . BASE_URL . "/admin/attendance?" . $query . $hash);
        exit;
    }
}