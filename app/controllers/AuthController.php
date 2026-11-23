<?php
require_once __DIR__ . '/../models/UserModel.php';

class AuthController {
    private $userModel;

    public function __construct() {
        $this->userModel = new UserModel();
    }

    public function showLogin() {
        if (isset($_SESSION['user'])) {
            $this->redirectByRole($_SESSION['user']['role']);
        }
        require_once __DIR__ . '/../views/client/login.php';
    }

    public function handleLogin() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASE_URL . "/login");
            exit;
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $_SESSION['error'] = "Vui lòng nhập đầy đủ email và mật khẩu.";
            header("Location: " . BASE_URL . "/login");
            exit;
        }

        $user = $this->userModel->findByEmail($email);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = [
                'id'            => $user['id'],
                'employee_code' => $user['employee_code'],
                'full_name'     => $user['full_name'],
                'email'         => $user['email'],
                'role'          => $user['role'],
                'store_id'      => $user['store_id'],
                'store_name'    => $user['store_name']
            ];
            $this->redirectByRole($user['role']);
        } else {
            $_SESSION['error'] = "Email hoặc mật khẩu không chính xác!";
            header("Location: " . BASE_URL . "/login");
            exit;
        }
    }

    public function logout() {
        session_unset();
        session_destroy();
        header("Location: " . BASE_URL . "/login");
        exit;
    }

    private function redirectByRole($role) {
        if (in_array($role, ['Quản lý', 'Trưởng ca (TC)'])) {
            header("Location: " . BASE_URL . "/admin/dashboard");
        } else {
            header("Location: " . BASE_URL . "/client/home");
        }
        exit;
    }
}