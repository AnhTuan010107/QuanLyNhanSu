<?php $active = $activePage ?? 'dashboard'; ?>
<?php ob_start(); ?>
<ul class="nav flex-column sidebar-nav p-3">
    <li class="nav-item">
        <a class="nav-link <?= $active === 'dashboard' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/dashboard">
            <i class="bi bi-speedometer2 me-2"></i> Tổng quan
        </a>
    </li>
    <li class="nav-item mt-2 text-uppercase text-secondary small px-2 fw-bold">Nhân sự & Tổ chức</li>
    <?php if (($_SESSION['user']['role'] ?? '') === 'Quản lý'): ?>
    <li class="nav-item">
        <a class="nav-link <?= $active === 'stores' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/stores">
            <i class="bi bi-shop me-2"></i> Cửa hàng
        </a>
    </li>
    <?php endif; ?>
    <li class="nav-item">
        <a class="nav-link <?= $active === 'users' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/users">
            <i class="bi bi-person-badge me-2"></i> Hồ sơ nhân viên
        </a>
    </li>

    <li class="nav-item mt-2 text-uppercase text-secondary small px-2 fw-bold">Ca làm & Chấm công</li>
    <li class="nav-item">
        <a class="nav-link <?= $active === 'shifts' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/shifts">
            <i class="bi bi-clock me-2"></i> Danh mục ca làm
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $active === 'schedules' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/schedules">
            <i class="bi bi-calendar-check me-2"></i> Duyệt lịch làm việc
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $active === 'attendances' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/attendance">
            <i class="bi bi-fingerprint me-2"></i> Chấm công & Nghỉ phép
        </a>
    </li>

    <li class="nav-item mt-2 text-uppercase text-secondary small px-2 fw-bold">Đào tạo & Tiền lương</li>
    <li class="nav-item">
        <a class="nav-link <?= $active === 'trainings' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/trainings">
            <i class="bi bi-journal-bookmark me-2"></i> Đào tạo nội bộ
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $active === 'payrolls' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/payrolls">
            <i class="bi bi-cash-stack me-2"></i> Bảng lương tháng
        </a>
    </li>
</ul>
<?php $menuHtml = ob_get_clean(); ?>

<aside class="bg-dark sidebar-desktop d-none d-md-block flex-shrink-0">
    <?= $menuHtml ?>
</aside>

<div class="offcanvas offcanvas-start bg-dark text-white" tabindex="-1" id="mobileSidebar">
    <div class="offcanvas-header border-bottom border-secondary">
        <h5 class="offcanvas-title fw-bold">HR SYSTEM</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-0">
        <?= $menuHtml ?>
    </div>
</div>

<main class="flex-grow-1 p-3 p-md-4">