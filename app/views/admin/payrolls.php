<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Bảng Lương - Quản Lý Nhân Sự</title>
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
                <?php if ($isManager): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>/admin/shifts">
                        <i class="bi bi-clock me-1"></i> Ca làm
                    </a>
                </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>/admin/schedules">
                        <i class="bi bi-calendar-check me-1"></i> Duyệt & Xếp ca
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active fw-semibold" href="<?= BASE_URL ?>/admin/payrolls">
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

    <!-- Tiêu đề & Nút tính lương -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">Tính & Chốt Bảng Lương Hàng Tháng</h4>
            <p class="text-muted small mb-0">
                Tổng hợp tự động từ lịch làm việc đã duyệt (<strong><?= htmlspecialchars($selectedMonth) ?></strong>)
            </p>
        </div>

        <?php if ($isManager): ?>
        <div class="d-flex flex-wrap gap-2">
            <?php if ($summary['unpaid_count'] > 0): ?>
                <form action="<?= BASE_URL ?>/admin/payrolls/mark-all-paid" method="POST"
                      onsubmit="return confirm('Xác nhận đã thanh toán lương cho tất cả <?= $summary['unpaid_count'] ?> nhân viên chưa thanh toán trong kỳ <?= htmlspecialchars($selectedMonth) ?>?');">
                    <input type="hidden" name="month" value="<?= htmlspecialchars($selectedMonth) ?>">
                    <input type="hidden" name="store_id" value="<?= $selectedStoreId ?? '' ?>">
                    <input type="hidden" name="filter_store_id" value="<?= $selectedStoreId ?? '' ?>">
                    <button type="submit" class="btn btn-outline-success fw-semibold">
                        <i class="bi bi-check2-circle me-1"></i> Thanh toán tất cả (<?= $summary['unpaid_count'] ?>)
                    </button>
                </form>
            <?php endif; ?>

            <form action="<?= BASE_URL ?>/admin/payrolls/generate" method="POST"
                  onsubmit="return confirm('Hệ thống sẽ tổng hợp giờ công các ca đã duyệt trong tháng <?= htmlspecialchars($selectedMonth) ?> để tính lương. Tiếp tục?');">
                <input type="hidden" name="month" value="<?= htmlspecialchars($selectedMonth) ?>">
                <input type="hidden" name="store_id" value="<?= $selectedStoreId ?? '' ?>">
                <input type="hidden" name="filter_store_id" value="<?= $selectedStoreId ?? '' ?>">
                <button type="submit" class="btn btn-primary fw-semibold">
                    <i class="bi bi-calculator me-1"></i> Tính / Cập nhật lương tháng <?= htmlspecialchars($selectedMonth) ?>
                </button>
            </form>
        </div>
        <?php endif; ?>
    </div>

    <!-- 4 Thẻ thống kê quỹ lương -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-3 me-3">
                        <i class="bi bi-people fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Nhân sự có lương</div>
                        <h4 class="fw-bold mb-0"><?= $summary['total_employees'] ?> người</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-info bg-opacity-10 text-info p-3 rounded-3 me-3">
                        <i class="bi bi-clock-history fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Tổng giờ quy đổi</div>
                        <h4 class="fw-bold mb-0"><?= number_format($summary['total_hours'], 2) ?> giờ</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-success bg-opacity-10 text-success p-3 rounded-3 me-3">
                        <i class="bi bi-cash-stack fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Tổng quỹ lương kỳ này</div>
                        <h5 class="fw-bold text-success mb-0"><?= number_format($summary['total_salary'], 0, ',', '.') ?> đ</h5>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-3 me-3">
                        <i class="bi bi-hourglass-split fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Chưa thanh toán</div>
                        <h4 class="fw-bold text-warning mb-0"><?= $summary['unpaid_count'] ?> phiếu</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bộ lọc Kỳ lương & Cửa hàng -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
            <form method="GET" action="<?= BASE_URL ?>/admin/payrolls" class="d-flex flex-wrap align-items-center gap-3">
                <div class="d-flex align-items-center gap-2">
                    <label class="fw-semibold small text-nowrap mb-0">Kỳ lương:</label>
                    <input type="month" name="month" class="form-control form-control-sm"
                           value="<?= htmlspecialchars($selectedMonth) ?>" onchange="this.form.submit()">
                </div>

                <?php if ($isManager): ?>
                    <div class="d-flex align-items-center gap-2">
                        <label class="fw-semibold small text-nowrap mb-0">Cửa hàng:</label>
                        <select name="store_id" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">-- Tất cả cửa hàng --</option>
                            <?php foreach ($stores as $st): ?>
                                <option value="<?= $st['id'] ?>" <?= (int)$selectedStoreId === (int)$st['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($st['store_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>
            </form>

            <div class="text-muted small">
                <i class="bi bi-info-circle me-1"></i> Công thức: <code>(Giờ công quy đổi hệ số × Lương giờ) + Thưởng − Khấu trừ</code>
            </div>
        </div>
    </div>

    <!-- Bảng chi tiết lương nhân viên -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 fw-bold">
            <i class="bi bi-receipt-cutoff text-primary me-1"></i> Bảng lương tháng <?= htmlspecialchars($selectedMonth) ?>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nhân viên</th>
                        <th>Cửa hàng</th>
                        <th class="text-end">Giờ quy đổi</th>
                        <th class="text-end">Lương/giờ</th>
                        <th class="text-end">Lương cơ bản</th>
                        <th class="text-end">Thưởng</th>
                        <th class="text-end">Khấu trừ</th>
                        <th class="text-end">Thực lãnh</th>
                        <th class="text-center">Trạng thái</th>
                        <?php if ($isManager): ?>
                            <th class="text-end">Thao tác</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($payrolls)): ?>
                        <tr>
                            <td colspan="<?= $isManager ? 10 : 9 ?>" class="text-center text-muted py-4">
                                Chưa có dữ liệu bảng lương cho kỳ <strong><?= htmlspecialchars($selectedMonth) ?></strong>.
                                <?php if ($isManager): ?>
                                    Bấm nút <strong>"Tính / Cập nhật lương tháng <?= htmlspecialchars($selectedMonth) ?>"</strong> ở góc trên để tổng hợp từ lịch đã duyệt.
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($payrolls as $p): 
                            $basePay = round((float) $p['total_hours'] * (float) $p['hourly_rate']);
                            $isPaid  = ($p['payment_status'] === 'paid');
                        ?>
                            <tr>
                                <td>
                                    <div class="fw-bold"><?= htmlspecialchars($p['full_name']) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($p['employee_code']) ?> · <?= htmlspecialchars($p['role']) ?></small>
                                </td>
                                <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($p['store_name']) ?></span></td>
                                <td class="text-end fw-semibold"><?= number_format($p['total_hours'], 2) ?>h</td>
                                <td class="text-end"><?= number_format($p['hourly_rate'], 0, ',', '.') ?> đ</td>
                                <td class="text-end"><?= number_format($basePay, 0, ',', '.') ?> đ</td>
                                <td class="text-end text-success">+<?= number_format($p['bonus'], 0, ',', '.') ?> đ</td>
                                <td class="text-end text-danger">-<?= number_format($p['deduction'], 0, ',', '.') ?> đ</td>
                                <td class="text-end fw-bold text-primary fs-6"><?= number_format($p['final_salary'], 0, ',', '.') ?> đ</td>
                                <td class="text-center">
                                    <?php if ($isPaid): ?>
                                        <span class="badge bg-success">Đã thanh toán</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">Chưa thanh toán</span>
                                    <?php endif; ?>
                                </td>

                                <?php if ($isManager): ?>
                                    <td class="text-end text-nowrap">
                                        <?php if (!$isPaid): ?>
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-primary btn-edit-payroll"
                                                    data-id="<?= $p['id'] ?>"
                                                    data-name="<?= htmlspecialchars($p['full_name']) ?>"
                                                    data-base="<?= number_format($basePay, 0, ',', '.') ?> đ"
                                                    data-bonus="<?= (float) $p['bonus'] ?>"
                                                    data-deduction="<?= (float) $p['deduction'] ?>"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editPayrollModal"
                                                    title="Điều chỉnh thưởng / khấu trừ">
                                                <i class="bi bi-sliders"></i>
                                            </button>
                                        <?php endif; ?>

                                        <form action="<?= BASE_URL ?>/admin/payrolls/toggle-status" method="POST" class="d-inline">
                                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                            <input type="hidden" name="month" value="<?= htmlspecialchars($selectedMonth) ?>">
                                            <input type="hidden" name="filter_store_id" value="<?= $selectedStoreId ?? '' ?>">
                                            <?php if (!$isPaid): ?>
                                                <button type="submit" class="btn btn-sm btn-success" title="Xác nhận đã thanh toán">
                                                    <i class="bi bi-check-lg"></i> Chi lương
                                                </button>
                                            <?php else: ?>
                                                <button type="submit" class="btn btn-sm btn-outline-secondary" title="Hoàn tác về chưa thanh toán">
                                                    Hoàn tác
                                                </button>
                                            <?php endif; ?>
                                        </form>

                                        <?php if (!$isPaid): ?>
                                            <form action="<?= BASE_URL ?>/admin/payrolls/delete" method="POST" class="d-inline"
                                                  onsubmit="return confirm('Xóa phiếu lương của <?= htmlspecialchars($p['full_name']) ?>?');">
                                                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                                <input type="hidden" name="month" value="<?= htmlspecialchars($selectedMonth) ?>">
                                                <input type="hidden" name="filter_store_id" value="<?= $selectedStoreId ?? '' ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Xóa phiếu lương">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</main>

<!-- Modal Điều chỉnh Thưởng / Khấu trừ -->
<div class="modal fade" id="editPayrollModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="<?= BASE_URL ?>/admin/payrolls/update" method="POST" class="modal-content">
            <input type="hidden" name="id" id="edit_payroll_id">
            <input type="hidden" name="month" value="<?= htmlspecialchars($selectedMonth) ?>">
            <input type="hidden" name="filter_store_id" value="<?= $selectedStoreId ?? '' ?>">

            <div class="modal-header">
                <h5 class="modal-title fw-bold">Điều chỉnh Thưởng & Khấu trừ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-light border small mb-3">
                    Nhân viên: <strong id="edit_payroll_name"></strong><br>
                    Lương theo giờ công: <strong class="text-primary" id="edit_payroll_base"></strong>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Tiền thưởng / Phụ cấp (VNĐ)</label>
                    <input type="number" name="bonus" id="edit_payroll_bonus" class="form-control" min="0" step="1000" required>
                </div>

                <div class="mb-0">
                    <label class="form-label fw-semibold">Tiền khấu trừ / Phạt (VNĐ)</label>
                    <input type="number" name="deduction" id="edit_payroll_deduction" class="form-control" min="0" step="1000" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="submit" class="btn btn-primary fw-semibold">Lưu & Tính lại lương</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.querySelectorAll('.btn-edit-payroll').forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('edit_payroll_id').value        = this.dataset.id;
            document.getElementById('edit_payroll_name').textContent  = this.dataset.name;
            document.getElementById('edit_payroll_base').textContent  = this.dataset.base;
            document.getElementById('edit_payroll_bonus').value     = this.dataset.bonus;
            document.getElementById('edit_payroll_deduction').value = this.dataset.deduction;
        });
    });
</script>
</body>
</html>