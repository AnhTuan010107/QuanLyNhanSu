<?php
session_start();

define('BASE_URL', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'));

$url = isset($_GET['url']) ? trim($_GET['url'], '/') : 'login';

require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/controllers/AdminController.php';

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

    case 'client/home':
        if (!isset($_SESSION['user'])) {
            header("Location: " . BASE_URL . "/login");
            exit;
        }
        echo "<h2>Trang Nhân Viên (Staff)</h2>";
        echo "<p>Xin chào: <strong>" . htmlspecialchars($_SESSION['user']['full_name']) . "</strong> - " . htmlspecialchars($_SESSION['user']['store_name']) . "</p>";
        echo "<a href='" . BASE_URL . "/logout'>Đăng xuất</a>";
        break;

    default:
        http_response_code(404);
        echo "<h3>404 - Không tìm thấy trang yêu cầu!</h3>";
        break;
}