<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Hệ thống Quản lý Nhân sự</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center min-vh-100">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-body p-4">
                        <h4 class="text-center fw-bold text-primary mb-1">QUẢN LÝ NHÂN SỰ</h4>
                        <p class="text-center text-muted small mb-4">Đăng nhập hệ thống nội bộ</p>

                        <?php if (!empty($_SESSION['error'])): ?>
                            <div class="alert alert-danger py-2 small">
                                <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                            </div>
                        <?php endif; ?>

                        <form id="loginForm" action="<?= BASE_URL ?>/auth/login" method="POST" novalidate>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Email công việc</label>
                                <input type="email" name="email" id="email" class="form-control" placeholder="admin@hr.vn" required>
                                <div class="invalid-feedback">Vui lòng nhập email hợp lệ.</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Mật khẩu</label>
                                <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required>
                                <div class="invalid-feedback">Vui lòng nhập mật khẩu.</div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 fw-semibold">Đăng nhập</button>
                        </form>

                        <hr class="my-3">
                        <div class="small text-muted">
                            <strong>Tài khoản test (mật khẩu: <code>password</code>):</strong><br>
                            • Admin: <code>admin@hr.vn</code><br>
                            • Quản lý: <code>khoi.tm@hr.vn</code><br>
                            • Nhân viên: <code>ha.ltt@hr.vn</code>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const email = document.getElementById('email');
            const password = document.getElementById('password');
            let valid = true;

            if (!email.value.trim() || !email.value.includes('@')) {
                email.classList.add('is-invalid');
                valid = false;
            } else {
                email.classList.remove('is-invalid');
            }

            if (!password.value.trim()) {
                password.classList.add('is-invalid');
                valid = false;
            } else {
                password.classList.remove('is-invalid');
            }

            if (!valid) e.preventDefault();
        });
    </script>
</body>
</html>
