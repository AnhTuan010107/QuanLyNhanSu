<?php
require_once __DIR__ . '/../models/ClientModel.php';

class ClientController {
    private $clientModel;

    public function __construct() {
        if (!isset($_SESSION['user'])) {
            header("Location: " . BASE_URL . "/login");
            exit;
        }
        if (in_array($_SESSION['user']['role'], ['Quản lý', 'Trưởng ca (TC)'])) {
            header("Location: " . BASE_URL . "/admin/dashboard");
            exit;
        }
        $this->clientModel = new ClientModel();
    }

    // Trang chủ Cổng Nhân Viên (/client/home) - Giao diện Bootstrap trước đó
    public function home() {
        $userId       = (int) $_SESSION['user']['id'];
        $currentMonth = date('Y-m');

        $profile      = $this->clientModel->getUserProfile($userId);
        $allSchedules = $this->clientModel->getUserSchedules($userId);
        $payrolls     = $this->clientModel->getUserPayrolls($userId);
        $estimate     = $this->clientModel->getMonthlyApprovedEstimate(
            $userId,
            $currentMonth,
            (float) ($profile['hourly_rate'] ?? 0)
        );

        $approvedSchedules = [];
        $otherSchedules    = [];
        foreach ($allSchedules as $sc) {
            if ($sc['status'] === 'approved') {
                $approvedSchedules[] = $sc;
            } else {
                $otherSchedules[] = $sc;
            }
        }

        require_once __DIR__ . '/../views/client/home.php';
    }

    // Trang Đăng ký ca làm riêng (/client/schedules) - Giao diện theo mẫu ảnh
    public function registerPage() {
        $userId  = (int) $_SESSION['user']['id'];
        $storeId = (int) $_SESSION['user']['store_id'];
        $profile = $this->clientModel->getUserProfile($userId);

        $weekOffset = isset($_GET['week']) ? (int) $_GET['week'] : 1;
        if (!in_array($weekOffset, [0, 1, 2])) {
            $weekOffset = 1;
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

        $selectedWeek = $weeks[$weekOffset];

        // Hạn đăng ký: 15h thứ Sáu trước tuần đang chọn
        $deadlineTs   = strtotime('-3 days', $selectedWeek['start_ts']);
        $deadlineText = '15h thứ Sáu ' . date('d/m/Y', $deadlineTs) . '.';

        // Thẻ Hôm nay & Ngày mai
        $todayInfo    = $this->clientModel->getDayScheduleSummary($userId, $storeId, date('Y-m-d'));
        $tomorrowInfo = $this->clientModel->getDayScheduleSummary($userId, $storeId, date('Y-m-d', strtotime('+1 day')));

        // Dữ liệu tuần đang chọn
        $weekData = $this->clientModel->getWeekSlotsMap(
            $userId,
            $selectedWeek['start_date'],
            $selectedWeek['end_date']
        );

        // Dữ liệu tuần liền trước cho nút "Giống tuần dd/mm"
        $prevMondayTs  = strtotime('-7 days', $selectedWeek['start_ts']);
        $prevSundayTs  = strtotime('+6 days', $prevMondayTs);
        $prevWeekLabel = date('d/m', $prevMondayTs);
        $prevWeekData  = $this->clientModel->getWeekSlotsMap(
            $userId,
            date('Y-m-d', $prevMondayTs),
            date('Y-m-d', $prevSundayTs)
        );

        $dayCodes = ['T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'CN'];
        $weekDays = [];
        $prevWeekSlotsByIndex = [];

        for ($d = 0; $d < 7; $d++) {
            $curTs    = strtotime("+{$d} days", $selectedWeek['start_ts']);
            $curDate  = date('Y-m-d', $curTs);
            $prevDate = date('Y-m-d', strtotime("+{$d} days", $prevMondayTs));

            $saved = $weekData['days'][$curDate] ?? ['slots' => [], 'locked' => false];
            $weekDays[] = [
                'code'       => $dayCodes[$d],
                'date_short' => date('d/m', $curTs),
                'date_full'  => $curDate,
                'slots'      => $saved['slots'],
                'locked'     => $saved['locked'],
                'summary'    => $this->clientModel->getDayScheduleSummary($userId, $storeId, $curDate)
            ];

            $prevWeekSlotsByIndex[$d] = $prevWeekData['days'][$prevDate]['slots'] ?? [];
        }

        require_once __DIR__ . '/../views/client/register_shift.php';
    }

    public function saveAvailability() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASE_URL . "/client/schedules");
            exit;
        }

        $userId      = (int) $_SESSION['user']['id'];
        $storeId     = (int) $_SESSION['user']['store_id'];
        $weekOffset  = (int) ($_POST['week_offset'] ?? 1);
        $weekStart   = trim($_POST['week_start'] ?? '');
        $weekEnd     = trim($_POST['week_end'] ?? '');
        $generalNote = trim($_POST['general_note'] ?? '');
        $daysInput   = $_POST['days'] ?? [];

        if (empty($weekStart) || empty($weekEnd)) {
            $_SESSION['error'] = "Dữ liệu tuần đăng ký không hợp lệ!";
            header("Location: " . BASE_URL . "/client/schedules?week=" . $weekOffset);
            exit;
        }

        $daysData   = [];
        $totalSlots = 0;
        foreach ($daysInput as $date => $info) {
            $slots = [];
            if (!empty($info['start1']) && !empty($info['end1'])) {
                if ($info['end1'] <= $info['start1']) {
                    $_SESSION['error'] = "Giờ kết thúc phải lớn hơn giờ bắt đầu (ngày " . date('d/m', strtotime($date)) . ").";
                    header("Location: " . BASE_URL . "/client/schedules?week=" . $weekOffset);
                    exit;
                }
                $slots[] = ['start' => $info['start1'], 'end' => $info['end1']];
                $totalSlots++;
            }
            if (!empty($info['start2']) && !empty($info['end2'])) {
                if ($info['end2'] <= $info['start2']) {
                    $_SESSION['error'] = "Giờ kết thúc khoảng 2 phải lớn hơn giờ bắt đầu (ngày " . date('d/m', strtotime($date)) . ").";
                    header("Location: " . BASE_URL . "/client/schedules?week=" . $weekOffset);
                    exit;
                }
                $slots[] = ['start' => $info['start2'], 'end' => $info['end2']];
                $totalSlots++;
            }
            $daysData[$date] = $slots;
        }

        if ($this->clientModel->saveWeeklyAvailability($userId, $storeId, $weekStart, $weekEnd, $daysData, $generalNote)) {
            $_SESSION['success'] = $totalSlots > 0
                ? "Đã gửi đăng ký giờ rảnh tuần " . date('d/m', strtotime($weekStart)) . " – " . date('d/m', strtotime($weekEnd)) . " thành công!"
                : "Đã cập nhật trạng thái nghỉ cho tuần " . date('d/m', strtotime($weekStart)) . " – " . date('d/m', strtotime($weekEnd)) . ".";
        } else {
            $_SESSION['error'] = "Không thể lưu giờ rảnh. Vui lòng kiểm tra danh mục ca làm trong hệ thống.";
        }

        header("Location: " . BASE_URL . "/client/schedules?week=" . $weekOffset);
        exit;
    }

    public function cancelShift() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASE_URL . "/client/home");
            exit;
        }

        $scheduleId = (int) ($_POST['id'] ?? 0);
        $userId     = (int) $_SESSION['user']['id'];

        if ($scheduleId > 0 && $this->clientModel->cancelPendingSchedule($scheduleId, $userId)) {
            $_SESSION['success'] = "Đã hủy đăng ký ca chờ duyệt.";
        } else {
            $_SESSION['error'] = "Không thể hủy ca này (chỉ hủy được ca đang ở trạng thái Chờ duyệt).";
        }

        header("Location: " . BASE_URL . "/client/home");
        exit;
    }

    public function updateProfile() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASE_URL . "/client/home#profile");
            exit;
        }

        $userId   = (int) $_SESSION['user']['id'];
        $phone    = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!preg_match('/^0[0-9]{9}$/', $phone)) {
            $_SESSION['error'] = "Số điện thoại phải gồm 10 chữ số và bắt đầu bằng số 0.";
            header("Location: " . BASE_URL . "/client/home#profile");
            exit;
        }

        if (!empty($password) && strlen($password) < 6) {
            $_SESSION['error'] = "Mật khẩu mới phải có ít nhất 6 ký tự.";
            header("Location: " . BASE_URL . "/client/home#profile");
            exit;
        }

        $hashed = !empty($password) ? password_hash($password, PASSWORD_DEFAULT) : null;
        if ($this->clientModel->updateProfile($userId, $phone, $hashed)) {
            $_SESSION['success'] = "Cập nhật thông tin cá nhân thành công!";
        } else {
            $_SESSION['error'] = "Không thể cập nhật thông tin cá nhân.";
        }

        header("Location: " . BASE_URL . "/client/home#profile");
        exit;
    }
}