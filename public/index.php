<?php
session_start();

define('BASE_URL', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'));

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/AppModel.php';
require_once __DIR__ . '/../controllers/AuthController.php';

$page = isset($_GET['page']) ? $_GET['page'] : 'login';
$auth = new AuthController();

if ($page === 'login') {
    $auth->login();
} elseif ($page === 'logout') {
    $auth->logout();
} elseif ($page === 'home') {
    if (!isset($_SESSION['user'])) {
        header("Location: " . BASE_URL . "/?page=login");
        exit;
    }

    $appModel = new AppModel();
    $userId = $_SESSION['user']['id'];
    $storeId = isset($_SESSION['user']['store_id']) ? $_SESSION['user']['store_id'] : 1;
    $role = isset($_SESSION['user']['role']) && $_SESSION['user']['role'] !== ''
        ? $_SESSION['user']['role']
        : (isset($_SESSION['user']['position']) && $_SESSION['user']['position'] !== '' ? $_SESSION['user']['position'] : 'Nhân viên');

    $isManager = ($role === 'Quản lý');
    $isSup     = ($role === 'Trưởng ca (TC)');

    $defaultTab = $isManager ? 'branches' : 'overview';
    $tab = isset($_GET['tab']) ? $_GET['tab'] : $defaultTab;

    $attMsg = "";
    $opMsg  = "";

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        $action = $_POST['action'];

        if ($action === 'check_in') {
            $appModel->checkInUser($userId, $storeId);
            header("Location: " . BASE_URL . "/?page=home&tab=attendance&msg=checkin");
            exit;
        } elseif ($action === 'check_out') {
            $attId = isset($_POST['attendance_id']) ? (int)$_POST['attendance_id'] : 0;
            if ($attId > 0) {
                $appModel->checkOutUser($attId, $userId);
            }
            header("Location: " . BASE_URL . "/?page=home&tab=attendance&msg=checkout");
            exit;
        } elseif ($action === 'create_leave') {
            $leaveDate = isset($_POST['leave_date']) ? $_POST['leave_date'] : '';
            $reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';
            if ($leaveDate !== '' && $reason !== '') {
                $appModel->createLeaveRequest($userId, $leaveDate, $reason);
                header("Location: " . BASE_URL . "/?page=home&tab=attendance&msg=leave_sent");
                exit;
            }
        } elseif ($action === 'save_availability') {
            $dates  = isset($_POST['work_date']) ? $_POST['work_date'] : [];
            $starts = isset($_POST['start_hour']) ? $_POST['start_hour'] : [];
            $ends   = isset($_POST['end_hour']) ? $_POST['end_hour'] : [];
            $slots  = isset($_POST['slot_type']) ? $_POST['slot_type'] : [];
            $note   = isset($_POST['avail_note']) ? trim($_POST['avail_note']) : "";
            $weekOffset = isset($_POST['week']) ? (int)$_POST['week'] : 1;

            $appModel->saveUserAvailabilities($userId, $dates, $starts, $ends, $slots, $note);
            header("Location: " . BASE_URL . "/?page=home&tab=shifts&week=" . $weekOffset . "&saved=1");
            exit;
        } elseif ($action === 'review_leave' && ($isSup || $isManager)) {
            $leaveId = isset($_POST['leave_id']) ? (int)$_POST['leave_id'] : 0;
            $newStatus = isset($_POST['new_status']) ? $_POST['new_status'] : '';
            $backTab = isset($_POST['back_tab']) ? $_POST['back_tab'] : 'leaves';
            if ($leaveId > 0) {
                $appModel->reviewLeaveRequest($leaveId, $userId, $newStatus);
            }
            header("Location: " . BASE_URL . "/?page=home&tab=" . urlencode($backTab) . "&opmsg=reviewed");
            exit;
        } elseif ($action === 'create_shift' && ($isSup || $isManager)) {
            $code  = isset($_POST['shift_code']) ? trim($_POST['shift_code']) : '';
            $name  = isset($_POST['shift_name']) ? trim($_POST['shift_name']) : '';
            $stime = isset($_POST['start_time']) ? $_POST['start_time'] : '';
            $etime = isset($_POST['end_time']) ? $_POST['end_time'] : '';
            if ($code !== '' && $name !== '' && $stime !== '' && $etime !== '') {
                $appModel->createShift($code, $name, $stime, $etime);
            }
            header("Location: " . BASE_URL . "/?page=home&tab=manage_shifts&opmsg=shift_created");
            exit;
        } elseif ($action === 'assign_schedule' && ($isSup || $isManager)) {
            $targetUid = isset($_POST['target_user_id']) ? (int)$_POST['target_user_id'] : 0;
            $shiftId   = isset($_POST['shift_id']) ? (int)$_POST['shift_id'] : 0;
            $workDate  = isset($_POST['work_date']) ? $_POST['work_date'] : '';
            $assignSid = isset($_POST['assign_store_id']) ? (int)$_POST['assign_store_id'] : $storeId;
            if ($targetUid > 0 && $shiftId > 0 && $workDate !== '') {
                $appModel->assignSchedule($targetUid, $assignSid, $shiftId, $workDate, $userId);
            }
            header("Location: " . BASE_URL . "/?page=home&tab=assign_schedule&opmsg=assigned");
            exit;
        } elseif ($action === 'add_staff' && ($isSup || $isManager)) {
            $fullName   = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
            $email      = isset($_POST['email']) ? trim($_POST['email']) : '';
            $password   = isset($_POST['password']) ? trim($_POST['password']) : '';
            $newRole    = isset($_POST['role']) ? trim($_POST['role']) : 'Nhân viên';
            $newStoreId = isset($_POST['store_id']) ? (int)$_POST['store_id'] : 0;
            $hourlyRate = isset($_POST['hourly_rate']) ? (float)$_POST['hourly_rate'] : 25000;

            if ($fullName === '' || $email === '' || $password === '') {
                header("Location: " . BASE_URL . "/?page=home&tab=staff_info&opmsg=missing_fields");
                exit;
            }

            if ($appModel->emailExists($email)) {
                header("Location: " . BASE_URL . "/?page=home&tab=staff_info&opmsg=email_exists");
                exit;
            }

            $appModel->createStaffAccount($fullName, $email, $password, $newRole, $newStoreId, $hourlyRate);
            header("Location: " . BASE_URL . "/?page=home&tab=staff_info&opmsg=staff_added");
            exit;
        } elseif ($action === 'update_staff' && ($isSup || $isManager)) {
            $targetUid  = isset($_POST['target_user_id']) ? (int)$_POST['target_user_id'] : 0;
            $fullName   = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
            $newRole    = isset($_POST['role']) ? trim($_POST['role']) : 'Nhân viên';
            $newStoreId = isset($_POST['store_id']) ? (int)$_POST['store_id'] : 0;
            $hourlyRate = isset($_POST['hourly_rate']) ? (float)$_POST['hourly_rate'] : 25000;
            $status     = isset($_POST['status']) ? trim($_POST['status']) : 'active';
            $backTab    = isset($_POST['back_tab']) ? $_POST['back_tab'] : 'staff_info';

            if ($targetUid > 0 && $fullName !== '') {
                $appModel->updateStaffInfo($targetUid, $fullName, $newRole, $newStoreId, $hourlyRate, $status);
            }
            header("Location: " . BASE_URL . "/?page=home&tab=" . urlencode($backTab) . "&opmsg=staff_updated");
            exit;
        } elseif ($action === 'delete_staff' && ($isSup || $isManager)) {
            $targetUid = isset($_POST['target_user_id']) ? (int)$_POST['target_user_id'] : 0;
            $backTab   = isset($_POST['back_tab']) ? $_POST['back_tab'] : 'staff_info';

            if ($targetUid > 0 && $targetUid !== (int)$userId) {
                $appModel->deleteStaffAccount($targetUid);
                header("Location: " . BASE_URL . "/?page=home&tab=" . urlencode($backTab) . "&opmsg=staff_deleted");
                exit;
            }

            header("Location: " . BASE_URL . "/?page=home&tab=" . urlencode($backTab) . "&opmsg=cannot_delete_self");
            exit;
        }
    }

    if (isset($_GET['msg'])) {
        if ($_GET['msg'] === 'checkin') $attMsg = "Đã Check-in thành công!";
        elseif ($_GET['msg'] === 'checkout') $attMsg = "Đã Check-out và tính giờ công!";
        elseif ($_GET['msg'] === 'leave_sent') $attMsg = "Đã gửi đơn xin nghỉ phép!";
    }

    if (isset($_GET['opmsg'])) {
        if ($_GET['opmsg'] === 'reviewed') $opMsg = "Đã cập nhật đơn nghỉ phép!";
        elseif ($_GET['opmsg'] === 'shift_created') $opMsg = "Đã thêm ca làm mới!";
        elseif ($_GET['opmsg'] === 'assigned') $opMsg = "Đã sắp xếp lịch ca làm!";
        elseif ($_GET['opmsg'] === 'staff_added') $opMsg = "Đã thêm nhân viên và tạo tài khoản Gmail thành công!";
        elseif ($_GET['opmsg'] === 'email_exists') $opMsg = "Gmail này đã tồn tại trong hệ thống, vui lòng dùng Gmail khác!";
        elseif ($_GET['opmsg'] === 'missing_fields') $opMsg = "Vui lòng nhập đầy đủ Họ tên, Gmail và Mật khẩu!";
        elseif ($_GET['opmsg'] === 'staff_updated') $opMsg = "Đã cập nhật thông tin nhân viên!";
        elseif ($_GET['opmsg'] === 'staff_deleted') $opMsg = "Đã xóa nhân viên khỏi hệ thống!";
        elseif ($_GET['opmsg'] === 'cannot_delete_self') $opMsg = "Không thể tự xóa tài khoản đang đăng nhập!";
    }

    $todayStr = date('Y-m-d');
    $tomorrowStr = date('Y-m-d', strtotime('+1 day'));

    $overview = $appModel->getOverviewData($userId);

    $shiftMsg = (isset($_GET['saved']) && $_GET['saved'] == '1') ? "Đã lưu giờ rảnh thành công!" : "";
    $weekOffset = isset($_GET['week']) ? (int)$_GET['week'] : 0;
    if ($weekOffset < 0 || $weekOffset > 2) {
        $weekOffset = 0;
    }

    $todaySummary = $appModel->getShiftDaySummary($todayStr, $userId);
    $tomorrowSummary = $appModel->getShiftDaySummary($tomorrowStr, $userId);

    $mondayTs = strtotime('monday this week') + ($weekOffset * 7 * 86400);
    $weekDays = [];
    $dayLabels = ['T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'CN'];
    for ($i = 0; $i < 7; $i++) {
        $ts = $mondayTs + ($i * 86400);
        $weekDays[] = [
            'label' => $dayLabels[$i],
            'date'  => date('Y-m-d', $ts),
            'dm'    => date('d/m', $ts)
        ];
    }

    $startWeek = $weekDays[0]['date'];
    $endWeek = $weekDays[6]['date'];

    $myWeekSchedules = $appModel->getUserWeekSchedules($userId, $startWeek, $endWeek);
    $selectedStoreDate = isset($_GET['cdate']) ? $_GET['cdate'] : $todayStr;
    $filterStoreId = isset($_GET['filter_store']) ? (int)$_GET['filter_store'] : 0;
    $storeTimeline = $appModel->getStoreDayTimeline($selectedStoreDate, $userId, $filterStoreId > 0 ? $filterStoreId : null);
    $userAvailMap = $appModel->getUserAvailabilities($userId, $startWeek, $endWeek);

    $selectedSchedMonth = isset($_GET['sched_month']) ? $_GET['sched_month'] : date('Y-m');
    $monthScheduleData = $appModel->getStoreMonthSchedules($selectedSchedMonth, $filterStoreId > 0 ? $filterStoreId : null);
    $monthSchedulesByDate = $monthScheduleData['by_date'];
    $monthStaffHours = $monthScheduleData['staff_summary'];

    $todayAttendance = $appModel->getTodayAttendance($userId);
    $attendanceList = $appModel->getUserAttendances($userId);
    $storeAttendances = $appModel->getStoreAttendances($selectedStoreDate, $filterStoreId > 0 ? $filterStoreId : null);
    $leaveList = $appModel->getLeaveRequests($userId, ($isSup || $isManager));

    $storesList = $appModel->getAllStores();
    $staffList = $appModel->getAllStaff($filterStoreId > 0 ? $filterStoreId : null);
    $allStaffList = $appModel->getAllStaff(null);
    $allShifts = $appModel->getAllShifts();
    $upcomingAvails = $appModel->getUpcomingAvailabilities();

    require_once __DIR__ . '/../views/home.php';
} else {
    http_response_code(404);
    echo "404 - Không tìm thấy trang!";
}