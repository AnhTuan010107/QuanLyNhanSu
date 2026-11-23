<?php
require_once __DIR__ . '/../models/ScheduleModel.php';

class ScheduleController {
    private $scheduleModel;
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

        $this->scheduleModel = new ScheduleModel();
        $this->isManager     = ($_SESSION['user']['role'] === 'Quản lý');
        $this->userStoreId   = (int) $_SESSION['user']['store_id'];
    }

    public function index() {
        $weekOffset = isset($_GET['week']) ? (int) $_GET['week'] : 1;
        if (!in_array($weekOffset, [0, 1, 2])) {
            $weekOffset = 1;
        }

        if ($this->isManager) {
            $selectedStoreId = isset($_GET['store_id']) && $_GET['store_id'] !== ''
                ? (int) $_GET['store_id']
                : null;
        } else {
            $selectedStoreId = $this->userStoreId;
        }

        $selectedStatus = $_GET['status'] ?? 'all';
        if (!in_array($selectedStatus, ['all', 'pending', 'approved', 'rejected'])) {
            $selectedStatus = 'all';
        }

        $thisMondayTs = strtotime('monday this week');
        $weeks = [];
        $weekTitles = ['Tuần này', 'Tuần sau', 'Tuần sau nữa'];
        for ($i = 0; $i < 3; $i++) {
            $startTs = strtotime('+' . ($i * 7) . ' days', $thisMondayTs);
            $endTs   = strtotime('+6 days', $startTs);
            $weeks[$i] = [
                'offset'     => $i,
                'title'      => $weekTitles[$i],
                'range_text' => date('d/m', $startTs) . ' – ' . date('d/m', $endTs),
                'start_date' => date('Y-m-d', $startTs),
                'end_date'   => date('Y-m-d', $endTs),
                'start_ts'   => $startTs
            ];
        }

        $currentWeek = $weeks[$weekOffset];

        $schedules = $this->scheduleModel->getSchedules([
            'store_id'   => $selectedStoreId,
            'start_date' => $currentWeek['start_date'],
            'end_date'   => $currentWeek['end_date'],
            'status'     => $selectedStatus
        ]);

        $allWeekSchedules = $this->scheduleModel->getSchedules([
            'store_id'   => $selectedStoreId,
            'start_date' => $currentWeek['start_date'],
            'end_date'   => $currentWeek['end_date'],
            'status'     => 'all'
        ]);

        $stats = ['total' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0];
        $matrixByDate = [];
        foreach ($allWeekSchedules as $item) {
            $stats['total']++;
            if (isset($stats[$item['status']])) {
                $stats[$item['status']]++;
            }
            if ($item['status'] !== 'rejected') {
                $matrixByDate[$item['work_date']][] = $item;
            }
        }

        $dayCodes = ['Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7', 'Chủ Nhật'];
        $weekDays = [];
        for ($d = 0; $d < 7; $d++) {
            $ts = strtotime("+{$d} days", $currentWeek['start_ts']);
            $dateStr = date('Y-m-d', $ts);
            $weekDays[] = [
                'label'     => $dayCodes[$d],
                'date_text' => date('d/m', $ts),
                'date_full' => $dateStr,
                'items'     => $matrixByDate[$dateStr] ?? []
            ];
        }

        $stores    = $this->scheduleModel->getAllStores();
        $shifts    = $this->scheduleModel->getAllShifts();
        $employees = $this->scheduleModel->getActiveEmployees($selectedStoreId);

        $isManager = $this->isManager;
        require_once __DIR__ . '/../views/admin/schedules.php';
    }

    public function approve() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id      = (int) ($_POST['id'] ?? 0);
            $shiftId = !empty($_POST['shift_id']) ? (int) $_POST['shift_id'] : null;
            $scope   = $this->isManager ? null : $this->userStoreId;

            if ($id > 0 && $this->scheduleModel->approveSchedule($id, $shiftId, $scope)) {
                $_SESSION['success'] = "Đã duyệt và xếp ca làm việc thành công!";
            } else {
                $_SESSION['error'] = "Không thể duyệt ca làm việc này.";
            }
        }
        $this->redirectBack();
    }

    public function reject() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id    = (int) ($_POST['id'] ?? 0);
            $scope = $this->isManager ? null : $this->userStoreId;

            if ($id > 0 && $this->scheduleModel->rejectSchedule($id, $scope)) {
                $_SESSION['success'] = "Đã từ chối đăng ký ca làm.";
            } else {
                $_SESSION['error'] = "Không thể từ chối ca làm việc này.";
            }
        }
        $this->redirectBack();
    }

    public function approveAll() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $startDate = trim($_POST['start_date'] ?? '');
            $endDate   = trim($_POST['end_date'] ?? '');
            $storeId   = $this->isManager
                ? (!empty($_POST['store_id']) ? (int) $_POST['store_id'] : null)
                : $this->userStoreId;

            if ($startDate !== '' && $endDate !== '') {
                $count = $this->scheduleModel->approveAllPending($startDate, $endDate, $storeId);
                $_SESSION['success'] = "Đã duyệt hàng loạt {$count} đăng ký giờ rảnh!";
            }
        }
        $this->redirectBack();
    }

    public function assign() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId   = (int) ($_POST['user_id'] ?? 0);
            $shiftId  = (int) ($_POST['shift_id'] ?? 0);
            $workDate = trim($_POST['work_date'] ?? '');
            $noteText = trim($_POST['note'] ?? '');

            $employees = $this->scheduleModel->getActiveEmployees($this->isManager ? null : $this->userStoreId);
            $targetStoreId = 0;
            foreach ($employees as $emp) {
                if ((int) $emp['id'] === $userId) {
                    $targetStoreId = (int) $emp['store_id'];
                    break;
                }
            }

            if ($userId <= 0 or $shiftId <= 0 or $workDate === '' or $targetStoreId <= 0) {
                $_SESSION['error'] = "Vui lòng chọn đầy đủ nhân viên, ngày làm và ca làm việc.";
            } elseif ($this->scheduleModel->assignDirectSchedule($userId, $targetStoreId, $shiftId, $workDate, $noteText)) {
                $_SESSION['success'] = "Đã xếp ca trực tiếp cho nhân viên thành công!";
            } else {
                $_SESSION['error'] = "Không thể xếp ca. Vui lòng thử lại.";
            }
        }
        $this->redirectBack();
    }

    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id    = (int) ($_POST['id'] ?? 0);
            $scope = $this->isManager ? null : $this->userStoreId;

            if ($id > 0 && $this->scheduleModel->deleteSchedule($id, $scope)) {
                $_SESSION['success'] = "Đã xóa lịch làm việc khỏi hệ thống.";
            } else {
                $_SESSION['error'] = "Không thể xóa lịch làm việc này.";
            }
        }
        $this->redirectBack();
    }

    private function redirectBack() {
        $week   = (int) ($_POST['week_offset'] ?? 1);
        $store  = $_POST['filter_store_id'] ?? '';
        $status = $_POST['filter_status'] ?? 'all';
        $query  = http_build_query([
            'week'     => $week,
            'store_id' => $store,
            'status'   => $status
        ]);
        header("Location: " . BASE_URL . "/admin/schedules?" . $query);
        exit;
    }
}