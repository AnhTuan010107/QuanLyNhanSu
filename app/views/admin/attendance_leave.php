<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chấm Công & Nghỉ Phép - Quản Lý Nhân Sự</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; }
    </style>
</head>
<body>

<?php require_once __DIR__ . '/../layouts/admin_navbar.php'; ?>

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

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">Quản Lý Chấm Công & Duyệt Nghỉ Phép</h4>
            <p class="text-muted small mb-0">Theo dõi giờ vào/ra thực tế của nhân viên và xét duyệt đơn xin nghỉ phép</p>
        </div>

        <button type="button" class="btn btn-primary fw-semibold" data-bs-toggle="modal" data-bs-target="#manualAttModal">
            <i class="bi bi-plus-circle me-1"></i> Chấm công hộ / Bổ sung
        </button>
    </div>

    <!-- Bộ lọc -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="<?= BASE_URL ?>/admin/attendance" class="row g-3 align-items-end">
                <div class="col-sm-6 col-md-3">
                    <label class="form-label small fw-semibold">Ngày chấm công</label>
                    <input type="date" name="date" class="form-control form-control-sm"
                           value="<?= htmlspecialchars($selectedDate) ?>" onchange="this.form.submit()">
                </div>

                <?php if ($isManager): ?>
                <div class="col-sm-6 col-md-3">
                    <label class="form-label small fw-semibold">Cửa hàng</label>
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

                <div class="col-sm-6 col-md-3">
                    <label class="form-label small fw-semibold">Trạng thái đơn nghỉ phép</label>
                    <select name="leave_status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="all" <?= $leaveStatus === 'all' ? 'selected' : '' ?>>Tất cả đơn nghỉ</option>
                        <option value="pending" <?= $leaveStatus === 'pending' ? 'selected' : '' ?>>Chờ duyệt (<?= $pendingLeavesCount ?>)</option>
                        <option value="approved" <?= $leaveStatus === 'approved' ? 'selected' : '' ?>>Đã duyệt</option>
                        <option value="rejected" <?= $leaveStatus === 'rejected' ? 'selected' : '' ?>>Từ chối</option>
                    </select>
                </div>
            </form>
        </div>
    </div>

    <!-- Bảng 1: Chấm công theo ngày -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 fw-bold">
            <i class="bi bi-fingerprint text-primary me-1"></i>
            Dữ liệu chấm công ngày <?= date('d/m/Y', strtotime($selectedDate)) ?> (<?= count($attendances) ?> lượt)
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nhân viên</th>
                        <th>Cửa hàng</th>
                        <th>Ca làm</th>
                        <th>Giờ vào (Check-in)</th>
                        <th>Giờ ra (Check-out)</th>
                        <th class="text-center">Tình trạng</th>
                        <th>Ghi chú</th>
                        <th class="text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($attendances)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                Chưa có dữ liệu chấm công nào trong ngày <?= date('d/m/Y', strtotime($selectedDate)) ?>.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($attendances as $att): ?>
                            <tr>
                                <td>
                                    <div class="fw-bold"><?= htmlspecialchars($att['full_name']) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($att['employee_code']) ?> · <?= htmlspecialchars($att['role']) ?></small>
                                </td>
                                <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($att['store_name']) ?></span></td>
                                <td>
                                    <?php if (!empty($att['shift_name'])): ?>
                                        <span class="fw-semibold"><?= htmlspecialchars($att['shift_name']) ?></span>
                                        <div class="small text-muted"><?= substr($att['start_time'],0,5) ?>–<?= substr($att['end_time'],0,5) ?></div>
                                    <?php else: ?>
                                        <span class="text-muted small">Ngoài ca cố định</span>
                                    <?php endif; ?>
                                </td>
                                <td class="fw-bold text-success"><?= $att['check_in'] ? substr($att['check_in'], 0, 5) : '--:--' ?></td>
                                <td class="fw-bold text-primary"><?= $att['check_out'] ? substr($att['check_out'], 0, 5) : '<span class="badge bg-warning text-dark fw-normal">Đang trong ca</span>' ?></td>
                                <td class="text-center">
                                    <?php if ($att['status'] === 'late'): ?>
                                        <span class="badge bg-danger">Đi trễ</span>
                                    <?php elseif ($att['status'] === 'absent'): ?>
                                        <span class="badge bg-dark">Vắng mặt</span>
                                    <?php elseif ($att['status'] === 'manual'): ?>
                                        <span class="badge bg-info text-dark">Chấm bổ sung</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Đúng giờ</span>
                                    <?php endif; ?>
                                </td>
                                <td><small class="text-muted"><?= htmlspecialchars($att['note'] ?? '-') ?></small></td>
                                <td class="text-end text-nowrap">
                                    <button type="button"
                                            class="btn btn-sm btn-outline-primary btn-edit-att"
                                            data-id="<?= $att['id'] ?>"
                                            data-name="<?= htmlspecialchars($att['full_name']) ?>"
                                            data-in="<?= $att['check_in'] ? substr($att['check_in'], 0, 5) : '' ?>"
                                            data-out="<?= $att['check_out'] ? substr($att['check_out'], 0, 5) : '' ?>"
                                            data-status="<?= $att['status'] ?>"
                                            data-note="<?= htmlspecialchars($att['note'] ?? '') ?>"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editAttModal">
                                        <i class="bi bi-pencil-square"></i> Sửa
                                    </button>

                                    <form action="<?= BASE_URL ?>/admin/attendance/delete" method="POST" class="d-inline"
                                          onsubmit="return confirm('Xóa lượt chấm công này?');">
                                        <input type="hidden" name="id" value="<?= $att['id'] ?>">
                                        <input type="hidden" name="filter_date" value="<?= htmlspecialchars($selectedDate) ?>">
                                        <input type="hidden" name="filter_store_id" value="<?= $selectedStoreId ?? '' ?>">
                                        <input type="hidden" name="filter_leave_status" value="<?= htmlspecialchars($leaveStatus) ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bảng 2: Duyệt đơn xin nghỉ phép -->
    <div class="card border-0 shadow-sm" id="leaves">
        <div class="card-header bg-white py-3 fw-bold">
            <i class="bi bi-envelope-paper text-warning me-1"></i>
            Danh sách đơn xin nghỉ phép (<?= count($leaveRequests) ?>)
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nhân viên</th>
                        <th>Cửa hàng</th>
                        <th>Loại nghỉ</th>
                        <th>Thời gian nghỉ</th>
                        <th>Lý do</th>
                        <th class="text-center">Trạng thái</th>
                        <th class="text-end">Xét duyệt</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($leaveRequests)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Không có đơn xin nghỉ phép nào.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($leaveRequests as $lr): ?>
                            <tr>
                                <td>
                                    <div class="fw-bold"><?= htmlspecialchars($lr['full_name']) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($lr['employee_code']) ?> · <?= htmlspecialchars($lr['role']) ?></small>
                                </td>
                                <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($lr['store_name']) ?></span></td>
                                <td class="fw-semibold text-primary"><?= htmlspecialchars($lr['leave_type']) ?></td>
                                <td>
                                    <?= date('d/m/Y', strtotime($lr['start_date'])) ?>
                                    <?php if ($lr['end_date'] !== $lr['start_date']): ?>
                                        → <?= date('d/m/Y', strtotime($lr['end_date'])) ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div><?= htmlspecialchars($lr['reason']) ?></div>
                                    <?php if (!empty($lr['admin_note'])): ?>
                                        <small class="text-muted">Ghi chú duyệt: <?= htmlspecialchars($lr['admin_note']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($lr['status'] === 'approved'): ?>
                                        <span class="badge bg-success">Đã duyệt</span>
                                    <?php elseif ($lr['status'] === 'rejected'): ?>
                                        <span class="badge bg-danger">Từ chối</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">Chờ duyệt</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end text-nowrap">
                                    <?php if ($lr['status'] === 'pending'): ?>
                                        <button type="button"
                                                class="btn btn-sm btn-primary btn-review-leave"
                                                data-id="<?= $lr['id'] ?>"
                                                data-name="<?= htmlspecialchars($lr['full_name']) ?>"
                                                data-type="<?= htmlspecialchars($lr['leave_type']) ?>"
                                                data-bs-toggle="modal"
                                                data-bs-target="#reviewLeaveModal">
                                            Xét duyệt
                                        </button>
                                    <?php else: ?>
                                        <span class="text-muted small">Đã xử lý</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</main>

<!-- Modal Chấm công hộ / Bổ sung -->
<div class="modal fade" id="manualAttModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="<?= BASE_URL ?>/admin/attendance/store" method="POST" class="modal-content">
            <input type="hidden" name="filter_date" value="<?= htmlspecialchars($selectedDate) ?>">
            <input type="hidden" name="filter_store_id" value="<?= $selectedStoreId ?? '' ?>">
            <input type="hidden" name="filter_leave_status" value="<?= htmlspecialchars($leaveStatus) ?>">

            <div class="modal-header">
                <h5 class="modal-title fw-bold">Chấm công hộ / Bổ sung giờ công</h5>
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
                    <input type="date" name="work_date" class="form-control" value="<?= htmlspecialchars($selectedDate) ?>" required>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label class="form-label fw-semibold">Giờ vào (Check-in)</label>
                        <input type="time" name="check_in" class="form-control" value="08:00">
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">Giờ ra (Check-out)</label>
                        <input type="time" name="check_out" class="form-control" value="13:00">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Trạng thái</label>
                    <select name="status" class="form-select">
                        <option value="manual">Chấm công bổ sung</option>
                        <option value="on_time">Đúng giờ</option>
                        <option value="late">Đi trễ</option>
                        <option value="absent">Vắng mặt không phép</option>
                    </select>
                </div>

                <div class="mb-0">
                    <label class="form-label fw-semibold">Ghi chú</label>
                    <input type="text" name="note" class="form-control" placeholder="VD: Quên bấm check-in, Trưởng ca xác nhận...">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="submit" class="btn btn-primary fw-semibold">Lưu chấm công</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Sửa chấm công -->
<div class="modal fade" id="editAttModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="<?= BASE_URL ?>/admin/attendance/update" method="POST" class="modal-content">
            <input type="hidden" name="id" id="edit_att_id">
            <input type="hidden" name="filter_date" value="<?= htmlspecialchars($selectedDate) ?>">
            <input type="hidden" name="filter_store_id" value="<?= $selectedStoreId ?? '' ?>">
            <input type="hidden" name="filter_leave_status" value="<?= htmlspecialchars($leaveStatus) ?>">

            <div class="modal-header">
                <h5 class="modal-title fw-bold">Chỉnh sửa giờ chấm công</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-light border small mb-3">
                    Nhân viên: <strong id="edit_att_name"></strong>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label class="form-label fw-semibold">Giờ vào</label>
                        <input type="time" name="check_in" id="edit_att_in" class="form-control">
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">Giờ ra</label>
                        <input type="time" name="check_out" id="edit_att_out" class="form-control">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Tình trạng</label>
                    <select name="status" id="edit_att_status" class="form-select">
                        <option value="on_time">Đúng giờ</option>
                        <option value="late">Đi trễ</option>
                        <option value="manual">Chấm công bổ sung</option>
                        <option value="absent">Vắng mặt</option>
                    </select>
                </div>
                <div class="mb-0">
                    <label class="form-label fw-semibold">Ghi chú</label>
                    <input type="text" name="note" id="edit_att_note" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="submit" class="btn btn-warning fw-semibold">Cập nhật</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Xét duyệt đơn xin nghỉ phép -->
<div class="modal fade" id="reviewLeaveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="<?= BASE_URL ?>/admin/leaves/review" method="POST" class="modal-content">
            <input type="hidden" name="id" id="review_leave_id">
            <input type="hidden" name="filter_date" value="<?= htmlspecialchars($selectedDate) ?>">
            <input type="hidden" name="filter_store_id" value="<?= $selectedStoreId ?? '' ?>">
            <input type="hidden" name="filter_leave_status" value="<?= htmlspecialchars($leaveStatus) ?>">

            <div class="modal-header">
                <h5 class="modal-title fw-bold">Xét duyệt đơn xin nghỉ phép</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-light border small mb-3">
                    Nhân viên: <strong id="review_leave_name"></strong><br>
                    Loại nghỉ: <strong class="text-primary" id="review_leave_type"></strong>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Quyết định xét duyệt <span class="text-danger">*</span></label>
                    <select name="status" class="form-select" required>
                        <option value="approved">Đồng ý duyệt nghỉ phép</option>
                        <option value="rejected">Từ chối đơn nghỉ phép</option>
                    </select>
                </div>

                <div class="mb-0">
                    <label class="form-label fw-semibold">Ghi chú phản hồi cho nhân viên</label>
                    <input type="text" name="admin_note" class="form-control" placeholder="VD: Đã sắp xếp người trực thay ca...">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="submit" class="btn btn-primary fw-semibold">Xác nhận</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.querySelectorAll('.btn-edit-att').forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('edit_att_id').value       = this.dataset.id;
            document.getElementById('edit_att_name').textContent = this.dataset.name;
            document.getElementById('edit_att_in').value       = this.dataset.in;
            document.getElementById('edit_att_out').value      = this.dataset.out;
            document.getElementById('edit_att_status').value   = this.dataset.status;
            document.getElementById('edit_att_note').value     = this.dataset.note;
        });
    });

    document.querySelectorAll('.btn-review-leave').forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('review_leave_id').value         = this.dataset.id;
            document.getElementById('review_leave_name').textContent = this.dataset.name;
            document.getElementById('review_leave_type').textContent = this.dataset.type;
        });
    });
</script>
</body>
</html>