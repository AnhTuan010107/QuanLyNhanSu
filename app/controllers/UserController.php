<?php
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/StoreModel.php';

class UserController {
    private $userModel;
    private $storeModel;

    public function __construct() {
        if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['Quản lý', 'Trưởng ca (TC)'])) {
            header("Location: " . BASE_URL . "/login");
            exit;
        }
        $this->userModel = new UserModel();
        $this->storeModel = new StoreModel();
    }

    public function index() {
        $currentUser = $_SESSION['user'];
        $storeFilter = ($currentUser['role'] === 'Trưởng ca (TC)') ? (int) $currentUser['store_id'] : null;

        $users  = $this->userModel->getAll($storeFilter);
        $stores = $this->storeModel->getActiveStores();

        require_once __DIR__ . '/../views/admin/users.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASE_URL . "/admin/users");
            exit;
        }

        $currentUser  = $_SESSION['user'];
        $employeeCode = strtoupper(trim($_POST['employee_code'] ?? ''));
        $fullName     = trim($_POST['full_name'] ?? '');
        $email        = trim($_POST['email'] ?? '');
        $password     = $_POST['password'] ?? '';
        $phone        = trim($_POST['phone'] ?? '');
        $hourlyRate   = (float) ($_POST['hourly_rate'] ?? 25000);
        $inputRole    = trim($_POST['role'] ?? 'Nhân viên PartTime');

        // Trưởng ca (TC) chỉ được thêm Nhân viên PartTime hoặc Học Việc tại cửa hàng của mình
        if ($currentUser['role'] === 'Trưởng ca (TC)') {
            $storeId = (int) $currentUser['store_id'];
            $role    = in_array($inputRole, ['Nhân viên PartTime', 'Học Việc']) ? $inputRole : 'Nhân viên PartTime';
        } else {
            $storeId = (int) ($_POST['store_id'] ?? 0);
            $role    = in_array($inputRole, ['Quản lý', 'Trưởng ca (TC)', 'Nhân viên PartTime', 'Học Việc']) ? $inputRole : 'Nhân viên PartTime';
        }

        if (empty($employeeCode) || empty($fullName) || empty($email) || empty($password) || empty($phone) || $storeId <= 0) {
            $_SESSION['error'] = "Vui lòng nhập đầy đủ thông tin bắt buộc!";
            header("Location: " . BASE_URL . "/admin/users");
            exit;
        }

        if ($this->userModel->existsEmployeeCode($employeeCode)) {
            $_SESSION['error'] = "Mã nhân viên '$employeeCode' đã tồn tại!";
            header("Location: " . BASE_URL . "/admin/users");
            exit;
        }

        if ($this->userModel->existsEmail($email)) {
            $_SESSION['error'] = "Email '$email' đã được sử dụng!";
            header("Location: " . BASE_URL . "/admin/users");
            exit;
        }

        $created = $this->userModel->create([
            'store_id'      => $storeId,
            'employee_code' => $employeeCode,
            'full_name'     => $fullName,
            'email'         => $email,
            'password'      => password_hash($password, PASSWORD_DEFAULT),
            'phone'         => $phone,
            'role'          => $role,
            'hourly_rate'   => $hourlyRate
        ]);

        if ($created) {
            $_SESSION['success'] = "Thêm hồ sơ nhân viên thành công!";
        } else {
            $_SESSION['error'] = "Có lỗi xảy ra khi thêm nhân viên.";
        }
        header("Location: " . BASE_URL . "/admin/users");
        exit;
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASE_URL . "/admin/users");
            exit;
        }

        $currentUser  = $_SESSION['user'];
        $id           = (int) ($_POST['id'] ?? 0);
        $employeeCode = strtoupper(trim($_POST['employee_code'] ?? ''));
        $fullName     = trim($_POST['full_name'] ?? '');
        $email        = trim($_POST['email'] ?? '');
        $password     = $_POST['password'] ?? '';
        $phone        = trim($_POST['phone'] ?? '');
        $hourlyRate   = (float) ($_POST['hourly_rate'] ?? 25000);
        $status       = isset($_POST['status']) ? (int) $_POST['status'] : 1;
        $inputRole    = trim($_POST['role'] ?? 'Nhân viên PartTime');

        if ($currentUser['role'] === 'Trưởng ca (TC)') {
            $storeId = (int) $currentUser['store_id'];
            $role    = in_array($inputRole, ['Nhân viên PartTime', 'Học Việc']) ? $inputRole : 'Nhân viên PartTime';
        } else {
            $storeId = (int) ($_POST['store_id'] ?? 0);
            $role    = in_array($inputRole, ['Quản lý', 'Trưởng ca (TC)', 'Nhân viên PartTime', 'Học Việc']) ? $inputRole : 'Nhân viên PartTime';
        }

        if ($id <= 0 || empty($employeeCode) || empty($fullName) || empty($email) || empty($phone) || $storeId <= 0) {
            $_SESSION['error'] = "Dữ liệu cập nhật không hợp lệ!";
            header("Location: " . BASE_URL . "/admin/users");
            exit;
        }

        if ($id === (int) $currentUser['id'] && $status === 0) {
            $_SESSION['error'] = "Không thể khóa tài khoản đang đăng nhập!";
            header("Location: " . BASE_URL . "/admin/users");
            exit;
        }

        if ($this->userModel->existsEmployeeCode($employeeCode, $id)) {
            $_SESSION['error'] = "Mã nhân viên '$employeeCode' đã thuộc về nhân sự khác!";
            header("Location: " . BASE_URL . "/admin/users");
            exit;
        }

        if ($this->userModel->existsEmail($email, $id)) {
            $_SESSION['error'] = "Email '$email' đã thuộc về nhân sự khác!";
            header("Location: " . BASE_URL . "/admin/users");
            exit;
        }

        $updated = $this->userModel->update($id, [
            'store_id'      => $storeId,
            'employee_code' => $employeeCode,
            'full_name'     => $fullName,
            'email'         => $email,
            'password'      => !empty($password) ? password_hash($password, PASSWORD_DEFAULT) : '',
            'phone'         => $phone,
            'role'          => $role,
            'hourly_rate'   => $hourlyRate,
            'status'        => $status
        ]);

        if ($updated) {
            $_SESSION['success'] = "Cập nhật hồ sơ nhân viên thành công!";
        } else {
            $_SESSION['error'] = "Không thể cập nhật hồ sơ nhân viên.";
        }
        header("Location: " . BASE_URL . "/admin/users");
        exit;
    }

    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASE_URL . "/admin/users");
            exit;
        }

        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            header("Location: " . BASE_URL . "/admin/users");
            exit;
        }

        if ($id === (int) $_SESSION['user']['id']) {
            $_SESSION['error'] = "Không thể xóa tài khoản đang đăng nhập!";
            header("Location: " . BASE_URL . "/admin/users");
            exit;
        }

        if ($this->userModel->delete($id)) {
            $_SESSION['success'] = "Đã xóa hồ sơ nhân viên thành công!";
        } else {
            $_SESSION['error'] = "Không thể xóa nhân viên này.";
        }
        header("Location: " . BASE_URL . "/admin/users");
        exit;
    }
}