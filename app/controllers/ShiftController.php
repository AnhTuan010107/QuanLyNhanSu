<?php
require_once __DIR__ . '/../models/ShiftModel.php';

class ShiftController {
    private $shiftModel;

    public function __construct() {
        if (!isset($_SESSION['user'])) {
            header("Location: " . BASE_URL . "/login");
            exit;
        }

        if ($_SESSION['user']['role'] !== 'Quản lý') {
            $_SESSION['error'] = "Chỉ Quản lý mới có quyền chỉnh sửa danh mục ca làm.";
            header("Location: " . BASE_URL . "/admin/dashboard");
            exit;
        }

        $this->shiftModel = new ShiftModel();
    }

    public function index() {
        $shifts = $this->shiftModel->getAllShifts();
        require_once __DIR__ . '/../views/admin/shifts.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASE_URL . "/admin/shifts");
            exit;
        }

        $shiftName   = trim($_POST['shift_name'] ?? '');
        $startTime   = trim($_POST['start_time'] ?? '');
        $endTime     = trim($_POST['end_time'] ?? '');
        $coefficient = (float) ($_POST['coefficient'] ?? 1.0);

        if ($shiftName === '' || $startTime === '' || $endTime === '') {
            $_SESSION['error'] = "Vui lòng nhập đầy đủ tên ca, giờ bắt đầu và giờ kết thúc.";
            header("Location: " . BASE_URL . "/admin/shifts");
            exit;
        }

        if ($startTime === $endTime) {
            $_SESSION['error'] = "Giờ kết thúc không được trùng với giờ bắt đầu.";
            header("Location: " . BASE_URL . "/admin/shifts");
            exit;
        }

        if ($coefficient <= 0 || $coefficient > 5) {
            $_SESSION['error'] = "Hệ số lương phải lớn hơn 0 và không vượt quá 5.0.";
            header("Location: " . BASE_URL . "/admin/shifts");
            exit;
        }

        if ($this->shiftModel->create($shiftName, $startTime, $endTime, $coefficient)) {
            $_SESSION['success'] = "Đã thêm ca làm \"{$shiftName}\" thành công!";
        } else {
            $_SESSION['error'] = "Không thể thêm ca làm mới.";
        }

        header("Location: " . BASE_URL . "/admin/shifts");
        exit;
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASE_URL . "/admin/shifts");
            exit;
        }

        $id          = (int) ($_POST['id'] ?? 0);
        $shiftName   = trim($_POST['shift_name'] ?? '');
        $startTime   = trim($_POST['start_time'] ?? '');
        $endTime     = trim($_POST['end_time'] ?? '');
        $coefficient = (float) ($_POST['coefficient'] ?? 1.0);

        if ($id <= 0 || $shiftName === '' || $startTime === '' || $endTime === '') {
            $_SESSION['error'] = "Dữ liệu cập nhật ca làm không hợp lệ.";
            header("Location: " . BASE_URL . "/admin/shifts");
            exit;
        }

        if ($startTime === $endTime) {
            $_SESSION['error'] = "Giờ kết thúc không được trùng với giờ bắt đầu.";
            header("Location: " . BASE_URL . "/admin/shifts");
            exit;
        }

        if ($coefficient <= 0 || $coefficient > 5) {
            $_SESSION['error'] = "Hệ số lương phải lớn hơn 0 và không vượt quá 5.0.";
            header("Location: " . BASE_URL . "/admin/shifts");
            exit;
        }

        if ($this->shiftModel->update($id, $shiftName, $startTime, $endTime, $coefficient)) {
            $_SESSION['success'] = "Đã cập nhật ca làm \"{$shiftName}\" thành công!";
        } else {
            $_SESSION['error'] = "Không thể cập nhật ca làm.";
        }

        header("Location: " . BASE_URL . "/admin/shifts");
        exit;
    }

    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASE_URL . "/admin/shifts");
            exit;
        }

        $id = (int) ($_POST['id'] ?? 0);

        if ($id <= 0) {
            $_SESSION['error'] = "Ca làm không tồn tại.";
        } elseif ($this->shiftModel->isShiftInUse($id)) {
            $_SESSION['error'] = "Không thể xóa ca làm này vì đã có lịch đăng ký/phân công của nhân viên gắn với ca.";
        } elseif ($this->shiftModel->delete($id)) {
            $_SESSION['success'] = "Đã xóa ca làm khỏi danh mục.";
        } else {
            $_SESSION['error'] = "Không thể xóa ca làm.";
        }

        header("Location: " . BASE_URL . "/admin/shifts");
        exit;
    }
}