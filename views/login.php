<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Hệ thống Quản Lý Nhân Sự</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-dark-blue: #060913;
            --card-dark-blue: #0d1526;
            --input-dark: #080d1a;
            --text-light: #f1f5f9;
            --text-muted-blue: #94a3b8;
            --blue-primary: #2563eb;
            --blue-cyan: #0ea5e9;
            --border-blue: rgba(14, 165, 233, 0.24);
        }
        body {
            font-family: 'Be Vietnam Pro', sans-serif;
            background-color: var(--bg-dark-blue);
            background-image:
                radial-gradient(at 18% 18%, rgba(37, 99, 235, 0.22) 0px, transparent 52%),
                radial-gradient(at 82% 82%, rgba(14, 165, 233, 0.16) 0px, transparent 52%),
                linear-gradient(180deg, #060913 0%, #091120 100%);
            color: var(--text-light);
            min-height: 100dvh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
            margin: 0;
        }
        .login-box {
            width: 100%;
            max-width: 420px;
            background: rgba(13, 21, 38, 0.94);
            backdrop-filter: blur(14px);
            border-radius: 22px;
            border: 1px solid var(--border-blue);
            box-shadow:
                0 25px 55px -12px rgba(0, 0, 0, 0.72),
                0 0 28px -8px rgba(14, 165, 233, 0.22);
            padding: 34px 30px;
            position: relative;
            overflow: hidden;
        }
        .login-box::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3.5px;
            background: linear-gradient(90deg, #1d4ed8 0%, #0ea5e9 55%, #38bdf8 100%);
        }
        .brand-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            border-radius: 999px;
            background: rgba(14, 165, 233, 0.12);
            border: 1px solid rgba(56, 189, 248, 0.3);
            color: #38bdf8;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.03em;
            margin-bottom: 14px;
        }
        .form-label {
            font-size: 0.82rem;
            font-weight: 600;
            color: #cbd5e1;
            margin-bottom: 6px;
        }
        .forgot-link {
            font-size: 0.78rem;
            font-weight: 600;
            color: #38bdf8;
            text-decoration: none;
        }
        .forgot-link:hover {
            color: #7dd3fc;
            text-decoration: underline;
        }
        .form-control {
            border-radius: 11px;
            padding: 11px 14px;
            border: 1px solid rgba(148, 163, 184, 0.25);
            background-color: var(--input-dark);
            font-size: 0.95rem;
            color: #ffffff;
            transition: all 0.2s ease;
        }
        .form-control::placeholder {
            color: #64748b;
        }
        .form-control:focus {
            background-color: #091020;
            border-color: var(--blue-cyan);
            color: #ffffff;
            box-shadow: 0 0 0 3.5px rgba(14, 165, 233, 0.22);
        }
        .btn-toggle-pass {
            border-radius: 0 11px 11px 0;
            border: 1px solid rgba(148, 163, 184, 0.25);
            border-left: 0;
            background-color: var(--input-dark);
            color: var(--text-muted-blue);
        }
        .btn-toggle-pass:hover {
            background-color: rgba(14, 165, 233, 0.14);
            color: #38bdf8;
        }
        .btn-login {
            background: linear-gradient(135deg, #2563eb 0%, #0284c7 100%);
            border: none;
            border-radius: 11px;
            padding: 12px 16px;
            font-weight: 600;
            font-size: 0.95rem;
            color: #ffffff;
            box-shadow: 0 10px 24px -6px rgba(2, 132, 199, 0.5);
            transition: all 0.2s ease;
        }
        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 14px 28px -6px rgba(14, 165, 233, 0.6);
            color: #ffffff;
        }
        .demo-info {
            background: rgba(8, 13, 26, 0.78);
            border: 1px solid rgba(148, 163, 184, 0.16);
            border-radius: 12px;
            padding: 11px 14px;
            font-size: 0.78rem;
            color: var(--text-muted-blue);
        }
        .demo-info code {
            color: #38bdf8;
        }
        .modal-content {
            background-color: var(--card-dark-blue);
            color: var(--text-light);
            border: 1px solid var(--border-blue) !important;
        }
        .modal-header .btn-close {
            filter: invert(1) grayscale(100%) brightness(200%);
        }
        @media (max-width: 480px) {
            .login-box {
                padding: 28px 20px 24px;
                border-radius: 18px;
            }
        }
    </style>
</head>
<body>

<div class="login-box">
    <div class="brand-tag">
        <i class="bi bi-layers-half"></i>
        <span>HR WORKSPACE</span>
    </div>

    <h4 class="fw-bold text-white mb-1">Đăng nhập</h4>
    <p class="small mb-4" style="color: var(--text-muted-blue);">Nhập thông tin tài khoản để tiếp tục</p>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger border-0 rounded-3 py-2 px-3 small d-flex align-items-center mb-3">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control"
                   placeholder="name@company.vn" inputmode="email" required autofocus>
        </div>

        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <label class="form-label mb-0">Mật khẩu</label>
                <a href="#" class="forgot-link" data-bs-toggle="modal" data-bs-target="#forgotModal">
                    Quên mật khẩu?
                </a>
            </div>
            <div class="input-group">
                <input type="password" id="passwordInput" name="password" class="form-control"
                       placeholder="Nhập mật khẩu" required style="border-radius: 11px 0 0 11px; border-right: 0;">
                <button type="button" id="togglePass" class="btn btn-toggle-pass"
                        aria-label="Hiện hoặc ẩn mật khẩu">
                    <i class="bi bi-eye" id="toggleIcon"></i>
                </button>
            </div>
        </div>

        <button type="submit" class="btn btn-login w-100">
            Đăng nhập <i class="bi bi-arrow-right-short fs-5 align-middle"></i>
        </button>
    </form>

    <div class="demo-info mt-4">
        <div class="fw-semibold text-white mb-1">Tài khoản mẫu:</div>
        <div>Quản lý: <code>admin@hr.vn</code> / <code>password</code></div>
        <div>Nhân viên: <code>ha.ltt@hr.vn</code> / <code>password</code></div>
    </div>
</div>

<!-- Modal Quên mật khẩu -->
<div class="modal fade" id="forgotModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content rounded-4 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold">Khôi phục mật khẩu</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="forgotForm">
                <div class="modal-body pt-2">
                    <p class="small mb-3" style="color: var(--text-muted-blue);">
                        Nhập email tài khoản của bạn để gửi yêu cầu cấp lại mật khẩu tới Quản lý hệ thống.
                    </p>
                    <label class="form-label">Email tài khoản</label>
                    <input type="email" id="forgotEmail" class="form-control"
                           placeholder="name@company.vn" inputmode="email" required>
                    <div id="forgotMsg" class="small mt-2 d-none"></div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-3 text-light" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary btn-sm rounded-3 px-3">Gửi yêu cầu</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const toggleBtn = document.getElementById('togglePass');
const passInput = document.getElementById('passwordInput');
const toggleIcon = document.getElementById('toggleIcon');

toggleBtn.addEventListener('click', () => {
    const isHidden = passInput.type === 'password';
    passInput.type = isHidden ? 'text' : 'password';
    toggleIcon.className = isHidden ? 'bi bi-eye-slash' : 'bi bi-eye';
});

const forgotForm = document.getElementById('forgotForm');
const forgotMsg = document.getElementById('forgotMsg');

forgotForm.addEventListener('submit', (e) => {
    e.preventDefault();
    const emailVal = document.getElementById('forgotEmail').value.trim();
    forgotMsg.className = 'small mt-2 text-info fw-medium';
    forgotMsg.textContent = `Đã ghi nhận yêu cầu cho ${emailVal}. Vui lòng liên hệ Quản lý để đặt lại mật khẩu.`;
    forgotForm.reset();
});
</script>
</body>
</html>