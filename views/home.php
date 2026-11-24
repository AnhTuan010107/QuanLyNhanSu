<?php
$user = $_SESSION['user'];
$rawRole = isset($user['role']) && trim($user['role']) !== ''
    ? trim($user['role'])
    : (isset($user['position']) && trim($user['position']) !== ''
        ? trim($user['position'])
        : 'Nhân viên');

$roleKey = mb_strtolower($rawRole, 'UTF-8');
if ($roleKey === 'pt' || $roleKey === 'part time' || $roleKey === 'part-time') {
    $role = 'Part-time';
} elseif ($roleKey === 'học việc' || $roleKey === 'hoc viec') {
    $role = 'Học việc';
} elseif ($roleKey === 'trưởng ca' || $roleKey === 'trưởng ca (tc)' || $roleKey === 'tc') {
    $role = 'Trưởng ca (TC)';
} elseif ($roleKey === 'quản lý' || $roleKey === 'quản lí' || $roleKey === 'admin') {
    $role = 'Quản lý';
} else {
    $role = $rawRole;
}

$roleBadgeClassMap = [
    'Học việc'      => 'bg-secondary text-white',
    'Part-time'     => 'bg-info text-dark',
    'Nhân viên'    => 'bg-primary text-white',
    'Trưởng ca (TC)' => 'bg-warning text-dark',
    'Quản lý'      => 'bg-success text-white'
];
$roleBadgeClass = isset($roleBadgeClassMap[$role]) ? $roleBadgeClassMap[$role] : 'bg-info text-dark';

$isManager = ($role === 'Quản lý');
$isSup     = ($role === 'Trưởng ca (TC)');

$defaultTab = $isManager ? 'branches' : 'overview';
$tab = isset($_GET['tab']) ? $_GET['tab'] : $defaultTab;

$thuMap = [
    'Mon' => 'Thứ Hai',
    'Tue' => 'Thứ Ba',
    'Wed' => 'Thứ Tư',
    'Thu' => 'Thứ Năm',
    'Fri' => 'Thứ Sáu',
    'Sat' => 'Thứ Bảy',
    'Sun' => 'Chủ Nhật'
];
$todayThu = isset($thuMap[date('D')]) ? $thuMap[date('D')] : date('D');
$tomorrowThu = isset($thuMap[date('D', strtotime('+1 day'))]) ? $thuMap[date('D', strtotime('+1 day'))] : '';

$slotNameMap = [
    'morning'   => 'Sáng',
    'afternoon' => 'Chiều',
    'evening'   => 'Tối',
    'off'       => 'Nghỉ'
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang chủ - HR Workspace</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Be Vietnam Pro', sans-serif;
            background-color: #060913;
            color: #f1f5f9;
            min-height: 100dvh;
            margin: 0;
        }
        .topbar {
            background-color: #080d1a;
            border-bottom: 1px solid rgba(14, 165, 233, 0.22);
        }
        .main-shell {
            max-width: 1060px;
            margin: 0 auto;
            padding: 20px 14px 40px;
        }
        .tab-btn {
            text-decoration: none;
            padding: 8px 14px;
            border-radius: 10px;
            font-size: 0.84rem;
            font-weight: 600;
            color: #94a3b8;
            background-color: #0d1526;
            border: 1px solid rgba(148, 163, 184, 0.2);
            white-space: nowrap;
        }
        .tab-btn.active {
            color: #ffffff;
            background-color: #2563eb;
            border-color: #2563eb;
        }
        .box-dark {
            background-color: #0d1526;
            border: 1px solid rgba(14, 165, 233, 0.22);
            border-radius: 16px;
            padding: 16px 18px;
        }
        .progress {
            height: 8px;
            background-color: rgba(148, 163, 184, 0.2);
            border-radius: 999px;
            margin: 12px 0;
        }
        .progress-bar {
            background-color: #0ea5e9;
            border-radius: 999px;
        }
        .day-mini-card {
            background-color: rgba(14, 165, 233, 0.08);
            border: 1px solid rgba(14, 165, 233, 0.25);
            border-radius: 14px;
            padding: 12px 14px;
        }
        .week-switch-btn {
            text-decoration: none;
            color: #94a3b8;
            background-color: #0d1526;
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 12px;
            padding: 10px 12px;
            text-align: center;
            flex: 1;
            min-width: 140px;
            font-size: 0.84rem;
        }
        .week-switch-btn.active {
            color: #ffffff;
            background-color: rgba(37, 99, 235, 0.28);
            border-color: #38bdf8;
            font-weight: 600;
        }
        .slot-chip {
            border-radius: 10px;
            padding: 5px 10px;
            font-size: 0.78rem;
            font-weight: 600;
            border: 1px solid rgba(148, 163, 184, 0.22);
            background-color: #080d1a;
            color: #cbd5e1;
        }
        .slot-chip.active {
            background-color: #0284c7;
            border-color: #38bdf8;
            color: #ffffff;
        }
        .dark-select {
            background-color: #080d1a;
            color: #f1f5f9;
            border: 1px solid rgba(148, 163, 184, 0.25);
            border-radius: 10px;
            padding: 6px 10px;
            font-size: 0.85rem;
        }
        .timeline-track {
            position: relative;
            height: 12px;
            background-color: #080d1a;
            border-radius: 999px;
            border: 1px solid rgba(148, 163, 184, 0.16);
            overflow: hidden;
        }
        .timeline-bar {
            position: absolute;
            top: 0;
            bottom: 0;
            background: linear-gradient(90deg, #2563eb, #0ea5e9);
            border-radius: 999px;
        }
        .table-dark-custom {
            --bs-table-bg: transparent;
            --bs-table-color: #f1f5f9;
            --bs-table-border-color: rgba(148, 163, 184, 0.18);
        }
    </style>
</head>
<body>

<header class="topbar py-2 px-3">
    <div class="d-flex justify-content-between align-items-center" style="max-width: 1060px; margin: 0 auto;">
        <a href="<?php echo BASE_URL; ?>/?page=home" class="text-white text-decoration-none fw-bold">
            <i class="bi bi-layers-half text-info me-1"></i> HR WORKSPACE
        </a>
        <div class="d-flex align-items-center gap-2">
            <span class="small text-info">
                <?php echo htmlspecialchars($user['full_name']); ?>
            </span>
            <span class="badge <?php echo $roleBadgeClass; ?>">
                <?php echo htmlspecialchars($role); ?>
            </span>
            <a href="<?php echo BASE_URL; ?>/?page=logout" class="btn btn-sm btn-outline-info">
                Đăng xuất
            </a>
        </div>
    </div>
</header>

<main class="main-shell">
    <div class="d-flex gap-2 overflow-auto pb-2 mb-3">
        <?php if ($isManager): ?>
            <a href="<?php echo BASE_URL; ?>/?page=home&tab=branches" class="tab-btn <?php if ($tab === 'branches') echo 'active'; ?>">
                Cửa hàng & Chi nhánh
            </a>
            <a href="<?php echo BASE_URL; ?>/?page=home&tab=staff_info" class="tab-btn <?php if ($tab === 'staff_info') echo 'active'; ?>">
                Quản lý nhân viên
            </a>
            <a href="<?php echo BASE_URL; ?>/?page=home&tab=store_schedule" class="tab-btn <?php if ($tab === 'store_schedule') echo 'active'; ?>">
                Lịch trình cửa hàng & NV
            </a>
            <a href="<?php echo BASE_URL; ?>/?page=home&tab=leaves" class="tab-btn <?php if ($tab === 'leaves') echo 'active'; ?>">
                Duyệt đơn nghỉ phép
            </a>
        <?php elseif ($isSup): ?>
            <a href="<?php echo BASE_URL; ?>/?page=home&tab=overview" class="tab-btn <?php if ($tab === 'overview') echo 'active'; ?>">
                Tổng quan
            </a>
            <a href="<?php echo BASE_URL; ?>/?page=home&tab=shifts" class="tab-btn <?php if ($tab === 'shifts') echo 'active'; ?>">
                Ca làm của tôi
            </a>
            <a href="<?php echo BASE_URL; ?>/?page=home&tab=attendance" class="tab-btn <?php if ($tab === 'attendance') echo 'active'; ?>">
                Chấm công & Nghỉ phép
            </a>
            <a href="<?php echo BASE_URL; ?>/?page=home&tab=manage_shifts" class="tab-btn <?php if ($tab === 'manage_shifts') echo 'active'; ?>">
                Quản lý ca
            </a>
            <a href="<?php echo BASE_URL; ?>/?page=home&tab=staff_info" class="tab-btn <?php if ($tab === 'staff_info') echo 'active'; ?>">
                Quản lý nhân viên
            </a>
            <a href="<?php echo BASE_URL; ?>/?page=home&tab=assign_schedule" class="tab-btn <?php if ($tab === 'assign_schedule') echo 'active'; ?>">
                Sắp xếp lịch
            </a>
            <a href="<?php echo BASE_URL; ?>/?page=home&tab=track_shifts" class="tab-btn <?php if ($tab === 'track_shifts') echo 'active'; ?>">
                Theo dõi ca làm
            </a>
            <a href="<?php echo BASE_URL; ?>/?page=home&tab=leaves" class="tab-btn <?php if ($tab === 'leaves') echo 'active'; ?>">
                Duyệt đơn nghỉ phép
            </a>
        <?php else: ?>
            <a href="<?php echo BASE_URL; ?>/?page=home&tab=overview" class="tab-btn <?php if ($tab === 'overview') echo 'active'; ?>">
                Tổng quan
            </a>
            <a href="<?php echo BASE_URL; ?>/?page=home&tab=shifts" class="tab-btn <?php if ($tab === 'shifts') echo 'active'; ?>">
                Ca làm
            </a>
            <a href="<?php echo BASE_URL; ?>/?page=home&tab=attendance" class="tab-btn <?php if ($tab === 'attendance') echo 'active'; ?>">
                Chấm công & Nghỉ phép
            </a>
        <?php endif; ?>
    </div>

    <?php if ($opMsg !== ""): ?>
        <div class="alert alert-info py-2 px-3 small rounded-3">
            <?php echo htmlspecialchars($opMsg); ?>
        </div>
    <?php endif; ?>

    <?php if ($tab === 'overview' && !$isManager): ?>
        <div class="box-dark mb-3 d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <div class="small text-secondary">Nhân viên</div>
                <div class="fw-bold text-white"><?php echo htmlspecialchars($user['full_name']); ?></div>
                <div class="small text-secondary mt-1">
                    Chi nhánh: <span class="text-info"><?php echo htmlspecialchars(!empty($user['store_name']) ? $user['store_name'] : 'Chưa gán'); ?></span>
                    · Lương/giờ: <span class="text-info fw-semibold"><?php echo number_format((float)$overview['hourly_rate'], 0, ',', '.'); ?>đ</span>
                </div>
            </div>
            <div class="text-end">
                <div class="small text-secondary mb-1">Cấp bậc / Vai trò</div>
                <span class="badge <?php echo $roleBadgeClass; ?> fs-6"><?php echo htmlspecialchars($role); ?></span>
            </div>
        </div>

        <a href="<?php echo BASE_URL; ?>/?page=home&tab=shifts" class="box-dark d-flex justify-content-between align-items-center text-decoration-none text-white mb-3">
            <div>
                <div class="fw-bold">
                    <?php if ($overview['today_shift']): ?>
                        Ca hôm nay: <?php echo htmlspecialchars($overview['today_shift']['shift_name']); ?>
                        (<?php echo substr($overview['today_shift']['start_time'], 0, 5); ?> - <?php echo substr($overview['today_shift']['end_time'], 0, 5); ?>)
                    <?php else: ?>
                        Hôm nay chưa có ca làm
                    <?php endif; ?>
                </div>
                <div class="small text-secondary">Bấm để xem lịch & đăng ký giờ rảnh</div>
            </div>
            <i class="bi bi-chevron-right text-info"></i>
        </a>

        <div class="mb-3">
            <div class="fw-bold mb-2">Việc cần làm</div>
            <div class="box-dark small">
                <?php if ($overview['pending_leaves'] > 0): ?>
                    Bạn đang có <strong><?php echo $overview['pending_leaves']; ?></strong> đơn nghỉ phép đang chờ duyệt.
                <?php else: ?>
                    Không có việc gì đang chờ. Làm việc thật tốt ca hôm nay nhé!
                <?php endif; ?>
            </div>
        </div>

        <div class="mb-3">
            <div class="fw-bold mb-2">Lộ trình của bạn</div>
            <div class="box-dark">
                <div class="d-flex justify-content-between small">
                    <span>Giờ làm tích lũy</span>
                    <strong class="text-info"><?php echo $overview['current_hours']; ?> / <?php echo $overview['target_hours']; ?> giờ</strong>
                </div>
                <div class="progress">
                    <div class="progress-bar" style="width: <?php echo $overview['percent']; ?>%;"></div>
                </div>
                <div class="small text-secondary">
                    <?php if ($overview['remaining_hours'] > 0): ?>
                        Còn <strong class="text-white"><?php echo $overview['remaining_hours']; ?> giờ</strong> nữa là đủ giờ mục tiêu.
                    <?php else: ?>
                        Đã hoàn thành đủ <strong class="text-white"><?php echo $overview['target_hours']; ?> giờ</strong> mục tiêu.
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-6">
                <div class="box-dark h-100">
                    <div class="small text-secondary mb-1">Lương tạm tính</div>
                    <div class="fs-4 fw-bold text-white">
                        <?php echo number_format($overview['est_salary'], 0, ',', '.'); ?>đ
                    </div>
                    <div class="small text-secondary mt-1">
                        <?php echo $overview['current_hours']; ?> giờ · <?php echo $overview['work_days']; ?> ngày công
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="box-dark h-100">
                    <div class="small text-secondary mb-1">Bảng vàng CH</div>
                    <div class="fs-4 fw-bold text-white">Chưa xếp</div>
                    <div class="small text-secondary mt-1">Được khen để lên bảng nhé!</div>
                </div>
            </div>
        </div>

    <?php elseif ($tab === 'shifts' && !$isManager): ?>
        <h5 class="fw-bold mb-1">Lịch làm</h5>
        <p class="small text-secondary mb-3">Ca của bạn · đăng ký giờ rảnh tuần sau</p>

        <?php if ($shiftMsg !== ""): ?>
            <div class="alert alert-info py-2 px-3 small rounded-3">
                <?php echo htmlspecialchars($shiftMsg); ?>
            </div>
        <?php endif; ?>

        <div class="row g-2 mb-3">
            <div class="col-sm-6">
                <div class="day-mini-card h-100">
                    <div class="small text-info">Hôm nay · <?php echo $todayThu . " " . date('d/m'); ?></div>
                    <div class="fw-bold text-white">
                        <?php echo $todaySummary['my_range'] !== "" ? htmlspecialchars($todaySummary['my_range']) : "Nghỉ / Chưa có ca"; ?>
                    </div>
                    <div class="small text-secondary">
                        Cùng ca: <?php echo count($todaySummary['mates']) > 0 ? htmlspecialchars(implode(', ', $todaySummary['mates'])) : "Chưa có"; ?>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="day-mini-card h-100">
                    <div class="small text-info">Ngày mai · <?php echo $tomorrowThu . " " . date('d/m', strtotime('+1 day')); ?></div>
                    <div class="fw-bold text-white">
                        <?php echo $tomorrowSummary['my_range'] !== "" ? htmlspecialchars($tomorrowSummary['my_range']) : "Nghỉ / Chưa có ca"; ?>
                    </div>
                    <div class="small text-secondary">
                        Cùng ca: <?php echo count($tomorrowSummary['mates']) > 0 ? htmlspecialchars(implode(', ', $tomorrowSummary['mates'])) : "Chưa có"; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 flex-wrap mb-3">
            <a href="<?php echo BASE_URL; ?>/?page=home&tab=shifts&week=0" class="week-switch-btn <?php if ($weekOffset === 0) echo 'active'; ?>">
                <div>Tuần này</div>
                <small><?php echo date('d/m', strtotime($startWeek)) . " – " . date('d/m', strtotime($endWeek)); ?></small>
            </a>
            <?php
            $w1Start = date('d/m', strtotime('monday next week'));
            $w1End   = date('d/m', strtotime('sunday next week'));
            $w2Start = date('d/m', strtotime('+2 weeks monday', strtotime('monday this week')));
            $w2End   = date('d/m', strtotime('+2 weeks sunday', strtotime('monday this week')));
            ?>
            <a href="<?php echo BASE_URL; ?>/?page=home&tab=shifts&week=1" class="week-switch-btn <?php if ($weekOffset === 1) echo 'active'; ?>">
                <div>Tuần sau</div>
                <small><?php echo $w1Start . " – " . $w1End; ?></small>
            </a>
            <a href="<?php echo BASE_URL; ?>/?page=home&tab=shifts&week=2" class="week-switch-btn <?php if ($weekOffset === 2) echo 'active'; ?>">
                <div>Tuần sau nữa</div>
                <small><?php echo $w2Start . " – " . $w2End; ?></small>
            </a>
        </div>

        <?php if ($weekOffset === 0): ?>
            <div class="box-dark mb-3">
                <div class="fw-bold mb-3">Ca của bạn</div>
                <?php foreach ($weekDays as $wd): ?>
                    <?php
                    $dKey = $wd['date'];
                    $dayShifts = isset($myWeekSchedules[$dKey]) ? $myWeekSchedules[$dKey] : [];
                    ?>
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom border-secondary border-opacity-25">
                        <div class="d-flex align-items-center gap-3">
                            <div style="width: 38px;">
                                <div class="fw-bold small text-info"><?php echo $wd['label']; ?></div>
                                <small class="text-secondary"><?php echo $wd['dm']; ?></small>
                            </div>
                            <?php if (count($dayShifts) > 0): ?>
                                <?php foreach ($dayShifts as $ds): ?>
                                    <div>
                                        <span class="fw-bold text-white">
                                            <?php echo (int)substr($ds['start_time'], 0, 2) . "h–" . (int)substr($ds['end_time'], 0, 2) . "h"; ?>
                                        </span>
                                        <span class="small text-info ms-1"><?php echo htmlspecialchars($ds['shift_code']); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <span class="small text-secondary">Nghỉ</span>
                            <?php endif; ?>
                        </div>
                        <a href="<?php echo BASE_URL; ?>/?page=home&tab=attendance" class="btn btn-sm btn-outline-info py-0 px-2" style="font-size: 0.78rem;">
                            Xin nghỉ
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <form method="POST" class="box-dark" id="availForm">
                <input type="hidden" name="action" value="save_availability">
                <input type="hidden" name="week" value="<?php echo $weekOffset; ?>">

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="fw-bold">Giờ rảnh của bạn</div>
                    <span class="small text-info">Tích nhiều ca nếu rảnh cả ngày</span>
                </div>

                <?php foreach ($weekDays as $wd): ?>
                    <?php
                    $dKey = $wd['date'];
                    $saved = isset($userAvailMap[$dKey]) ? $userAvailMap[$dKey] : null;
                    $curSlotRaw = $saved ? $saved['slot_type'] : '';
                    $selectedSlots = $curSlotRaw !== '' ? explode(',', $curSlotRaw) : [];
                    $curStart = $saved ? $saved['start_hour'] : '';
                    $curEnd = $saved ? $saved['end_hour'] : '';
                    ?>
                    <div class="py-2 border-bottom border-secondary border-opacity-25 avail-row">
                        <input type="hidden" name="work_date[]" value="<?php echo $dKey; ?>">
                        <input type="hidden" name="slot_type[]" class="slot-hidden" value="<?php echo htmlspecialchars($curSlotRaw); ?>">

                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                            <div class="d-flex align-items-center gap-2">
                                <div style="width: 42px;">
                                    <div class="fw-bold small text-info"><?php echo $wd['label']; ?></div>
                                    <small class="text-secondary"><?php echo $wd['dm']; ?></small>
                                </div>

                                <select name="start_hour[]" class="dark-select start-sel">
                                    <option value="">--</option>
                                    <?php for ($h = 8; $h <= 22; $h++): ?>
                                        <option value="<?php echo $h; ?>" <?php if ((string)$curStart === (string)$h) echo 'selected'; ?>>
                                            <?php echo $h; ?>h
                                        </option>
                                    <?php endfor; ?>
                                </select>
                                <span>-</span>
                                <select name="end_hour[]" class="dark-select end-sel">
                                    <option value="">--</option>
                                    <?php for ($h = 9; $h <= 23; $h++): ?>
                                        <option value="<?php echo $h; ?>" <?php if ((string)$curEnd === (string)$h) echo 'selected'; ?>>
                                            <?php echo $h; ?>h
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>

                            <div class="d-flex gap-1 flex-wrap">
                                <button type="button" class="slot-chip <?php if (in_array('morning', $selectedSlots)) echo 'active'; ?>" data-slot="morning" data-s="8" data-e="13">
                                    <i class="bi bi-check2 me-1"></i>Sáng
                                </button>
                                <button type="button" class="slot-chip <?php if (in_array('afternoon', $selectedSlots)) echo 'active'; ?>" data-slot="afternoon" data-s="13" data-e="18">
                                    <i class="bi bi-check2 me-1"></i>Chiều
                                </button>
                                <button type="button" class="slot-chip <?php if (in_array('evening', $selectedSlots)) echo 'active'; ?>" data-slot="evening" data-s="18" data-e="23">
                                    <i class="bi bi-check2 me-1"></i>Tối
                                </button>
                                <button type="button" class="slot-chip <?php if (in_array('off', $selectedSlots)) echo 'active'; ?>" data-slot="off" data-s="" data-e="">
                                    Nghỉ
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="mt-3 mb-3">
                    <label class="form-label small text-secondary">Ghi chú</label>
                    <?php
                    $firstAvail = reset($userAvailMap);
                    $existingNote = ($firstAvail && isset($firstAvail['note'])) ? $firstAvail['note'] : '';
                    ?>
                    <input type="text" name="avail_note" class="form-control dark-select w-100"
                           placeholder="VD: Rảnh Sáng + Chiều..."
                           value="<?php echo htmlspecialchars($existingNote); ?>">
                </div>

                <button type="submit" class="btn btn-primary w-100 fw-semibold py-2 rounded-3">
                    Gửi giờ rảnh
                </button>
            </form>
        <?php endif; ?>

    <?php elseif ($tab === 'attendance' && !$isManager): ?>
        <h5 class="fw-bold mb-1">Chấm công & Nghỉ phép</h5>
        <p class="small text-secondary mb-3">Check-in / Check-out hôm nay và gửi đơn xin nghỉ phép</p>

        <?php if ($attMsg !== ""): ?>
            <div class="alert alert-info py-2 px-3 small rounded-3">
                <?php echo htmlspecialchars($attMsg); ?>
            </div>
        <?php endif; ?>

        <div class="box-dark mb-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <div class="fw-bold text-white">Chấm công hôm nay (<?php echo date('d/m/Y'); ?>)</div>
                    <?php if ($todayAttendance): ?>
                        <div class="small text-secondary mt-1">
                            Vào ca: <strong class="text-info"><?php echo htmlspecialchars(substr($todayAttendance['check_in'], 11, 5)); ?></strong>
                            · Ra ca:
                            <?php if ($todayAttendance['check_out']): ?>
                                <strong class="text-info"><?php echo htmlspecialchars(substr($todayAttendance['check_out'], 11, 5)); ?></strong>
                                (<?php echo $todayAttendance['work_hours']; ?> giờ)
                            <?php else: ?>
                                <span class="text-warning">Chưa check-out</span>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="small text-secondary mt-1">Bạn chưa check-in hôm nay.</div>
                    <?php endif; ?>
                </div>

                <div>
                    <?php if (!$todayAttendance): ?>
                        <form method="POST">
                            <input type="hidden" name="action" value="check_in">
                            <button type="submit" class="btn btn-primary fw-semibold px-4 rounded-3">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Check-in
                            </button>
                        </form>
                    <?php elseif (!$todayAttendance['check_out']): ?>
                        <form method="POST">
                            <input type="hidden" name="action" value="check_out">
                            <input type="hidden" name="attendance_id" value="<?php echo $todayAttendance['id']; ?>">
                            <button type="submit" class="btn btn-info fw-semibold px-4 rounded-3 text-dark">
                                <i class="bi bi-box-arrow-left me-1"></i> Check-out
                            </button>
                        </form>
                    <?php else: ?>
                        <span class="badge bg-success px-3 py-2 rounded-3">Đã hoàn tất chấm công</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="box-dark mb-3">
            <div class="fw-bold mb-2">Gửi đơn xin nghỉ phép</div>
            <form method="POST" class="row g-2 align-items-end">
                <input type="hidden" name="action" value="create_leave">
                <div class="col-sm-4">
                    <label class="form-label small text-secondary">Ngày nghỉ</label>
                    <input type="date" name="leave_date" class="form-control dark-select w-100"
                           min="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="col-sm-5">
                    <label class="form-label small text-secondary">Lý do</label>
                    <input type="text" name="reason" class="form-control dark-select w-100"
                           placeholder="VD: Bận lịch học / việc gia đình" required>
                </div>
                <div class="col-sm-3">
                    <button type="submit" class="btn btn-primary w-100 fw-semibold rounded-3">
                        Gửi đơn
                    </button>
                </div>
            </form>
        </div>

        <div class="box-dark mb-3">
            <div class="fw-bold mb-2">Đơn nghỉ phép của bạn</div>
            <?php if (count($leaveList) > 0): ?>
                <?php foreach ($leaveList as $lv): ?>
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom border-secondary border-opacity-25 small">
                        <div>
                            <span class="text-info fw-semibold"><?php echo date('d/m/Y', strtotime($lv['leave_date'])); ?></span>
                            · <?php echo htmlspecialchars($lv['reason']); ?>
                        </div>
                        <?php if ($lv['status'] === 'approved'): ?>
                            <span class="badge bg-success">Đã duyệt</span>
                        <?php elseif ($lv['status'] === 'rejected'): ?>
                            <span class="badge bg-danger">Từ chối</span>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark">Chờ duyệt</span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="small text-secondary">Chưa có đơn nghỉ phép nào.</div>
            <?php endif; ?>
        </div>

        <div class="box-dark">
            <div class="fw-bold mb-2">Lịch sử chấm công gần đây</div>
            <?php if (count($attendanceList) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-dark-custom table-sm align-middle mb-0 small">
                        <thead>
                            <tr>
                                <th>Ngày</th>
                                <th>Vào ca</th>
                                <th>Ra ca</th>
                                <th>Giờ công</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($attendanceList as $attRow): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y', strtotime($attRow['work_date'])); ?></td>
                                    <td><?php echo $attRow['check_in'] ? substr($attRow['check_in'], 11, 5) : '--'; ?></td>
                                    <td><?php echo $attRow['check_out'] ? substr($attRow['check_out'], 11, 5) : '--'; ?></td>
                                    <td class="text-info fw-semibold"><?php echo $attRow['work_hours']; ?>h</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="small text-secondary">Chưa có dữ liệu chấm công.</div>
            <?php endif; ?>
        </div>

    <?php elseif ($tab === 'manage_shifts' && ($isSup || $isManager)): ?>
        <h5 class="fw-bold mb-1">Quản lý ca</h5>
        <p class="small text-secondary mb-3">Danh sách khung ca làm việc và tạo ca mới</p>

        <div class="box-dark mb-3">
            <div class="fw-bold mb-2">Thêm khung ca mới</div>
            <form method="POST" class="row g-2 align-items-end">
                <input type="hidden" name="action" value="create_shift">
                <div class="col-sm-2">
                    <label class="form-label small text-secondary">Mã ca</label>
                    <input type="text" name="shift_code" class="form-control dark-select w-100" placeholder="P4" required>
                </div>
                <div class="col-sm-4">
                    <label class="form-label small text-secondary">Tên ca</label>
                    <input type="text" name="shift_name" class="form-control dark-select w-100" placeholder="Ca tăng cường" required>
                </div>
                <div class="col-sm-2">
                    <label class="form-label small text-secondary">Bắt đầu</label>
                    <input type="time" name="start_time" class="form-control dark-select w-100" value="08:00" required>
                </div>
                <div class="col-sm-2">
                    <label class="form-label small text-secondary">Kết thúc</label>
                    <input type="time" name="end_time" class="form-control dark-select w-100" value="13:00" required>
                </div>
                <div class="col-sm-2">
                    <button type="submit" class="btn btn-primary w-100 fw-semibold rounded-3">Thêm ca</button>
                </div>
            </form>
        </div>

        <div class="box-dark">
            <div class="fw-bold mb-2">Các ca hiện có (<?php echo count($allShifts); ?>)</div>
            <div class="table-responsive">
                <table class="table table-dark-custom table-sm align-middle mb-0 small">
                    <thead>
                        <tr>
                            <th>Mã ca</th>
                            <th>Tên ca</th>
                            <th>Khung giờ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($allShifts as $sh): ?>
                            <tr>
                                <td class="fw-bold text-info"><?php echo htmlspecialchars($sh['shift_code']); ?></td>
                                <td><?php echo htmlspecialchars($sh['shift_name']); ?></td>
                                <td><?php echo substr($sh['start_time'], 0, 5); ?> - <?php echo substr($sh['end_time'], 0, 5); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <?php elseif ($tab === 'assign_schedule' && ($isSup || $isManager)): ?>
        <h5 class="fw-bold mb-1">Sắp xếp lịch</h5>
        <p class="small text-secondary mb-3">Phân ca cho nhân viên dựa trên giờ rảnh đã đăng ký</p>

        <div class="box-dark mb-3">
            <div class="fw-bold mb-2">Xếp ca làm việc</div>
            <form method="POST" class="row g-2 align-items-end">
                <input type="hidden" name="action" value="assign_schedule">
                <input type="hidden" name="assign_store_id" value="<?php echo (int)$storeId; ?>">
                <div class="col-sm-4">
                    <label class="form-label small text-secondary">Nhân viên</label>
                    <select name="target_user_id" class="dark-select w-100" required>
                        <?php foreach ($allStaffList as $st): ?>
                            <option value="<?php echo $st['id']; ?>">
                                <?php echo htmlspecialchars($st['full_name']); ?> (<?php echo htmlspecialchars($st['role']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-sm-3">
                    <label class="form-label small text-secondary">Ngày làm</label>
                    <input type="date" name="work_date" class="dark-select w-100" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="col-sm-3">
                    <label class="form-label small text-secondary">Ca làm</label>
                    <select name="shift_id" class="dark-select w-100" required>
                        <?php foreach ($allShifts as $sh): ?>
                            <option value="<?php echo $sh['id']; ?>">
                                <?php echo htmlspecialchars($sh['shift_code']); ?> (<?php echo substr($sh['start_time'], 0, 5); ?>-<?php echo substr($sh['end_time'], 0, 5); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-sm-2">
                    <button type="submit" class="btn btn-primary w-100 fw-semibold rounded-3">Xếp ca</button>
                </div>
            </form>
        </div>

        <div class="box-dark">
            <div class="fw-bold mb-2">Giờ rảnh nhân viên đã gửi</div>
            <?php if (count($upcomingAvails) > 0): ?>
                <?php foreach ($upcomingAvails as $av): ?>
                    <?php
                    $parts = explode(',', $av['slot_type']);
                    $labels = [];
                    foreach ($parts as $p) {
                        $labels[] = isset($slotNameMap[$p]) ? $slotNameMap[$p] : $p;
                    }
                    ?>
                    <div class="small py-2 d-flex flex-wrap justify-content-between border-bottom border-secondary border-opacity-25">
                        <span>
                            <strong class="text-white"><?php echo htmlspecialchars($av['full_name']); ?></strong>
                            · <?php echo date('d/m', strtotime($av['work_date'])); ?>:
                            <span class="text-info"><?php echo htmlspecialchars(implode(' + ', $labels)); ?></span>
                            <?php if ($av['start_hour'] && $av['end_hour']): ?>
                                (<?php echo $av['start_hour']; ?>h-<?php echo $av['end_hour']; ?>h)
                            <?php endif; ?>
                        </span>
                        <?php if (!empty($av['note'])): ?>
                            <span class="text-secondary">Ghi chú: <?php echo htmlspecialchars($av['note']); ?></span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="small text-secondary">Chưa có giờ rảnh nào được đăng ký.</div>
            <?php endif; ?>
        </div>

    <?php elseif ($tab === 'track_shifts' && ($isSup || $isManager)): ?>
        <h5 class="fw-bold mb-1">Theo dõi ca làm</h5>
        <p class="small text-secondary mb-3">Theo dõi phân ca và check-in/check-out trong ngày</p>

        <div class="box-dark mb-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div class="fw-bold">Lịch phân ca ngày <?php echo date('d/m/Y', strtotime($selectedStoreDate)); ?></div>
                <form method="GET" class="d-flex gap-2">
                    <input type="hidden" name="page" value="home">
                    <input type="hidden" name="tab" value="track_shifts">
                    <input type="date" name="cdate" class="dark-select" value="<?php echo htmlspecialchars($selectedStoreDate); ?>">
                    <button type="submit" class="btn btn-sm btn-outline-info">Xem</button>
                </form>
            </div>

            <?php if (count($storeTimeline) > 0): ?>
                <?php foreach ($storeTimeline as $person): ?>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="text-white fw-semibold"><?php echo htmlspecialchars($person['full_name']); ?></span>
                            <span class="text-info"><?php echo htmlspecialchars(implode(', ', $person['ranges'])); ?></span>
                        </div>
                        <div class="timeline-track">
                            <?php foreach ($person['blocks'] as $blk): ?>
                                <?php
                                $leftPct  = max(0, (($blk['start'] - 8) / 15) * 100);
                                $widthPct = max(5, ((min(23, $blk['end']) - max(8, $blk['start'])) / 15) * 100);
                                ?>
                                <div class="timeline-bar" style="left: <?php echo $leftPct; ?>%; width: <?php echo $widthPct; ?>%;"></div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="small text-secondary">Chưa có ca nào được xếp trong ngày này.</div>
            <?php endif; ?>
        </div>

        <div class="box-dark">
            <div class="fw-bold mb-2">Chấm công thực tế trong ngày</div>
            <?php if (count($storeAttendances) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-dark-custom table-sm align-middle mb-0 small">
                        <thead>
                            <tr>
                                <th>Nhân viên</th>
                                <th>Vào ca</th>
                                <th>Ra ca</th>
                                <th>Giờ công</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($storeAttendances as $sa): ?>
                                <tr>
                                    <td class="fw-semibold text-white"><?php echo htmlspecialchars($sa['full_name']); ?></td>
                                    <td><?php echo $sa['check_in'] ? substr($sa['check_in'], 11, 5) : '--'; ?></td>
                                    <td><?php echo $sa['check_out'] ? substr($sa['check_out'], 11, 5) : '<span class="text-warning">Đang làm</span>'; ?></td>
                                    <td class="text-info"><?php echo $sa['work_hours']; ?>h</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="small text-secondary">Chưa có lượt chấm công nào trong ngày này.</div>
            <?php endif; ?>
        </div>

    <?php elseif ($tab === 'branches' && $isManager): ?>
        <h5 class="fw-bold mb-1">Cửa hàng & Chi nhánh</h5>
        <p class="small text-secondary mb-3">Tổng hợp thông tin các chi nhánh / cửa hàng trong hệ thống</p>

        <div class="row g-3 mb-3">
            <?php foreach ($storesList as $st): ?>
                <div class="col-md-6">
                    <div class="box-dark h-100">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <div class="fw-bold text-white"><?php echo htmlspecialchars($st['store_name']); ?></div>
                            <span class="badge bg-info text-dark"><?php echo (int)$st['staff_count']; ?> nhân sự</span>
                        </div>
                        <div class="small text-secondary">
                            Mã CN: <span class="text-info"><?php echo htmlspecialchars($st['store_code'] ?? ('CN-' . $st['id'])); ?></span>
                            <?php if (!empty($st['address'])): ?>
                                · <?php echo htmlspecialchars($st['address']); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="box-dark">
            <div class="fw-bold mb-2">So sánh nhanh giữa các cửa hàng</div>
            <div class="table-responsive">
                <table class="table table-dark-custom table-sm align-middle mb-0 small">
                    <thead>
                        <tr>
                            <th>Cửa hàng / Chi nhánh</th>
                            <th>Địa chỉ</th>
                            <th>Số nhân sự</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($storesList as $st): ?>
                            <tr>
                                <td class="fw-semibold text-white"><?php echo htmlspecialchars($st['store_name']); ?></td>
                                <td class="text-secondary"><?php echo htmlspecialchars($st['address'] ?? 'Đang cập nhật'); ?></td>
                                <td class="text-info fw-semibold"><?php echo (int)$st['staff_count']; ?> người</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <?php elseif ($tab === 'store_schedule' && $isManager): ?>
        <h5 class="fw-bold mb-1">Lịch trình ca làm của cửa hàng và nhân viên</h5>
        <p class="small text-secondary mb-3">Xem nguyên lịch 1 tháng và theo dõi giờ công (h công) của nhân viên</p>

        <div class="box-dark mb-3">
            <form method="GET" class="row g-2 align-items-end">
                <input type="hidden" name="page" value="home">
                <input type="hidden" name="tab" value="store_schedule">
                <div class="col-sm-4">
                    <label class="form-label small text-secondary">Tháng xem lịch</label>
                    <input type="month" name="sched_month" class="dark-select w-100" value="<?php echo htmlspecialchars($selectedSchedMonth); ?>">
                </div>
                <div class="col-sm-4">
                    <label class="form-label small text-secondary">Cửa hàng / Chi nhánh</label>
                    <select name="filter_store" class="dark-select w-100">
                        <option value="0">Tất cả cửa hàng</option>
                        <?php foreach ($storesList as $st): ?>
                            <option value="<?php echo $st['id']; ?>" <?php if ($filterStoreId === (int)$st['id']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($st['store_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-sm-4">
                    <button type="submit" class="btn btn-primary w-100 fw-semibold rounded-3">Xem lịch tháng</button>
                </div>
            </form>
        </div>

        <div class="box-dark mb-3">
            <div class="fw-bold mb-2">Theo dõi giờ công nhân viên (Tháng <?php echo htmlspecialchars($selectedSchedMonth); ?>)</div>
            <?php if (count($monthStaffHours) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-dark-custom table-sm align-middle mb-0 small">
                        <thead>
                            <tr>
                                <th>Nhân viên</th>
                                <th>Vai trò</th>
                                <th>Chi nhánh</th>
                                <th>Số ca xếp</th>
                                <th>Giờ công (h công)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($monthStaffHours as $ms): ?>
                                <tr>
                                    <td class="fw-semibold text-white"><?php echo htmlspecialchars($ms['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($ms['role']); ?></td>
                                    <td class="text-secondary"><?php echo htmlspecialchars($ms['store_name'] ? $ms['store_name'] : 'Chưa gán'); ?></td>
                                    <td><?php echo (int)$ms['shift_count']; ?> ca</td>
                                    <td class="text-info fw-bold"><?php echo (float)$ms['month_hours']; ?> h công</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="small text-secondary">Chưa có dữ liệu ca làm hoặc giờ công trong tháng này.</div>
            <?php endif; ?>
        </div>

        <div class="box-dark">
            <div class="fw-bold mb-3">Nguyên lịch tháng <?php echo htmlspecialchars($selectedSchedMonth); ?></div>
            <?php
            $daysInMonth = (int)date("t", strtotime($selectedSchedMonth . "-01"));
            $hasAnyShiftInMonth = false;
            for ($dayNum = 1; $dayNum <= $daysInMonth; $dayNum++):
                $fullDate = $selectedSchedMonth . "-" . str_pad((string)$dayNum, 2, "0", STR_PAD_LEFT);
                $dayRows = isset($monthSchedulesByDate[$fullDate]) ? $monthSchedulesByDate[$fullDate] : [];
                if (count($dayRows) > 0) {
                    $hasAnyShiftInMonth = true;
                }
            ?>
                <div class="py-2 border-bottom border-secondary border-opacity-25 small">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-1">
                        <span class="fw-bold text-info">
                            Ngày <?php echo date("d/m/Y", strtotime($fullDate)); ?>
                        </span>
                        <span class="text-secondary"><?php echo count($dayRows); ?> ca</span>
                    </div>

                    <?php if (count($dayRows) > 0): ?>
                        <?php foreach ($dayRows as $dr): ?>
                            <div class="d-flex flex-wrap justify-content-between align-items-center ps-2 py-1">
                                <span>
                                    <strong class="text-white"><?php echo htmlspecialchars($dr['full_name']); ?></strong>
                                    <span class="text-secondary">
                                        (<?php echo htmlspecialchars($dr['role']); ?>
                                        <?php if (!empty($dr['store_name'])) echo " · " . htmlspecialchars($dr['store_name']); ?>)
                                    </span>
                                </span>
                                <span>
                                    <span class="text-info fw-semibold">
                                        <?php echo htmlspecialchars($dr['shift_code']); ?>
                                        (<?php echo substr($dr['start_time'], 0, 5); ?> - <?php echo substr($dr['end_time'], 0, 5); ?>)
                                    </span>
                                    <span class="badge bg-dark border border-info text-info ms-1">
                                        <?php echo (float)$dr['month_hours']; ?> h công
                                    </span>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-secondary ps-2">Chưa có ca phân công</div>
                    <?php endif; ?>
                </div>
            <?php endfor; ?>

            <?php if (!$hasAnyShiftInMonth): ?>
                <div class="small text-secondary mt-2">Tháng này chưa có lịch ca làm nào được xếp.</div>
            <?php endif; ?>
        </div>

    <?php elseif ($tab === 'staff_info' && ($isSup || $isManager)): ?>
        <h5 class="fw-bold mb-1">Quản lý nhân viên</h5>
        <p class="small text-secondary mb-3">Thêm nhân viên mới, tạo tài khoản Gmail đăng nhập, chỉnh sửa hoặc xóa nhân viên</p>

        <div class="box-dark mb-3">
            <div class="fw-bold mb-2">Thêm nhân viên & tạo tài khoản Gmail</div>
            <form method="POST" class="row g-2 align-items-end">
                <input type="hidden" name="action" value="add_staff">

                <div class="col-sm-4">
                    <label class="form-label small text-secondary">Họ và tên</label>
                    <input type="text" name="full_name" class="dark-select w-100"
                           placeholder="VD: Nguyễn Văn A" required>
                </div>

                <div class="col-sm-4">
                    <label class="form-label small text-secondary">Gmail đăng nhập</label>
                    <input type="email" name="email" class="dark-select w-100"
                           placeholder="VD: nhanvien@gmail.com" required>
                </div>

                <div class="col-sm-4">
                    <label class="form-label small text-secondary">Mật khẩu tài khoản</label>
                    <input type="text" name="password" class="dark-select w-100"
                           value="123456" required>
                </div>

                <div class="col-sm-3">
                    <label class="form-label small text-secondary">Vai trò</label>
                    <select name="role" class="dark-select w-100">
                        <?php foreach (['Học việc', 'Part-time', 'Nhân viên', 'Trưởng ca (TC)', 'Quản lý'] as $rOpt): ?>
                            <option value="<?php echo $rOpt; ?>" <?php if ($rOpt === 'Nhân viên') echo 'selected'; ?>>
                                <?php echo $rOpt; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-sm-3">
                    <label class="form-label small text-secondary">Chi nhánh</label>
                    <select name="store_id" class="dark-select w-100">
                        <option value="0">-- Chưa gán --</option>
                        <?php foreach ($storesList as $sOpt): ?>
                            <option value="<?php echo $sOpt['id']; ?>">
                                <?php echo htmlspecialchars($sOpt['store_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-sm-3">
                    <label class="form-label small text-secondary">Lương/giờ (đ)</label>
                    <input type="number" name="hourly_rate" class="dark-select w-100"
                           value="25000" step="1000" min="0" required>
                </div>

                <div class="col-sm-3">
                    <button type="submit" class="btn btn-primary w-100 fw-semibold rounded-3">
                        <i class="bi bi-person-plus me-1"></i> Tạo nhân viên & acc
                    </button>
                </div>
            </form>
        </div>

        <?php if ($isManager): ?>
            <div class="box-dark mb-3">
                <form method="GET" class="row g-2 align-items-end">
                    <input type="hidden" name="page" value="home">
                    <input type="hidden" name="tab" value="staff_info">
                    <div class="col-sm-6">
                        <label class="form-label small text-secondary">Lọc theo chi nhánh</label>
                        <select name="filter_store" class="dark-select w-100">
                            <option value="0">Tất cả chi nhánh</option>
                            <?php foreach ($storesList as $st): ?>
                                <option value="<?php echo $st['id']; ?>" <?php if ($filterStoreId === (int)$st['id']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($st['store_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-sm-3">
                        <button type="submit" class="btn btn-outline-info w-100">Lọc</button>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <div class="box-dark">
            <div class="fw-bold mb-2">Danh sách, chỉnh sửa & xóa nhân viên (<?php echo count($staffList); ?>)</div>
            <div class="table-responsive">
                <table class="table table-dark-custom table-sm align-middle mb-0 small">
                    <thead>
                        <tr>
                            <th>Họ tên</th>
                            <th>Gmail</th>
                            <th>Vai trò</th>
                            <th>Chi nhánh</th>
                            <th>Lương/giờ</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($staffList as $st): ?>
                            <?php $formId = "upd_staff_" . (int)$st['id']; ?>
                            <tr>
                                <td style="min-width: 145px;">
                                    <form method="POST" id="<?php echo $formId; ?>">
                                        <input type="hidden" name="action" value="update_staff">
                                        <input type="hidden" name="target_user_id" value="<?php echo $st['id']; ?>">
                                        <input type="hidden" name="back_tab" value="staff_info">
                                    </form>
                                    <input type="text" name="full_name" form="<?php echo $formId; ?>" class="dark-select w-100 py-1"
                                           value="<?php echo htmlspecialchars($st['full_name']); ?>" required>
                                </td>
                                <td class="text-info"><?php echo htmlspecialchars($st['email']); ?></td>
                                <td style="min-width: 140px;">
                                    <select name="role" form="<?php echo $formId; ?>" class="dark-select w-100 py-1">
                                        <?php foreach (['Học việc', 'Part-time', 'Nhân viên', 'Trưởng ca (TC)', 'Quản lý'] as $rOpt): ?>
                                            <option value="<?php echo $rOpt; ?>" <?php if ($st['role'] === $rOpt) echo 'selected'; ?>>
                                                <?php echo $rOpt; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td style="min-width: 150px;">
                                    <select name="store_id" form="<?php echo $formId; ?>" class="dark-select w-100 py-1">
                                        <option value="0">-- Chưa gán --</option>
                                        <?php foreach ($storesList as $sOpt): ?>
                                            <option value="<?php echo $sOpt['id']; ?>" <?php if ((int)$st['store_id'] === (int)$sOpt['id']) echo 'selected'; ?>>
                                                <?php echo htmlspecialchars($sOpt['store_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td style="min-width: 110px;">
                                    <input type="number" name="hourly_rate" form="<?php echo $formId; ?>" class="dark-select w-100 py-1"
                                           value="<?php echo (int)$st['hourly_rate']; ?>" step="1000">
                                </td>
                                <td style="min-width: 145px;">
                                    <select name="status" form="<?php echo $formId; ?>" class="dark-select w-100 py-1">
                                        <option value="active" <?php if ($st['status'] === 'active') echo 'selected'; ?>>Đang làm việc</option>
                                        <option value="inactive" <?php if ($st['status'] === 'inactive') echo 'selected'; ?>>Ngừng hoạt động</option>
                                    </select>
                                </td>
                                <td style="white-space: nowrap;">
                                    <button type="submit" form="<?php echo $formId; ?>" class="btn btn-sm btn-primary py-1 px-2">Lưu</button>
                                    <?php if ((int)$st['id'] !== $userId): ?>
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Xóa vĩnh viễn nhân viên này khỏi hệ thống?');">
                                            <input type="hidden" name="action" value="delete_staff">
                                            <input type="hidden" name="target_user_id" value="<?php echo $st['id']; ?>">
                                            <input type="hidden" name="back_tab" value="staff_info">
                                            <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2">Xóa</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <?php elseif ($tab === 'leaves' && ($isSup || $isManager)): ?>
        <h5 class="fw-bold mb-1">Duyệt đơn nghỉ phép</h5>
        <p class="small text-secondary mb-3">Xem và xử lý các đơn nghỉ phép của nhân viên</p>

        <div class="box-dark">
            <?php if (count($leaveList) > 0): ?>
                <?php foreach ($leaveList as $lv): ?>
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 py-2 border-bottom border-secondary border-opacity-25 small">
                        <div>
                            <strong class="text-white"><?php echo htmlspecialchars($lv['full_name']); ?></strong>
                            · Ngày nghỉ: <span class="text-info"><?php echo date('d/m/Y', strtotime($lv['leave_date'])); ?></span>
                            <div class="text-secondary">Lý do: <?php echo htmlspecialchars($lv['reason']); ?></div>
                        </div>

                        <div class="d-flex align-items-center gap-2">
                            <?php if ($lv['status'] === 'approved'): ?>
                                <span class="badge bg-success">Đã duyệt</span>
                            <?php elseif ($lv['status'] === 'rejected'): ?>
                                <span class="badge bg-danger">Từ chối</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark">Chờ duyệt</span>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="action" value="review_leave">
                                    <input type="hidden" name="leave_id" value="<?php echo $lv['id']; ?>">
                                    <input type="hidden" name="new_status" value="approved">
                                    <input type="hidden" name="back_tab" value="leaves">
                                    <button type="submit" class="btn btn-sm btn-success py-0 px-2">Duyệt</button>
                                </form>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="action" value="review_leave">
                                    <input type="hidden" name="leave_id" value="<?php echo $lv['id']; ?>">
                                    <input type="hidden" name="new_status" value="rejected">
                                    <input type="hidden" name="back_tab" value="leaves">
                                    <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2">Từ chối</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="small text-secondary">Chưa có đơn nghỉ phép nào.</div>
            <?php endif; ?>
        </div>

    <?php else: ?>
        <div class="box-dark text-warning small">Không có quyền xem mục này.</div>
    <?php endif; ?>
</main>

<script>
document.querySelectorAll('.avail-row').forEach(function(row) {
    var chips = row.querySelectorAll('.slot-chip');
    var hidden = row.querySelector('.slot-hidden');
    var sSel = row.querySelector('.start-sel');
    var eSel = row.querySelector('.end-sel');

    function syncRowFromActive() {
        var activeSlots = [];
        var minStart = null;
        var maxEnd = null;
        var isOff = false;

        chips.forEach(function(c) {
            if (c.classList.contains('active')) {
                var sl = c.getAttribute('data-slot');
                if (sl === 'off') {
                    isOff = true;
                } else {
                    activeSlots.push(sl);
                    var sVal = parseInt(c.getAttribute('data-s'), 10);
                    var eVal = parseInt(c.getAttribute('data-e'), 10);
                    if (!isNaN(sVal) && (minStart === null || sVal < minStart)) {
                        minStart = sVal;
                    }
                    if (!isNaN(eVal) && (maxEnd === null || eVal > maxEnd)) {
                        maxEnd = eVal;
                    }
                }
            }
        });

        if (isOff) {
            hidden.value = 'off';
            sSel.value = '';
            eSel.value = '';
        } else {
            hidden.value = activeSlots.join(',');
            sSel.value = minStart !== null ? String(minStart) : '';
            eSel.value = maxEnd !== null ? String(maxEnd) : '';
        }
    }

    chips.forEach(function(btn) {
        btn.addEventListener('click', function() {
            var sl = btn.getAttribute('data-slot');

            if (sl === 'off') {
                var wasActive = btn.classList.contains('active');
                chips.forEach(function(c) { c.classList.remove('active'); });
                if (!wasActive) {
                    btn.classList.add('active');
                }
            } else {
                chips.forEach(function(c) {
                    if (c.getAttribute('data-slot') === 'off') {
                        c.classList.remove('active');
                    }
                });
                btn.classList.toggle('active');
            }

            syncRowFromActive();
        });
    });
});
</script>
</body>
</html>