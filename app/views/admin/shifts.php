<?php
function calcShiftDurationHours($start, $end) {
    $s = strtotime("1970-01-01 " . $start);
    $e = strtotime("1970-01-01 " . $end);
    if ($e < $s) {
        $e += 86400;
    }
    return round(($e - $s) / 3600, 2);
}

$totalShifts = count($shifts);
$bonusShifts = 0;
foreach ($shifts as $item) {
    if ((float) $item['coefficient'] > 1.0) {
        $bonusShifts++;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh Mục Ca Làm - Quản Lý Nhân Sự</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; }
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
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>/admin/stores">
                        <i class="bi bi-shop me-1"></i> Cửa hàng
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>/admin/users">
                        <i class="bi bi-people me-1"></i> Nhân viên
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active fw-semibold" href="<?= BASE_URL ?>/admin/shifts">
                        <i class="bi bi-clock me-1"></i> Ca làm
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>/admin/schedules">
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

    <!-- Tiêu đề & Nút thêm mới -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">Danh Mục Ca Làm & Hệ Số Lương</h4>
            <p class="text-muted small mb-0">Quản lý khung giờ ca chuẩn và hệ số nhân lương theo ca làm việc</p>
        </div>

        <button type="button" class="btn btn-primary fw-semibold" data-bs-toggle="modal" data-bs-target="#addShiftModal">
            <i class="bi bi-plus-circle me-1"></i> Thêm ca làm mới
        </button>
    </div>

    <!-- 3 Thẻ tóm tắt -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-3 me-3">
                        <i class="bi bi-clock-history fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Tổng số ca làm</div>
                        <h4 class="fw-bold mb-0"><?= $totalShifts ?> ca</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-success bg-opacity-10 text-success p-3 rounded-3 me-3">
                        <i class="bi bi-calendar2-check fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Ca tiêu chuẩn (x1.0)</div>
                        <h4 class="fw-bold mb-0"><?= $totalShifts - $bonusShifts ?> ca</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-3 me-3">
                        <i class="bi bi-stars fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Ca tăng hệ số (&gt; x1.0)</div>
                        <h4 class="fw-bold mb-0"><?= $bonusShifts ?> ca</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bảng danh sách ca làm -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 fw-bold">
            <i class="bi bi-table text-primary me-1"></i> Danh sách ca làm việc (<?= $totalShifts ?>)
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="70">ID</th>
                        <th>Tên ca làm</th>
                        <th>Giờ bắt đầu</th>
                        <th>Giờ kết thúc</th>
                        <th class="text-center">Thời lượng</th>
                        <th class="text-center">Hệ số lương</th>
                        <th class="text-center">Lượt phân ca</th>
                        <th class="text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($shifts)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">Chưa có ca làm nào trong hệ thống.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($shifts as $sh): 
                            $duration = calcShiftDurationHours($sh['start_time'], $sh['end_time']);
                            $coef = (float) $sh['coefficient'];
                        ?>
                            <tr>
                                <td class="text-muted fw-semibold">#<?= $sh['id'] ?></td>
                                <td class="fw-bold text-primary"><?= htmlspecialchars($sh['shift_name']) ?></td>
                                <td><span class="badge bg-light text-dark border fs-6 fw-normal"><?= substr($sh['start_time'], 0, 5) ?></span></td>
                                <td><span class="badge bg-light text-dark border fs-6 fw-normal"><?= substr($sh['end_time'], 0, 5) ?></span></td>
                                <td class="text-center"><?= $duration ?> giờ</td>
                                <td class="text-center">
                                    <?php if ($coef > 1.0): ?>
                                        <span class="badge bg-warning text-dark">x<?= number_format($coef, 2) ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">x<?= number_format($coef, 2) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary-subtle text-dark"><?= (int) $sh['total_schedules'] ?> lượt</span>
                                </td>
                                <td class="text-end text-nowrap">
                                    <button type="button"
                                            class="btn btn-sm btn-outline-primary btn-edit-shift"
                                            data-id="<?= $sh['id'] ?>"
                                            data-name="<?= htmlspecialchars($sh['shift_name']) ?>"
                                            data-start="<?= substr($sh['start_time'], 0, 5) ?>"
                                            data-end="<?= substr($sh['end_time'], 0, 5) ?>"
                                            data-coef="<?= $sh['coefficient'] ?>"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editShiftModal">
                                        <i class="bi bi-pencil-square"></i> Sửa
                                    </button>

                                    <form action="<?= BASE_URL ?>/admin/shifts/delete" method="POST" class="d-inline"
                                          onsubmit="return confirm('Xác nhận xóa ca làm \"<?= htmlspecialchars($sh['shift_name']) ?>\"?');">
                                        <input type="hidden" name="id" value="<?= $sh['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i> Xóa
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</main>

<!-- Modal Thêm Ca Làm Mới -->
<div class="modal fade" id="addShiftModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="<?= BASE_URL ?>/admin/shifts/store" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Thêm ca làm mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Tên ca làm <span class="text-danger">*</span></label>
                    <input type="text" name="shift_name" class="form-control" placeholder="VD: Ca Sáng, Ca Chiều, Ca Tối, Ca Lễ..." required>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label class="form-label fw-semibold">Giờ bắt đầu <span class="text-danger">*</span></label>
                        <input type="time" name="start_time" class="form-control" value="08:00" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">Giờ kết thúc <span class="text-danger">*</span></label>
                        <input type="time" name="end_time" class="form-control" value="13:00" required>
                    </div>
                </div>

                <div class="mb-0">
                    <label class="form-label fw-semibold">Hệ số lương <span class="text-danger">*</span></label>
                    <input type="number" name="coefficient" class="form-control" step="0.1" min="0.5" max="5.0" value="1.0" required>
                    <div class="form-text">Ví dụ: 1.0 (ngày thường), 1.5 (tăng ca/cuối tuần), 2.0 (ngày lễ).</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="submit" class="btn btn-primary fw-semibold">Lưu ca làm</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Sửa Ca Làm -->
<div class="modal fade" id="editShiftModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="<?= BASE_URL ?>/admin/shifts/update" method="POST" class="modal-content">
            <input type="hidden" name="id" id="edit_shift_id">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Cập nhật thông tin ca làm</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Tên ca làm <span class="text-danger">*</span></label>
                    <input type="text" name="shift_name" id="edit_shift_name" class="form-control" required>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label class="form-label fw-semibold">Giờ bắt đầu <span class="text-danger">*</span></label>
                        <input type="time" name="start_time" id="edit_start_time" class="form-control" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">Giờ kết thúc <span class="text-danger">*</span></label>
                        <input type="time" name="end_time" id="edit_end_time" class="form-control" required>
                    </div>
                </div>

                <div class="mb-0">
                    <label class="form-label fw-semibold">Hệ số lương <span class="text-danger">*</span></label>
                    <input type="number" name="coefficient" id="edit_coefficient" class="form-control" step="0.1" min="0.5" max="5.0" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="submit" class="btn btn-warning fw-semibold">Cập nhật</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.querySelectorAll('.btn-edit-shift').forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('edit_shift_id').value   = this.dataset.id;
            document.getElementById('edit_shift_name').value = this.dataset.name;
            document.getElementById('edit_start_time').value = this.dataset.start;
            document.getElementById('edit_end_time').value   = this.dataset.end;
            document.getElementById('edit_coefficient').value = this.dataset.coef;
        });
    });
</script>
</body>
</html>