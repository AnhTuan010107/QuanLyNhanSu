<?php
$pageTitle = 'Tổng quan Quản trị';
$activePage = 'dashboard';
require_once __DIR__ . '/../layouts/admin_header.php';
require_once __DIR__ . '/../layouts/admin_sidebar.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0">Bảng điều khiển tổng quan</h4>
        <small class="text-muted">Đơn vị công tác: <?= htmlspecialchars($_SESSION['user']['store_name'] ?? 'Cửa hàng Quận 1') ?></small>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-3 me-3">
                    <i class="bi bi-shop fs-3"></i>
                </div>
                <div>
                    <div class="text-muted small">Cửa hàng hoạt động</div>
                    <h4 class="fw-bold mb-0"><?= $stats['stores'] ?? 0 ?></h4>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <div class="bg-success bg-opacity-10 text-success p-3 rounded-3 me-3">
                    <i class="bi bi-people fs-3"></i>
                </div>
                <div>
                    <div class="text-muted small">Nhân sự đang làm việc</div>
                    <h4 class="fw-bold mb-0"><?= $stats['users'] ?? 0 ?></h4>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-3 me-3">
                    <i class="bi bi-calendar2-week fs-3"></i>
                </div>
                <div>
                    <div class="text-muted small">Ca đăng ký chờ duyệt</div>
                    <h4 class="fw-bold mb-0"><?= $stats['pending_schedules'] ?? 0 ?></h4>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <div class="bg-info bg-opacity-10 text-info p-3 rounded-3 me-3">
                    <i class="bi bi-journal-text fs-3"></i>
                </div>
                <div>
                    <div class="text-muted small">Khóa đào tạo nội bộ</div>
                    <h4 class="fw-bold mb-0"><?= $stats['trainings'] ?? 0 ?></h4>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-semibold">Truy cập nhanh nghiệp vụ</div>
    <div class="card-body d-flex flex-wrap gap-2">
        <?php if (($_SESSION['user']['role'] ?? '') === 'admin'): ?>
            <a href="<?= BASE_URL ?>/admin/stores" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-plus-circle me-1"></i> Quản lý Cửa hàng
            </a>
        <?php endif; ?>
        <a href="<?= BASE_URL ?>/admin/users" class="btn btn-outline-success btn-sm">
            <i class="bi bi-person-plus me-1"></i> Quản lý Nhân viên
        </a>
        <a href="<?= BASE_URL ?>/admin/schedules" class="btn btn-outline-warning btn-sm">
            <i class="bi bi-check2-square me-1"></i> Duyệt lịch làm việc
        </a>
        <a href="<?= BASE_URL ?>/admin/payrolls" class="btn btn-outline-dark btn-sm">
            <i class="bi bi-calculator me-1"></i> Tính lương tháng
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/admin_footer.php'; ?>