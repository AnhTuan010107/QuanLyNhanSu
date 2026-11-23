<?php
function getVietnameseWeekday($dateStr) {
    $days = [
        'Sunday'    => 'Chủ nhật',
        'Monday'    => 'Thứ 2',
        'Tuesday'   => 'Thứ 3',
        'Wednesday' => 'Thứ 4',
        'Thursday'  => 'Thứ 5',
        'Friday'    => 'Thứ 6',
        'Saturday'  => 'Thứ 7',
    ];
    return $days[date('l', strtotime($dateStr))] ?? '';
}

function formatScheduleTimeText($sc) {
    if (!empty($sc['note']) && preg_match('/^\[(\d{2}):00-(\d{2}):00\]/', $sc['note'], $m)) {
        return (int)$m[1] . 'h - ' . (int)$m[2] . 'h';
    }
    return substr($sc['start_time'], 0, 5) . ' - ' . substr($sc['end_time'], 0, 5);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cổng Nhân Viên - Hệ thống Quản lý Nhân sự</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; }
        .nav-pills .nav-link { color: #334155; font-weight: 600; }
        .nav-pills .nav-link.active { background-color: #0d6efd; color: #fff; }
    </style>
</head>
<body>

<!-- Thanh điều hướng trên cùng -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?= BASE_URL ?>/client/home">
            <i class="bi bi-people-fill text-primary me-1"></i> HR PORTAL
        </a>

        <div class="d-flex align-items-center gap-2 ms-auto">
            <a href="<?= BASE_URL ?>/client/attendance" class="btn btn-success btn-sm fw-semibold">
                <i class="bi bi-fingerprint me-1"></i> Chấm công & Nghỉ phép
            </a>
            <a href="<?= BASE_URL ?>/client/schedules" class="btn btn-primary btn-sm fw-semibold">
                <i class="bi bi-calendar-plus me-1"></i> Đăng ký ca làm
            </a>
            <span class="text-white small mx-2 d-none d-md-inline">
                <i class="bi bi-person-circle me-1"></i>
                <?= htmlspecialchars($profile['full_name']) ?>
                <span class="badge bg-info text-dark ms-1"><?= htmlspecialchars($profile['role']) ?></span>
            </span>
            <a href="<?= BASE_URL ?>/logout" class="btn btn-outline-danger btn-sm">
                <i class="bi bi-box-arrow-right"></i> Đăng xuất
            </a>
        </div>
    </div>
</nav>

<main class="container py-4">

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- 4 Thẻ tóm tắt nhanh -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-3 me-3">
                        <i class="bi bi-shop fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Cửa hàng công tác</div>
                        <div class="fw-bold"><?= htmlspecialchars($profile['store_name']) ?></div>
                        <span class="badge bg-secondary-subtle text-dark"><?= htmlspecialchars($profile['employee_code']) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-success bg-opacity-10 text-success p-3 rounded-3 me-3">
                        <i class="bi bi-cash-coin fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Mức lương theo giờ</div>
                        <h5 class="fw-bold mb-0"><?= number_format($profile['hourly_rate'], 0, ',', '.') ?> đ</h5>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-3 me-3">
                        <i class="bi bi-calendar-check fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Ca đã duyệt (Tháng <?= date('m/Y') ?>)</div>
                        <h5 class="fw-bold mb-0"><?= $estimate['approved_shifts'] ?> ca (<?= $estimate['raw_hours'] ?> giờ)</h5>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-info bg-opacity-10 text-info p-3 rounded-3 me-3">
                        <i class="bi bi-wallet2 fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Tạm tính tháng <?= date('m/Y') ?></div>
                        <h5 class="fw-bold text-primary mb-0"><?= number_format($estimate['estimated_salary'], 0, ',', '.') ?> đ</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Thanh chuyển Tab -->
    <div class="d-flex flex-wrap justify-content-between align-items-center bg-white p-2 rounded-3 shadow-sm mb-4 gap-2">
        <ul class="nav nav-pills gap-2" id="clientTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="tab-schedule-btn" data-bs-toggle="pill" data-bs-target="#schedule" type="button">
                    <i class="bi bi-calendar-week me-1"></i> Lịch làm việc của tôi
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-payroll-btn" data-bs-toggle="pill" data-bs-target="#payroll" type="button">
                    <i class="bi bi-receipt me-1"></i> Bảng lương cá nhân
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-profile-btn" data-bs-toggle="pill" data-bs-target="#profile" type="button">
                    <i class="bi bi-person-vcard me-1"></i> Hồ sơ cá nhân
                </button>
            </li>
        </ul>

        <a href="<?= BASE_URL ?>/client/schedules" class="btn btn-outline-primary btn-sm fw-semibold">
            <i class="bi bi-clock-history me-1"></i> Mở trang Đăng ký giờ rảnh tuần sau
        </a>
    </div>

    <div class="tab-content">

        <!-- TAB 1: LỊCH LÀM VIỆC CỦA TÔI -->
        <div class="tab-pane fade show active" id="schedule" role="tabpanel">
            <div class="row g-4">
                <!-- Bảng 1: Lịch làm việc đã duyệt -->
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                            <span class="fw-bold text-success">
                                <i class="bi bi-check-circle-fill me-1"></i> Lịch làm việc đã duyệt (<?= count($approvedSchedules) ?>)
                            </span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Ngày làm</th>
                                        <th>Ca làm</th>
                                        <th>Khung giờ</th>
                                        <th class="text-center">Hệ số</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($approvedSchedules)): ?>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">Chưa có ca làm việc nào được duyệt.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($approvedSchedules as $sc): ?>
                                            <tr>
                                                <td>
                                                    <div class="fw-bold"><?= date('d/m/Y', strtotime($sc['work_date'])) ?></div>
                                                    <small class="text-muted"><?= getVietnameseWeekday($sc['work_date']) ?></small>
                                                </td>
                                                <td class="fw-semibold text-primary"><?= htmlspecialchars($sc['shift_name']) ?></td>
                                                <td><?= formatScheduleTimeText($sc) ?></td>
                                                <td class="text-center"><span class="badge bg-light text-dark border">x<?= $sc['coefficient'] ?></span></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Bảng 2: Lịch đăng ký chờ duyệt / từ chối -->
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                            <span class="fw-bold">
                                <i class="bi bi-hourglass-split text-warning me-1"></i> Giờ rảnh chờ duyệt & Từ chối (<?= count($otherSchedules) ?>)
                            </span>
                            <a href="<?= BASE_URL ?>/client/schedules" class="btn btn-sm btn-primary">+ Đăng ký thêm</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Ngày làm</th>
                                        <th>Khung giờ</th>
                                        <th class="text-center">Trạng thái</th>
                                        <th class="text-end">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($otherSchedules)): ?>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">Chưa có đăng ký nào đang chờ duyệt.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($otherSchedules as $sc): ?>
                                            <tr>
                                                <td>
                                                    <div class="fw-semibold"><?= date('d/m/Y', strtotime($sc['work_date'])) ?></div>
                                                    <small class="text-muted"><?= getVietnameseWeekday($sc['work_date']) ?></small>
                                                </td>
                                                <td><?= formatScheduleTimeText($sc) ?></td>
                                                <td class="text-center">
                                                    <?php if ($sc['status'] === 'pending'): ?>
                                                        <span class="badge bg-warning text-dark">Chờ duyệt</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">Từ chối</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-end">
                                                    <?php if ($sc['status'] === 'pending'): ?>
                                                        <form action="<?= BASE_URL ?>/client/schedules/cancel" method="POST" class="d-inline"
                                                              onsubmit="return confirm('Hủy đăng ký ca này?');">
                                                            <input type="hidden" name="id" value="<?= $sc['id'] ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-danger">Hủy</button>
                                                        </form>
                                                    <?php else: ?>
                                                        <span class="text-muted small">-</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 2: BẢNG LƯƠNG CÁ NHÂN -->
        <div class="tab-pane fade" id="payroll" role="tabpanel">
            <div class="alert alert-info border-0 shadow-sm d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <strong>Tạm tính lương tháng <?= date('m/Y') ?> (theo lịch đã duyệt):</strong>
                    <?= $estimate['approved_shifts'] ?> ca • <?= $estimate['raw_hours'] ?> giờ thực tế (<?= $estimate['weighted_hours'] ?> giờ quy đổi hệ số) × <?= number_format($profile['hourly_rate'], 0, ',', '.') ?> đ/giờ
                </div>
                <span class="fs-5 fw-bold text-primary"><?= number_format($estimate['estimated_salary'], 0, ',', '.') ?> đ</span>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold py-3">
                    <i class="bi bi-cash-stack text-success me-1"></i> Bảng lương hàng tháng đã chốt
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Kỳ lương</th>
                                <th class="text-end">Tổng giờ công</th>
                                <th class="text-end">Mức lương/giờ</th>
                                <th class="text-end">Thưởng</th>
                                <th class="text-end">Khấu trừ</th>
                                <th class="text-end">Thực lãnh</th>
                                <th class="text-center">Thanh toán</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($payrolls)): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        Chưa có kỳ lương nào được chốt trên hệ thống. Bạn có thể theo dõi phần tạm tính ở phía trên.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($payrolls as $p): ?>
                                    <tr>
                                        <td class="fw-bold"><?= htmlspecialchars($p['month_year']) ?></td>
                                        <td class="text-end"><?= number_format($p['total_hours'], 2) ?> giờ</td>
                                        <td class="text-end"><?= number_format($profile['hourly_rate'], 0, ',', '.') ?> đ</td>
                                        <td class="text-end text-success">+<?= number_format($p['bonus'], 0, ',', '.') ?> đ</td>
                                        <td class="text-end text-danger">-<?= number_format($p['deduction'], 0, ',', '.') ?> đ</td>
                                        <td class="text-end fw-bold text-primary"><?= number_format($p['final_salary'], 0, ',', '.') ?> đ</td>
                                        <td class="text-center">
                                            <?php if ($p['payment_status'] === 'paid'): ?>
                                                <span class="badge bg-success">Đã thanh toán</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark">Chưa thanh toán</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TAB 3: HỒ SƠ CÁ NHÂN -->
        <div class="tab-pane fade" id="profile" role="tabpanel">
            <div class="row g-4">
                <div class="col-lg-7">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white fw-bold py-3">
                            <i class="bi bi-person-badge text-primary me-1"></i> Thông tin hồ sơ nhân sự
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless mb-0">
                                <tr>
                                    <th class="text-muted" width="180">Mã nhân viên:</th>
                                    <td class="fw-bold text-primary"><?= htmlspecialchars($profile['employee_code']) ?></td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Họ và tên:</th>
                                    <td class="fw-semibold"><?= htmlspecialchars($profile['full_name']) ?></td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Email công việc:</th>
                                    <td><?= htmlspecialchars($profile['email']) ?></td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Vai trò:</th>
                                    <td><span class="badge bg-info text-dark"><?= htmlspecialchars($profile['role']) ?></span></td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Cửa hàng trực thuộc:</th>
                                    <td>
                                        <div class="fw-semibold"><?= htmlspecialchars($profile['store_name']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($profile['store_address']) ?></small>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Mức lương theo giờ:</th>
                                    <td class="fw-bold text-success"><?= number_format($profile['hourly_rate'], 0, ',', '.') ?> VNĐ/giờ</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Ngày vào hệ thống:</th>
                                    <td><?= date('d/m/Y', strtotime($profile['created_at'])) ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white fw-bold py-3">
                            <i class="bi bi-shield-lock text-warning me-1"></i> Cập nhật liên hệ & Mật khẩu
                        </div>
                        <div class="card-body">
                            <form action="<?= BASE_URL ?>/client/profile/update" method="POST" class="needs-validation" novalidate>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Số điện thoại <span class="text-danger">*</span></label>
                                    <input type="text" name="phone" class="form-control" pattern="0[0-9]{9}" value="<?= htmlspecialchars($profile['phone']) ?>" required>
                                    <div class="invalid-feedback">Số điện thoại gồm 10 chữ số và bắt đầu bằng số 0.</div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Mật khẩu mới <small class="text-muted fw-normal">(Để trống nếu không đổi)</small></label>
                                    <input type="password" name="password" class="form-control" minlength="6" placeholder="Tối thiểu 6 ký tự">
                                    <div class="invalid-feedback">Mật khẩu mới tối thiểu 6 ký tự.</div>
                                </div>

                                <button type="submit" class="btn btn-warning fw-semibold w-100">
                                    <i class="bi bi-save me-1"></i> Lưu thay đổi
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</main>

<footer class="mt-5 py-3 border-top text-center text-muted small">
    Hệ thống Quản lý Nhân sự & Đào tạo Nội bộ &copy; 2026
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const hash = window.location.hash;
        if (hash) {
            const targetBtn = document.querySelector(`#clientTabs button[data-bs-target="${hash}"]`);
            if (targetBtn) {
                new bootstrap.Tab(targetBtn).show();
            }
        }
        document.querySelectorAll('#clientTabs button').forEach(btn => {
            btn.addEventListener('shown.bs.tab', function (e) {
                history.replaceState(null, null, e.target.getAttribute('data-bs-target'));
            });
        });
    });

    document.querySelectorAll('.needs-validation').forEach(form => {
        form.addEventListener('submit', function (e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
</script>
</body>
</html>