<?php
require_once __DIR__ . '/../models/PayrollModel.php';

class PayrollController {
    private $payrollModel;
    private $isManager;
    private $userStoreId;

    public function __construct() {
        if (!isset($_SESSION['user'])) {
            header("Location: " . BASE_URL . "/login");
            exit;
        }

        if (!in_array($_SESSION['user']['role'], ['Quản lý', 'Trưởng ca (TC)'])) {
            header("Location: " . BASE_URL . "/client/home");
            exit;
        }

        $this->payrollModel = new PayrollModel();
        $this->isManager    = ($_SESSION['user']['role'] === 'Quản lý');
        $this->userStoreId  = (int) $_SESSION['user']['store_id'];
    }

    public function index() {
        $selectedMonth = $_GET['month'] ?? date('Y-m');
        if (!preg_match('/^\d{4}-\d{2}$/', $selectedMonth)) {
            $selectedMonth = date('Y-m');
        }

        if ($this->isManager) {
            $selectedStoreId = isset($_GET['store_id']) && $_GET['store_id'] !== ''
                ? (int) $_GET['store_id']
                : null;
        } else {
            $selectedStoreId = $this->userStoreId;
        }

        $payrolls = $this->payrollModel->getPayrolls($selectedMonth, $selectedStoreId);
        $stores   = $this->payrollModel->getAllStores();

        // Thống kê tổng hợp kỳ lương
        $summary = [
            'total_employees' => count($payrolls),
            'total_hours'     => 0,
            'total_salary'    => 0,
            'unpaid_count'    => 0
        ];

        foreach ($payrolls as $p) {
            $summary['total_hours']  += (float) $p['total_hours'];
            $summary['total_salary'] += (float) $p['final_salary'];
            if ($p['payment_status'] === 'unpaid') {
                $summary['unpaid_count']++;
            }
        }

        $isManager = $this->isManager;
        require_once __DIR__ . '/../views/admin/payrolls.php';
    }

    public function generate() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $month   = trim($_POST['month'] ?? date('Y-m'));
            $storeId = $this->isManager
                ? (!empty($_POST['store_id']) ? (int) $_POST['store_id'] : null)
                : $this->userStoreId;

            if (!$this->isManager) {
                $_SESSION['error'] = "Chỉ Quản lý mới có quyền chốt bảng lương tháng.";
            } else {
                $count = $this->payrollModel->generateMonthlyPayrolls($month, $storeId);
                $_SESSION['success'] = "Đã tính và cập nhật bảng lương tháng {$month} cho {$count} nhân viên có ca đã duyệt!";
            }
        }
        $this->redirectBack();
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->isManager) {
                $_SESSION['error'] = "Chỉ Quản lý mới có quyền điều chỉnh thưởng/khấu trừ.";
                $this->redirectBack();
            }

            $id        = (int) ($_POST['id'] ?? 0);
            $bonus     = max(0, (float) ($_POST['bonus'] ?? 0));
            $deduction = max(0, (float) ($_POST['deduction'] ?? 0));

            if ($id > 0 && $this->payrollModel->updateBonusDeduction($id, $bonus, $deduction)) {
                $_SESSION['success'] = "Đã cập nhật thưởng/khấu trừ và tính lại thực lãnh!";
            } else {
                $_SESSION['error'] = "Không thể cập nhật (phiếu lương đã thanh toán hoặc không tồn tại).";
            }
        }
        $this->redirectBack();
    }

    public function toggleStatus() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->isManager) {
                $_SESSION['error'] = "Chỉ Quản lý mới có quyền xác nhận thanh toán.";
                $this->redirectBack();
            }

            $id = (int) ($_POST['id'] ?? 0);
            if ($id > 0 && $this->payrollModel->togglePaymentStatus($id)) {
                $_SESSION['success'] = "Đã cập nhật trạng thái thanh toán lương!";
            } else {
                $_SESSION['error'] = "Không thể cập nhật trạng thái thanh toán.";
            }
        }
        $this->redirectBack();
    }

    public function markAllPaid() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->isManager) {
                $_SESSION['error'] = "Chỉ Quản lý mới có quyền xác nhận thanh toán.";
                $this->redirectBack();
            }

            $month   = trim($_POST['month'] ?? date('Y-m'));
            $storeId = !empty($_POST['store_id']) ? (int) $_POST['store_id'] : null;

            $count = $this->payrollModel->markAllPaid($month, $storeId);
            $_SESSION['success'] = "Đã xác nhận thanh toán lương cho {$count} nhân viên!";
        }
        $this->redirectBack();
    }

    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->isManager) {
                $_SESSION['error'] = "Chỉ Quản lý mới có quyền xóa phiếu lương.";
                $this->redirectBack();
            }

            $id = (int) ($_POST['id'] ?? 0);
            if ($id > 0 && $this->payrollModel->delete($id)) {
                $_SESSION['success'] = "Đã xóa phiếu lương chưa thanh toán.";
            } else {
                $_SESSION['error'] = "Chỉ có thể xóa phiếu lương đang ở trạng thái Chưa thanh toán.";
            }
        }
        $this->redirectBack();
    }

    private function redirectBack() {
        $month   = $_POST['month'] ?? date('Y-m');
        $storeId = $_POST['filter_store_id'] ?? '';
        $query   = http_build_query([
            'month'    => $month,
            'store_id' => $storeId
        ]);
        header("Location: " . BASE_URL . "/admin/payrolls?" . $query);
        exit;
    }
}