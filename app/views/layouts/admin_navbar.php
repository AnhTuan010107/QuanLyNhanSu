<?php
$isAdminManager = (($_SESSION['user']['role'] ?? '') === 'Quản lý');
$activePage     = $activePage ?? '';
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm">
    <div class="container-fluid px-4">
        <a class="navbar-brand fw-bold" href="<?= BASE_URL ?>/admin/dashboard">
            <i class="bi bi-speedometer2 text-warning me-1"></i> HR ADMIN
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="adminNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 gap-1">
                <li class="nav-item">
                    <a class="nav-link <?= $activePage === 'dashboard' ? 'active fw-semibold' : '' ?>" href="<?= BASE_URL ?>/admin/dashboard">
                        <i class="bi bi-house-door me-1"></i> Tổng quan
                    </a>
                </li>
                <?php if ($isAdminManager): ?>
                <li class="nav-item">
                    <a class="nav-link <?= $activePage === 'stores' ? 'active fw-semibold' : '' ?>" href="<?= BASE_URL ?>/admin/stores">
                        <i class="bi bi-shop me-1"></i> Cửa hàng
                    </a>
                </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link <?= $activePage === 'users' ? 'active fw-semibold' : '' ?>" href="<?= BASE_URL ?>/admin/users">
                        <i class="bi bi-people me-1"></i> Nhân viên
                    </a>
                </li>
                <?php if ($isAdminManager): ?>
                <li class="nav-item">
                    <a class="nav-link <?= $activePage === 'shifts' ? 'active fw-semibold' : '' ?>" href="<?= BASE_URL ?>/admin/shifts">
                        <i class="bi bi-clock me-1"></i> Ca làm
                    </a>
                </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link <?= $activePage === 'schedules' ? 'active fw-semibold' : '' ?>" href="<?= BASE_URL ?>/admin/schedules">
                        <i class="bi bi-calendar-check me-1"></i> Duyệt & Xếp ca
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $activePage === 'attendance' ? 'active fw-semibold' : '' ?>" href="<?= BASE_URL ?>/admin/attendance">
                        <i class="bi bi-fingerprint me-1"></i> Chấm công & Nghỉ phép
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $activePage === 'payrolls' ? 'active fw-semibold' : '' ?>" href="<?= BASE_URL ?>/admin/payrolls">
                        <i class="bi bi-cash-coin me-1"></i> Bảng lương
                    </a>
                </li>
            </ul>

            <div class="d-flex align-items-center gap-3">
                <span class="text-white small">
                    <i class="bi bi-person-circle me-1"></i>
                    <?= htmlspecialchars($_SESSION['user']['full_name']) ?>
                    <span class="badge bg-warning text-dark ms-1"><?= htmlspecialchars($_SESSION['user']['role']) ?></span>
                </span>
                <a href="<?= BASE_URL ?>/logout" class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-box-arrow-right"></i> Đăng xuất
                </a>
            </div>
        </div>
    </div>
</nav>