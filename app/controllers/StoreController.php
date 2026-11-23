<?php
require_once __DIR__ . '/../models/StoreModel.php';

class StoreController {
    private $storeModel;

    public function __construct() {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Quản lý') {
            header("Location: " . BASE_URL . "/admin/dashboard");
            exit;
        }
        $this->storeModel = new StoreModel();
    }

    public function index() {
        $stores = $this->storeModel->getAll();
        require_once __DIR__ . '/../views/admin/stores.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASE_URL . "/admin/stores");
            exit;
        }

        $storeName = trim($_POST['store_name'] ?? '');
        $address   = trim($_POST['address'] ?? '');
        $phone     = trim($_POST['phone'] ?? '');

        if (empty($storeName) || empty($address) || empty($phone)) {
            $_SESSION['error'] = "Vui lòng nhập đầy đủ thông tin cửa hàng!";
            header("Location: " . BASE_URL . "/admin/stores");
            exit;
        }

        if ($this->storeModel->create($storeName, $address, $phone)) {
            $_SESSION['success'] = "Thêm cửa hàng mới thành công!";
        } else {
            $_SESSION['error'] = "Có lỗi xảy ra khi thêm cửa hàng.";
        }
        header("Location: " . BASE_URL . "/admin/stores");
        exit;
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASE_URL . "/admin/stores");
            exit;
        }

        $id        = (int) ($_POST['id'] ?? 0);
        $storeName = trim($_POST['store_name'] ?? '');
        $address   = trim($_POST['address'] ?? '');
        $phone     = trim($_POST['phone'] ?? '');
        $status    = isset($_POST['status']) ? (int) $_POST['status'] : 1;

        if ($id <= 0 || empty($storeName) || empty($address) || empty($phone)) {
            $_SESSION['error'] = "Dữ liệu cập nhật không hợp lệ!";
            header("Location: " . BASE_URL . "/admin/stores");
            exit;
        }

        if ($this->storeModel->update($id, $storeName, $address, $phone, $status)) {
            $_SESSION['success'] = "Cập nhật thông tin cửa hàng thành công!";
        } else {
            $_SESSION['error'] = "Không thể cập nhật cửa hàng.";
        }
        header("Location: " . BASE_URL . "/admin/stores");
        exit;
    }

    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASE_URL . "/admin/stores");
            exit;
        }

        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            header("Location: " . BASE_URL . "/admin/stores");
            exit;
        }

        if ($this->storeModel->countUsersInStore($id) > 0) {
            $_SESSION['error'] = "Không thể xóa cửa hàng đang có nhân viên trực thuộc! Hãy chuyển nhân viên sang cửa hàng khác hoặc đổi sang trạng thái Ngừng hoạt động.";
            header("Location: " . BASE_URL . "/admin/stores");
            exit;
        }

        if ($this->storeModel->delete($id)) {
            $_SESSION['success'] = "Đã xóa cửa hàng thành công!";
        } else {
            $_SESSION['error'] = "Không thể xóa cửa hàng này.";
        }
        header("Location: " . BASE_URL . "/admin/stores");
        exit;
    }
}