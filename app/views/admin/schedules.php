<?php
function getAdminVietnameseWeekday($dateStr) {
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
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Duyệt & Xếp Ca Làm Việc - Quản Lý Nhân Sự</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; }
        .week-pill-nav .nav-link { color: #334155; font-weight: 600; border: 1px solid #dee2e6; background: #fff; }
        .week-pill-nav .nav-link.active { background-color: #0d6efd; color: #fff; border-color: #0d6efd; }
        .matrix-card { border-top: 3px solid #0d6efd; }
    </style>
</head>
<body>

<!-- Thanh điều hướng Admin -->
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
                    <a class="nav-link" href="<?= BASE_URL ?>/admin/dashboard">
                        <i class="bi bi-house-door me-1"></i> Tổng quan
                    </a>
                </li>
                <?php if ($isManager): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>/admin/stores">
                        <i class="bi bi-shop me-1"></i> Cửa hàng
                    </a>
                </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>/admin/users">
                        <i class="bi bi-people me-1"></i> Nhân viên
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active fw-semibold" href="<?= BASE_URL ?>/admin/schedules">
                        <i class="bi bi-calendar-check me-1"></i> Duyệt & Xếp ca
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

<main class="container-fluid px-4 py-4">

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

    <!-- Tiêu đề & Nút hành động chính -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">Duyệt Giờ Rảnh & Xếp Ca Làm Việc</h4>
            <p class="text-muted small mb-0">
                Kỳ đang xem: <strong><?= $currentWeek['title'] ?> (<?= $currentWeek['range_text'] ?>)</strong>
            </p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <?php if ($stats['pending'] > 0): ?>
                <form action="<?= BASE_URL ?>/admin/schedules/approve-all" method="POST"
                      onsubmit="return confirm('Xác nhận duyệt tất cả <?= $stats['pending'] ?> đăng ký đang chờ trong tuần này?');">
                    <input type="hidden" name="start_date" value="<?= $currentWeek['start_date'] ?>">
                    <input type="hidden" name="end_date" value="<?= $currentWeek['end_date'] ?>">
                    <input type="hidden" name="store_id" value="<?= $selectedStoreId ?? '' ?>">
                    <input type="hidden" name="week_offset" value="<?= $weekOffset ?>">
                    <input type="hidden" name="filter_store_id" value="<?= $selectedStoreId ?? '' ?>">
                    <input type="hidden" name="filter_status" value="<?= htmlspecialchars($selectedStatus) ?>">
                    <button type="submit" class="btn btn-success fw-semibold">
                        <i class="bi bi-check2-all me-1"></i> Duyệt tất cả chờ duyệt (<?= $stats['pending'] ?>)
                    </button>
                </form>
            <?php endif; ?>

            <button type="button" class="btn btn-primary fw-semibold" data-bs-toggle="modal" data-bs-target="#assignShiftModal">
                <i class="bi bi-plus-circle me-1"></i> Xếp ca trực tiếp
            </button>
        </div>
    </div>

    <!-- 4 Thẻ thống kê trong tuần -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Tổng đăng ký trong tuần</div>
                    <h4 class="fw-bold mb-0 mt-1"><?= $stats['total'] ?></h4>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Đang chờ duyệt</div>
                    <h4 class="fw-bold text-warning mb-0 mt-1"><?= $stats['pending'] ?></h4>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Đã duyệt xếp ca</div>
                    <h4 class="fw-bold text-success mb-0 mt-1"><?= $stats['approved'] ?></h4>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Đã từ chối</div>
                    <h4 class="fw-bold text-danger mb-0 mt-1"><?= $stats['rejected'] ?></h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Thanh chọn Tuần & Bộ lọc Cửa hàng / Trạng thái -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
            <!-- Chọn 3 tuần -->
            <div class="nav nav-pills week-pill-nav gap-2">
                <?php foreach ($weeks as $w): ?>
                    <a class="nav-link <?= $w['offset'] === $weekOffset ? 'active' : '' ?>"
                       href="<?= BASE_URL ?>/admin/schedules?week=<?= $w['offset'] ?>&store_id=<?= $selectedStoreId ?? '' ?>&status=<?= urlencode($selectedStatus) ?>">
                        <?= $w['title'] ?> <small class="opacity-75">(<?= $w['range_text'] ?>)</small>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Form lọc Cửa hàng & Trạng thái -->
            <form method="GET" action="<?= BASE_URL ?>/admin/schedules" class="d-flex flex-wrap align-items-center gap-2">
                <input type="hidden" name="week" value="<?= $weekOffset ?>">

                <?php if ($isManager): ?>
                    <select name="store_id" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
                        <option value="">-- Tất cả cửa hàng --</option>
                        <?php foreach ($stores as $st): ?>
                            <option value="<?= $st['id'] ?>" <?= (int)$selectedStoreId === (int)$st['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($st['store_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>

                <select name="status" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
                    <option value="all" <?= $selectedStatus === 'all' ? 'selected' : '' ?>>Tất cả trạng thái</option>
                    <option value="pending" <?= $selectedStatus === 'pending' ? 'selected' : '' ?>>Chờ duyệt</option>
                    <option value="approved" <?= $selectedStatus === 'approved' ? 'selected' : '' ?>>Đã duyệt</option>
                    <option value="rejected" <?= $selectedStatus === 'rejected' ? 'selected' : '' ?>>Từ chối</option>
                </select>
            </form>
        </div>
    </div>

    <!-- Bảng 1: Danh sách giờ rảnh nhân viên đăng ký -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <span class="fw-bold">
                <i class="bi bi-list-check text-primary me-1"></i> Danh sách đăng ký giờ rảnh & Xếp ca (<?= count($schedules) ?>)
            </span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Ngày làm</th>
                        <th>Nhân viên</th>
                        <th>Cửa hàng</th>
                        <th>Giờ rảnh ĐK</th>
                        <th>Ca xếp (Hệ thống)</th>
                        <th>Ghi chú</th>
                        <th class="text-center">Trạng thái</th>
                        <th class="text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($schedules)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                Không có dữ liệu đăng ký ca làm nào trong tuần này.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($schedules as $sc): ?>
                            <tr>
                                <td>
                                    <div class="fw-bold"><?= date('d/m/Y', strtotime($sc['work_date'])) ?></div>
                                    <small class="text-muted"><?= getAdminVietnameseWeekday($sc['work_date']) ?></small>
                                </td>
                                <td>
                                    <div class="fw-semibold"><?= htmlspecialchars($sc['full_name']) ?></div>
                                    <small class="text-muted">
                                        <?= htmlspecialchars($sc['employee_code']) ?> · <?= htmlspecialchars($sc['user_role']) ?>
                                    </small>
                                </td>
                                <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($sc['store_name']) ?></span></td>
                                <td>
                                    <span class="fw-bold text-primary"><?= htmlspecialchars($sc['display_range']) ?></span>
                                </td>
                                <td>
                                    <?php if ($sc['status'] === 'pending'): ?>
                                        <form id="approve-form-<?= $sc['id'] ?>" action="<?= BASE_URL ?>/admin/schedules/approve" method="POST" class="d-inline">
                                            <input type="hidden" name="id" value="<?= $sc['id'] ?>">
                                            <input type="hidden" name="week_offset" value="<?= $weekOffset ?>">
                                            <input type="hidden" name="filter_store_id" value="<?= $selectedStoreId ?? '' ?>">
                                            <input type="hidden" name="filter_status" value="<?= htmlspecialchars($selectedStatus) ?>">
                                            <select name="shift_id" class="form-select form-select-sm" style="min-width: 155px;">
                                                <?php foreach ($shifts as $sh): ?>
                                                    <option value="<?= $sh['id'] ?>" <?= (int)$sc['shift_id'] === (int)$sh['id'] ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($sh['shift_name']) ?> (<?= substr($sh['start_time'],0,5) ?>-<?= substr($sh['end_time'],0,5) ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </form>
                                    <?php else: ?>
                                        <span class="fw-semibold"><?= htmlspecialchars($sc['shift_name']) ?></span>
                                        <div class="small text-muted"><?= substr($sc['start_time'],0,5) ?> – <?= substr($sc['end_time'],0,5) ?> (x<?= $sc['coefficient'] ?>)</div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small class="text-muted"><?= $sc['clean_note'] !== '' ? htmlspecialchars($sc['clean_note']) : '-' ?></small>
                                </td>
                                <td class="text-center">
                                    <?php if ($sc['status'] === 'approved'): ?>
                                        <span class="badge bg-success">Đã duyệt</span>
                                    <?php elseif ($sc['status'] === 'pending'): ?>
                                        <span class="badge bg-warning text-dark">Chờ duyệt</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Từ chối</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end text-nowrap">
                                    <?php if ($sc['status'] === 'pending'): ?>
                                        <button type="submit" form="approve-form-<?= $sc['id'] ?>" class="btn btn-sm btn-success">
                                            <i class="bi bi-check-lg"></i> Duyệt
                                        </button>
                                        <form action="<?= BASE_URL ?>/admin/schedules/reject" method="POST" class="d-inline">
                                            <input type="hidden" name="id" value="<?= $sc['id'] ?>">
                                            <input type="hidden" name="week_offset" value="<?= $weekOffset ?>">
                                            <input type="hidden" name="filter_store_id" value="<?= $selectedStoreId ?? '' ?>">
                                            <input type="hidden" name="filter_status" value="<?= htmlspecialchars($selectedStatus) ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Từ chối</button>
                                        </form>
                                    <?php else: ?>
                                        <form action="<?= BASE_URL ?>/admin/schedules/delete" method="POST" class="d-inline"
                                              onsubmit="return confirm('Xóa dòng lịch này khỏi hệ thống?');">
                                            <input type="hidden" name="id" value="<?= $sc['id'] ?>">
                                            <input type="hidden" name="week_offset" value="<?= $weekOffset ?>">
                                            <input type="hidden" name="filter_store_id" value="<?= $selectedStoreId ?? '' ?>">
                                            <input type="hidden" name="filter_status" value="<?= htmlspecialchars($selectedStatus) ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-secondary" title="Xóa">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bảng 2: Tổng quan lịch nhân sự theo từng ngày T2 -> CN -->
    <div class="card border-0 shadow-sm matrix-card">
        <div class="card-header bg-white py-3 fw-bold">
            <i class="bi bi-calendar3 text-primary me-1"></i> Tổng quan nhân sự theo ngày (<?= $currentWeek['range_text'] ?>)
        </div>
        <div class="card-body">
            <div class="row g-2">
                <?php foreach ($weekDays as $wd): ?>
                    <div class="col-12 col-md-6 col-xl">
                        <div class="border rounded-3 p-2 h-100 bg-light-subtle">
                            <div class="d-flex justify-content-between align-items-center border-bottom pb-1 mb-2">
                                <span class="fw-bold text-primary small"><?= $wd['label'] ?></span>
                                <span class="badge bg-secondary-subtle text-dark"><?= $wd['date_text'] ?></span>
                            </div>

                            <?php if (empty($wd['items'])): ?>
                                <div class="text-muted small text-center py-3">Trống</div>
                            <?php else: ?>
                                <div class="d-flex flex-column gap-1">
                                    <?php foreach ($wd['items'] as $it): ?>
                                        <div class="p-2 rounded bg-white border small">
                                            <div class="fw-semibold text-truncate"><?= htmlspecialchars($it['full_name']) ?></div>
                                            <div class="d-flex justify-content-between align-items-center mt-1">
                                                <span class="text-muted" style="font-size: 11.5px;"><?= htmlspecialchars($it['display_range']) ?></span>
                                                <?php if ($it['status'] === 'approved'): ?>
                                                    <span class="badge bg-success" style="font-size: 10px;">Đã duyệt</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning text-dark" style="font-size: 10px;">Chờ</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

</main>

<!-- Modal Xếp ca trực tiếp -->
<div class="modal fade" id="assignShiftModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="<?= BASE_URL ?>/admin/schedules/assign" method="POST" class="modal-content">
            <input type="hidden" name="week_offset" value="<?= $weekOffset ?>">
            <input type="hidden" name="filter_store_id" value="<?= $selectedStoreId ?? '' ?>">
            <input type="hidden" name="filter_status" value="<?= htmlspecialchars($selectedStatus) ?>">

            <div class="modal-header">
                <h5 class="modal-title fw-bold">Xếp ca làm việc trực tiếp</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nhân viên <span class="text-danger">*</span></label>
                    <select name="user_id" class="form-select" required>
                        <option value="">-- Chọn nhân viên --</option>
                        <?php foreach ($employees as $emp): ?>
                            <option value="<?= $emp['id'] ?>">
                                <?= htmlspecialchars($emp['employee_code']) ?> - <?= htmlspecialchars($emp['full_name']) ?> (<?= htmlspecialchars($emp['store_name']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Ngày làm việc <span class="text-danger">*</span></label>
                    <input type="date" name="work_date" class="form-control"
                           min="<?= $currentWeek['start_date'] ?>"
                           max="<?= $currentWeek['end_date'] ?>"
                           value="<?= $currentWeek['start_date'] ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Ca làm việc <span class="text-danger">*</span></label>
                    <select name="shift_id" class="form-select" required>
                        <?php foreach ($shifts as $sh): ?>
                            <option value="<?= $sh['id'] ?>">
                                <?= htmlspecialchars($sh['shift_name']) ?> (<?= substr($sh['start_time'],0,5) ?> – <?= substr($sh['end_time'],0,5) ?> | Hệ số x<?= $sh['coefficient'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-0">
                    <label class="form-label fw-semibold">Ghi chú (tùy chọn)</label>
                    <input type="text" name="note" class="form-control" placeholder="VD: Tăng cường ca cuối tuần...">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="submit" class="btn btn-primary fw-semibold">Xác nhận xếp ca</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>