<?php
class AuthController {
    private $model;

    public function __construct() {
        $this->model = new AppModel();
    }

    public function login() {
        $error = "";

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = isset($_POST['email']) ? trim($_POST['email']) : "";
            $password = isset($_POST['password']) ? $_POST['password'] : "";

            $user = $this->model->findUserByEmail($email);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user'] = $user;
                header("Location: " . BASE_URL . "/?page=home");
                exit;
            } else {
                $error = "Sai email hoặc mật khẩu!";
            }
        }

        require_once __DIR__ . '/../views/login.php';
    }

    public function logout() {
        session_destroy();
        header("Location: " . BASE_URL . "/?page=login");
        exit;
    }
}