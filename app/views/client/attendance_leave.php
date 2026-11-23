<?php
function calcWorkedDuration($in, $out) {
    if (empty($in) || empty($out)) return '-';
    $s = strtotime("1970-01-01 " . $in);
    $e = strtotime("1970-01-01 " . $out);
    if ($e < $s) $e += 86400;
    $mins = round(($e - $s) / 60);
    $h = floor($mins / 60);
    $m = $mins % 60;
    return "{$h}g {$m}p";
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chấm công & Nghỉ phép - Cổng Nhân Viên</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; }
        .clock-box {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            color: #fff;
            border-radius: 16px;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?= BASE_URL ?>/client/home">
            <i class="bi bi-people-fill text-primary me-1"></i> HR PORTAL
        </a>

        <div class="d-flex align-items-center gap-2 ms-auto">
            <a href="<?= BASE_URL ?>/client/home" class="btn btn-outline-light btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Trang chủ
            </a>
            <a href="<?= BASE_URL ?>/client/schedules" class="btn btn-primary btn-sm">
                <i class="bi bi-calendar-plus me-1"></i> Đăng ký ca
            </a>
            <a href="<?= BASE_URL ?>/logout" class="btn btn-outline-danger btn-sm">
                <i class="bi bi-box-arrow-right"></i>
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

    <div class="row g-4 mb-4">
        <!-- Cột trái: Đồng hồ & Nút Check-in / Check-out -->
        <div class="col-lg-5">
            <div class="clock-box p-4 shadow-sm h-100 d-flex flex-column justify-content-between">
                <div>
                    <div class="small opacity-75">Hôm nay, ngày <?= date('d/m/Y') ?></div>
                    <div class="display-5 fw-bold my-2" id="liveClock"><?= date('H:i:s') ?></div>
                    <div class="small opacity-90">
                        Nhân viên: <strong><?= htmlspecialchars($_SESSION['user']['full_name']) ?></strong>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-top border-white border-opacity-25">
                    <?php if (empty($todayShifts)): ?>
                        <div class="small mb-2 opacity-90">Hôm nay bạn không có ca cố định được duyệt, vẫn có thể chấm công tăng cường:</div>
                        <?php
                            $openAtt = null;
                            foreach ($todayAttendances as $ta) {
                                if (empty($ta['schedule_id'])) {
                                    $openAtt = $ta;
                                    break;
                                }
                            }
                        ?>
                        <?php if (!$openAtt): ?>
                            <form action="<?= BASE_URL ?>/client/attendance/check-in" method="POST">
                                <button type="submit" class="btn btn-light text-primary fw-bold w-100">
                                    <i class="bi bi-box-arrow-in-right me-1"></i> Check-in vào làm ngay
                                </button>
                            </form>
                        <?php elseif (empty($openAtt['check_out'])): ?>
                            <div class="d-flex justify-content-between align-items-center bg-white bg-opacity-10 rounded p-2 mb-2 small">
                                <span>Đã vào ca lúc: <strong><?= substr($openAtt['check_in'], 0, 5) ?></strong></span>
                                <span class="badge bg-warning text-dark">Đang làm việc</span>
                            </div>
                            <form action="<?= BASE_URL ?>/client/attendance/check-out" method="POST">
                                <input type="hidden" name="attendance_id" value="<?= $openAtt['id'] ?>">
                                <button type="submit" class="btn btn-warning fw-bold w-100">
                                    <i class="bi bi-box-arrow-left me-1"></i> Check-out tan ca
                                </button>
                            </form>
                        <?php else: ?>
                            <div class="alert alert-light text-dark mb-0 py-2 small">
                                Đã hoàn thành công hôm nay: <strong><?= substr($openAtt['check_in'], 0, 5) ?> – <?= substr($openAtt['check_out'], 0, 5) ?></strong>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="small fw-semibold mb-2">Ca làm của bạn hôm nay:</div>
                        <?php foreach ($todayShifts as $ts): ?>
                            <div class="bg-white text-dark rounded-3 p-3 mb-2">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-bold text-primary"><?= htmlspecialchars($ts['shift_name']) ?></span>
                                    <span class="badge bg-light text-dark border">
                                        <?= substr($ts['start_time'], 0, 5) ?> – <?= substr($ts['end_time'], 0, 5) ?>
                                    </span>
                                </div>

                                <?php if (empty($ts['attendance_id'])): ?>
                                    <form action="<?= BASE_URL ?>/client/attendance/check-in" method="POST">
                                        <input type="hidden" name="schedule_id" value="<?= $ts['schedule_id'] ?>">
                                        <button type="submit" class="btn btn-success btn-sm fw-semibold w-100">
                                            <i class="bi bi-fingerprint me-1"></i> Check-in vào ca
                                        </button>
                                    </form>
                                <?php elseif (empty($ts['check_out'])): ?>
                                    <div class="d-flex justify-content-between align-items-center small mb-2">
                                        <span>Giờ vào: <strong><?= substr($ts['check_in'], 0, 5) ?></strong></span>
                                        <?= $ts['att_status'] === 'late'
                                            ? '<span class="badge bg-danger">Đi trễ</span>'
                                            : '<span class="badge bg-success">Đúng giờ</span>' ?>
                                    </div>
                                    <form action="<?= BASE_URL ?>/client/attendance/check-out" method="POST">
                                        <input type="hidden" name="attendance_id" value="<?= $ts['attendance_id'] ?>">
                                        <button type="submit" class="btn btn-warning btn-sm fw-semibold w-100">
                                            <i class="bi bi-box-arrow-left me-1"></i> Check-out tan ca
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <div class="small text-success fw-semibold">
                                        <i class="bi bi-check-circle-fill me-1"></i>
                                        Đã chấm công: <?= substr($ts['check_in'], 0, 5) ?> – <?= substr($ts['check_out'], 0, 5) ?>
                                        (<?= calcWorkedDuration($ts['check_in'], $ts['check_out']) ?>)
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Cột phải: Form gửi đơn xin nghỉ phép -->
        <div class="col-lg-7" id="leave-tab">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 fw-bold">
                    <i class="bi bi-envelope-paper text-primary me-1"></i> Tạo đơn xin nghỉ phép / Báo nghỉ ca
                </div>
                <div class="card-body">
                    <form action="<?= BASE_URL ?>/client/leaves/store" method="POST">
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Loại nghỉ phép <span class="text-danger">*</span></label>
                                <select name="leave_type" class="form-select" required>
                                    <option value="Nghỉ việc riêng">Nghỉ việc riêng</option>
                                    <option value="Nghỉ ốm / Khám bệnh">Nghỉ ốm / Khám bệnh</option>
                                    <option value="Nghỉ phép năm">Nghỉ phép năm</option>
                                    <option value="Xin nghỉ đột xuất">Xin nghỉ đột xuất</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Từ ngày <span class="text-danger">*</span></label>
                                <input type="date" name="start_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Đến ngày <span class="text-danger">*</span></label>
                                <input type="date" name="end_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Lý do xin nghỉ <span class="text-danger">*</span></label>
                            <textarea name="reason" class="form-control" rows="3"
                                      placeholder="Nêu rõ lý do xin nghỉ hoặc ca làm cần xin nghỉ để Trưởng ca sắp xếp người thay thế..." required></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary fw-semibold">
                            <i class="bi bi-send me-1"></i> Gửi đơn xin nghỉ phép
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- 2 Bảng lịch sử -->
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <span class="fw-bold"><i class="bi bi-clock-history text-success me-1"></i> Lịch sử chấm công</span>
                    <form method="GET" action="<?= BASE_URL ?>/client/attendance" class="d-flex align-items-center gap-2">
                        <input type="month" name="month" class="form-control form-control-sm"
                               value="<?= htmlspecialchars($selectedMonth) ?>" onchange="this.form.submit()">
                    </form>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th>Ngày</th>
                                <th>Ca làm</th>
                                <th>Vào – Ra</th>
                                <th>Thời lượng</th>
                                <th class="text-center">Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($history)): ?>
                                <tr><td colspan="5" class="text-center text-muted py-4">Chưa có dữ liệu chấm công trong tháng này.</td></tr>
                            <?php else: ?>
                                <?php foreach ($history as $h): ?>
                                    <tr>
                                        <td class="fw-bold"><?= date('d/m/Y', strtotime($h['work_date'])) ?></td>
                                        <td><?= htmlspecialchars($h['shift_name'] ?? 'Chấm công ngoài ca') ?></td>
                                        <td>
                                            <?= $h['check_in'] ? substr($h['check_in'], 0, 5) : '--:--' ?>
                                            –
                                            <?= $h['check_out'] ? substr($h['check_out'], 0, 5) : 'Đang làm' ?>
                                        </td>
                                        <td><?= calcWorkedDuration($h['check_in'], $h['check_out']) ?></td>
                                        <td class="text-center">
                                            <?php if ($h['status'] === 'late'): ?>
                                                <span class="badge bg-danger">Đi trễ</span>
                                            <?php elseif ($h['status'] === 'manual'): ?>
                                                <span class="badge bg-info text-dark">Chấm bổ sung</span>
                                            <?php else: ?>
                                                <span class="badge bg-success">Đúng giờ</span>
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

        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 fw-bold">
                    <i class="bi bi-journal-text text-warning me-1"></i> Danh sách đơn xin nghỉ phép đã gửi
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th>Loại nghỉ</th>
                                <th>Thời gian</th>
                                <th>Lý do / Phản hồi</th>
                                <th class="text-center">Trạng thái</th>
                                <th class="text-end">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($leaveRequests)): ?>
                                <tr><td colspan="5" class="text-center text-muted py-4">Bạn chưa gửi đơn xin nghỉ phép nào.</td></tr>
                            <?php else: ?>
                                <?php foreach ($leaveRequests as $lr): ?>
                                    <tr>
                                        <td class="fw-semibold"><?= htmlspecialchars($lr['leave_type']) ?></td>
                                        <td>
                                            <?= date('d/m/Y', strtotime($lr['start_date'])) ?>
                                            <?php if ($lr['end_date'] !== $lr['start_date']): ?>
                                                <br><small class="text-muted">đến <?= date('d/m/Y', strtotime($lr['end_date'])) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div><?= htmlspecialchars($lr['reason']) ?></div>
                                            <?php if (!empty($lr['admin_note'])): ?>
                                                <small class="text-primary">Phản hồi: <?= htmlspecialchars($lr['admin_note']) ?></small>
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
                                        <td class="text-end">
                                            <?php if ($lr['status'] === 'pending'): ?>
                                                <form action="<?= BASE_URL ?>/client/leaves/cancel" method="POST" class="d-inline"
                                                      onsubmit="return confirm('Hủy đơn xin nghỉ phép này?');">
                                                    <input type="hidden" name="id" value="<?= $lr['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">Hủy</button>
                                                </form>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
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

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    setInterval(() => {
        const now = new Date();
        const pad = n => n < 10 ? '0' + n : n;
        const el = document.getElementById('liveClock');
        if (el) {
            el.textContent = pad(now.getHours()) + ':' + pad(now.getMinutes()) + ':' + pad(now.getSeconds());
        }
    }, 1000);
</script>
</body>
</html>