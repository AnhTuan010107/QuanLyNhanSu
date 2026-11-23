<?php
session_start();

define('BASE_URL', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'));

$url = isset($_GET['url']) ? trim($_GET['url'], '/') : 'login';

require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/controllers/AdminController.php';
require_once __DIR__ . '/../app/controllers/StoreController.php';
require_once __DIR__ . '/../app/controllers/UserController.php';
require_once __DIR__ . '/../app/controllers/ShiftController.php';
require_once __DIR__ . '/../app/controllers/ScheduleController.php';
require_once __DIR__ . '/../app/controllers/PayrollController.php';
require_once __DIR__ . '/../app/controllers/AttendanceLeaveController.php';
require_once __DIR__ . '/../app/controllers/ClientController.php';

switch ($url) {
    case '':
    case 'login':
        (new AuthController())->showLogin();
        break;

    case 'auth/login':
        (new AuthController())->handleLogin();
        break;

    case 'logout':
        (new AuthController())->logout();
        break;

    case 'admin/dashboard':
        (new AdminController())->dashboard();
        break;

    // Quản lý Cửa hàng
    case 'admin/stores':
        (new StoreController())->index();
        break;

    case 'admin/stores/store':
        (new StoreController())->store();
        break;

    case 'admin/stores/update':
        (new StoreController())->update();
        break;

    case 'admin/stores/delete':
        (new StoreController())->delete();
        break;

    // Quản lý Hồ sơ Nhân viên
    case 'admin/users':
        (new UserController())->index();
        break;

    case 'admin/users/store':
        (new UserController())->store();
        break;

    case 'admin/users/update':
        (new UserController())->update();
        break;

    case 'admin/users/delete':
        (new UserController())->delete();
        break;

    // Danh mục Ca làm
    case 'admin/shifts':
        (new ShiftController())->index();
        break;

    case 'admin/shifts/store':
        (new ShiftController())->store();
        break;

    case 'admin/shifts/update':
        (new ShiftController())->update();
        break;

    case 'admin/shifts/delete':
        (new ShiftController())->delete();
        break;

    // Duyệt & Xếp ca làm việc
    case 'admin/schedules':
        (new ScheduleController())->index();
        break;

    case 'admin/schedules/approve':
        (new ScheduleController())->approve();
        break;

    case 'admin/schedules/reject':
        (new ScheduleController())->reject();
        break;

    case 'admin/schedules/approve-all':
        (new ScheduleController())->approveAll();
        break;

    case 'admin/schedules/assign':
        (new ScheduleController())->assign();
        break;

    case 'admin/schedules/delete':
        (new ScheduleController())->delete();
        break;

    // Tính & Chốt Bảng lương
    case 'admin/payrolls':
        (new PayrollController())->index();
        break;

    case 'admin/payrolls/generate':
        (new PayrollController())->generate();
        break;

    case 'admin/payrolls/update':
        (new PayrollController())->update();
        break;

    case 'admin/payrolls/toggle-status':
        (new PayrollController())->toggleStatus();
        break;

    case 'admin/payrolls/mark-all-paid':
        (new PayrollController())->markAllPaid();
        break;

    case 'admin/payrolls/delete':
        (new PayrollController())->delete();
        break;

    // Chấm công & Nghỉ phép (Admin / Trưởng ca)
    case 'admin/attendances':
    case 'admin/attendance':
        (new AttendanceLeaveController())->adminIndex();
        break;

    case 'admin/attendance/store':
        (new AttendanceLeaveController())->adminStoreAttendance();
        break;

    case 'admin/attendance/update':
        (new AttendanceLeaveController())->adminUpdateAttendance();
        break;

    case 'admin/attendance/delete':
        (new AttendanceLeaveController())->adminDeleteAttendance();
        break;

    case 'admin/leaves/review':
        (new AttendanceLeaveController())->adminReviewLeave();
        break;

    // Cổng Nhân viên
    case 'client/home':
        (new ClientController())->home();
        break;

    case 'client/schedules/cancel':
        (new ClientController())->cancelShift();
        break;

    case 'client/profile/update':
        (new ClientController())->updateProfile();
        break;

    case 'client/schedules':
        (new ClientController())->registerPage();
        break;

    case 'client/schedules/save-availability':
        (new ClientController())->saveAvailability();
        break;

    // Chấm công & Nghỉ phép (Nhân viên)
    case 'client/attendance':
        (new AttendanceLeaveController())->clientIndex();
        break;

    case 'client/attendance/check-in':
        (new AttendanceLeaveController())->clientCheckIn();
        break;

    case 'client/attendance/check-out':
        (new AttendanceLeaveController())->clientCheckOut();
        break;

    case 'client/leaves/store':
        (new AttendanceLeaveController())->clientSubmitLeave();
        break;

    case 'client/leaves/cancel':
        (new AttendanceLeaveController())->clientCancelLeave();
        break;

    default:
        http_response_code(404);
        echo "<h3>404 - Không tìm thấy trang yêu cầu!</h3>";
        break;
}