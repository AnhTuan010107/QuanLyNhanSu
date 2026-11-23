<?php
function formatVietnameseDayHeader($ts) {
    $map = [
        'Sunday'    => 'Chủ Nhật',
        'Monday'    => 'Thứ Hai',
        'Tuesday'   => 'Thứ Ba',
        'Wednesday' => 'Thứ Tư',
        'Thursday'  => 'Thứ Năm',
        'Friday'    => 'Thứ Sáu',
        'Saturday'  => 'Thứ Bảy',
    ];
    return ($map[date('l', $ts)] ?? '') . ' ' . date('d/m', $ts);
}

$hoursList = [];
for ($h = 7; $h <= 23; $h++) {
    $hoursList[] = sprintf('%02d:00', $h);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch làm - Đăng ký giờ rảnh</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #fdfbf7;
            color: #292524;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        .page-wrap {
            max-width: 960px;
            margin: 0 auto;
            padding: 16px 14px 48px;
        }
        .top-back-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
            padding-bottom: 12px;
            margin-bottom: 14px;
            border-bottom: 1px solid #efe7da;
        }
        .btn-back-home {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            background-color: #f3eadc;
            color: #5c3a21;
            font-size: 13.5px;
            font-weight: 600;
            padding: 6px 14px;
            border-radius: 999px;
        }
        .btn-back-home:hover {
            background-color: #e7dac5;
            color: #3f2614;
        }
        .schedule-heading h2 {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 2px;
            color: #1c1917;
        }
        .schedule-heading p {
            font-size: 13.5px;
            color: #78716c;
            margin-bottom: 14px;
        }
        .today-card {
            background-color: #faf6ef;
            border: 1px solid #efe6d8;
            border-radius: 14px;
            padding: 14px 16px;
            height: 100%;
        }
        .tomorrow-card {
            background-color: #e4f1e9;
            border: 1px solid #cce3d5;
            border-radius: 14px;
            padding: 14px 16px;
            height: 100%;
        }
        .day-card-label {
            font-size: 12.5px;
            color: #6b7280;
            margin-bottom: 4px;
        }
        .tomorrow-card .day-card-label {
            color: #2f684c;
        }
        .day-card-time {
            font-size: 18px;
            font-weight: 700;
            color: #1c1917;
        }
        .tomorrow-card .day-card-time {
            color: #14532d;
        }
        .day-card-coworkers {
            font-size: 12px;
            color: #3f6e56;
            margin-top: 2px;
        }
        .week-switcher {
            background-color: #f5ede0;
            border-radius: 14px;
            padding: 5px;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 6px;
            margin: 14px 0 12px;
        }
        .week-tab-btn {
            text-align: center;
            text-decoration: none;
            padding: 8px 6px;
            border-radius: 10px;
            color: #6c6257;
            transition: all 0.15s ease;
        }
        .week-tab-btn:hover {
            color: #292524;
        }
        .week-tab-btn.active {
            background-color: #ffffff;
            color: #1c1917;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
        }
        .week-tab-title {
            font-size: 13.5px;
            font-weight: 700;
            line-height: 1.2;
        }
        .week-tab-range {
            font-size: 11.5px;
            color: #8c8277;
            margin-top: 2px;
        }
        .deadline-bar {
            background-color: #fdf6e7;
            border: 1px solid #f4e6c8;
            border-radius: 10px;
            padding: 9px 14px;
            font-size: 13px;
            color: #7c5821;
            margin-bottom: 14px;
        }
        .avail-box {
            background-color: #ffffff;
            border: 1px solid #efe7da;
            border-radius: 16px;
            padding: 18px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
        }
        .avail-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }
        .avail-header h5 {
            font-size: 16px;
            font-weight: 700;
            margin: 0;
        }
        .avail-counter {
            font-size: 13px;
            font-weight: 700;
            color: #168053;
        }
        .quick-copy-row {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 10px;
        }
        .btn-copy-pill {
            background-color: #f5ede0;
            border: 1px solid #eadbc5;
            color: #44382c;
            font-size: 12.5px;
            font-weight: 600;
            padding: 5px 12px;
            border-radius: 9px;
            cursor: pointer;
        }
        .btn-copy-pill:hover {
            background-color: #ecdfc9;
        }
        .legend-row {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 14px;
        }
        .legend-pill {
            font-size: 11.5px;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 7px;
        }
        .legend-morning { background-color: #f8e5b4; color: #785008; }
        .legend-afternoon { background-color: #dbeafe; color: #1e40af; }
        .legend-evening { background-color: #e6d5c5; color: #5c3a21; }

        .day-row {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            padding: 12px 0;
            border-top: 1px solid #f2ece1;
        }
        .day-label-col {
            width: 44px;
            flex-shrink: 0;
            padding-top: 4px;
        }
        .day-code {
            font-size: 13.5px;
            font-weight: 700;
            color: #d9383a;
            line-height: 1.2;
        }
        .day-date {
            font-size: 11.5px;
            color: #8c8277;
        }
        .day-controls-col {
            flex: 1;
            min-width: 0;
        }
        .time-select-line {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
        }
        .time-select {
            width: 82px;
            padding: 5px 8px;
            font-size: 13.5px;
            border: 1px solid #ded6c8;
            border-radius: 9px;
            background-color: #fff;
            color: #292524;
        }
        .btn-add-slot2 {
            font-size: 11.5px;
            color: #8c5327;
            background: #faf5ec;
            border: 1px dashed #d6c3a5;
            border-radius: 8px;
            padding: 4px 9px;
            cursor: pointer;
        }
        .btn-add-slot2:hover {
            background: #f3e8d6;
        }
        .shift-pills-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
        }
        .shift-quick-btn {
            background-color: #ffffff;
            border: 1px solid #e5ddd0;
            border-radius: 999px;
            padding: 5px 8px;
            font-size: 12.5px;
            font-weight: 600;
            color: #44403c;
            cursor: pointer;
            transition: all 0.15s ease;
            text-align: center;
        }
        .shift-quick-btn:hover {
            background-color: #faf6ef;
        }
        .shift-quick-btn.active-morning {
            background-color: #f8e5b4;
            border-color: #e6c675;
            color: #6b4400;
        }
        .shift-quick-btn.active-afternoon {
            background-color: #dbeafe;
            border-color: #93c5fd;
            color: #1e3a8a;
        }
        .shift-quick-btn.active-evening {
            background-color: #e6d5c5;
            border-color: #c7ad94;
            color: #4a2c16;
        }
        .shift-quick-btn.active-off {
            background-color: #f3efe8;
            border-color: #d6cec0;
            color: #57534e;
        }
        .helper-caption {
            font-size: 12px;
            color: #78716c;
            margin-top: 12px;
            line-height: 1.5;
        }
        .note-toggle-btn {
            background: none;
            border: none;
            padding: 0;
            margin-top: 10px;
            font-size: 13px;
            font-weight: 700;
            color: #292524;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .btn-submit-avail {
            width: 100%;
            margin-top: 16px;
            padding: 12px 18px;
            border: none;
            border-radius: 12px;
            font-size: 14.5px;
            font-weight: 700;
            color: #ffffff;
            background: linear-gradient(90deg, #8c5327 0%, #c48858 100%);
            box-shadow: 0 4px 12px rgba(140, 83, 39, 0.22);
            cursor: pointer;
            transition: opacity 0.15s;
        }
        .btn-submit-avail:hover {
            opacity: 0.93;
        }
        @media (max-width: 576px) {
            .shift-pills-grid {
                gap: 5px;
            }
            .shift-quick-btn {
                font-size: 12px;
                padding: 5px 4px;
            }
        }
    </style>
</head>
<body>

<div class="page-wrap">

    <!-- Thanh điều hướng quay lại Trang chủ Cổng Nhân Viên -->
    <div class="top-back-bar">
        <a href="<?= BASE_URL ?>/client/home" class="btn-back-home">
            <i class="bi bi-arrow-left"></i> Quay lại Cổng Nhân Viên
        </a>

        <div class="d-flex align-items-center gap-2 small">
            <span class="text-muted d-none d-sm-inline">
                <strong><?= htmlspecialchars($profile['full_name']) ?></strong> · <?= htmlspecialchars($profile['role']) ?>
            </span>
            <a href="<?= BASE_URL ?>/logout" class="btn btn-sm btn-outline-secondary rounded-pill px-3">Đăng xuất</a>
        </div>
    </div>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-3 py-2 small">
            <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show rounded-3 py-2 small">
            <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="schedule-heading">
        <h2>Lịch làm</h2>
        <p>Ca của bạn · đăng ký giờ rảnh tuần sau</p>
    </div>

    <!-- 2 thẻ Hôm nay & Ngày mai -->
    <div class="row g-2">
        <div class="col-12 col-md-6">
            <div class="today-card">
                <div class="day-card-label">Hôm nay · <?= formatVietnameseDayHeader(time()) ?></div>
                <?php if ($todayInfo): ?>
                    <div class="day-card-time"><?= htmlspecialchars($todayInfo['time_text']) ?></div>
                    <?php if (!empty($todayInfo['coworkers'])): ?>
                        <div class="day-card-coworkers">Cùng ca: <?= htmlspecialchars(implode(', ', $todayInfo['coworkers'])) ?></div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="day-card-time">Nghỉ</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="tomorrow-card">
                <div class="day-card-label">Ngày mai · <?= formatVietnameseDayHeader(strtotime('+1 day')) ?></div>
                <?php if ($tomorrowInfo): ?>
                    <div class="day-card-time"><?= htmlspecialchars($tomorrowInfo['time_text']) ?></div>
                    <?php if (!empty($tomorrowInfo['coworkers'])): ?>
                        <div class="day-card-coworkers">Cùng ca: <?= htmlspecialchars(implode(', ', $tomorrowInfo['coworkers'])) ?></div>
                    <?php else: ?>
                        <div class="day-card-coworkers"><?= $tomorrowInfo['status'] === 'approved' ? 'Đã xếp ca' : 'Đang chờ duyệt' ?></div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="day-card-time">Nghỉ</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Thanh chuyển 3 tuần -->
    <div class="week-switcher">
        <?php foreach ($weeks as $w): ?>
            <a href="<?= BASE_URL ?>/client/schedules?week=<?= $w['offset'] ?>"
               class="week-tab-btn <?= $w['offset'] === $weekOffset ? 'active' : '' ?>">
                <div class="week-tab-title"><?= $w['title'] ?></div>
                <div class="week-tab-range"><?= $w['range_text'] ?></div>
            </a>
        <?php endforeach; ?>
    </div>

    <?php if ($weekOffset === 0): ?>
        <!-- CHẾ ĐỘ XEM LỊCH TUẦN NÀY -->
        <div class="avail-box">
            <div class="avail-header">
                <h5>Lịch làm việc tuần này (<?= $selectedWeek['range_text'] ?>)</h5>
                <span class="avail-counter"><?= htmlspecialchars($profile['store_name']) ?></span>
            </div>
            <?php foreach ($weekDays as $wd): ?>
                <div class="day-row align-items-center">
                    <div class="day-label-col">
                        <div class="day-code"><?= $wd['code'] ?></div>
                        <div class="day-date"><?= $wd['date_short'] ?></div>
                    </div>
                    <div class="day-controls-col d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <?php if ($wd['summary']): ?>
                            <div>
                                <span class="fw-bold"><?= htmlspecialchars($wd['summary']['time_text']) ?></span>
                                <?php if (!empty($wd['summary']['coworkers'])): ?>
                                    <span class="text-muted small ms-2">Cùng ca: <?= htmlspecialchars(implode(', ', $wd['summary']['coworkers'])) ?></span>
                                <?php endif; ?>
                            </div>
                            <?php if ($wd['summary']['status'] === 'approved'): ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3">Đã duyệt</span>
                            <?php else: ?>
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-3">Chờ duyệt</span>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="text-muted">Nghỉ</span>
                            <span class="badge bg-light text-secondary border rounded-pill px-3">Nghỉ</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    <?php else: ?>
        <!-- CHẾ ĐỘ ĐĂNG KÝ GIỜ RẢNH TUẦN SAU / TUẦN SAU NỮA -->
        <div class="deadline-bar">
            Hạn đăng ký: <strong><?= $deadlineText ?></strong>
        </div>

        <form action="<?= BASE_URL ?>/client/schedules/save-availability" method="POST" id="availabilityForm">
            <input type="hidden" name="week_offset" value="<?= $weekOffset ?>">
            <input type="hidden" name="week_start" value="<?= $selectedWeek['start_date'] ?>">
            <input type="hidden" name="week_end" value="<?= $selectedWeek['end_date'] ?>">

            <div class="avail-box">
                <div class="avail-header">
                    <h5>Giờ rảnh của bạn</h5>
                    <span class="avail-counter" id="availSummaryText">0 giờ rảnh · 0 ngày</span>
                </div>

                <div class="quick-copy-row">
                    <button type="button" class="btn-copy-pill" id="btnCopyPrevWeek">
                        ↺ Giống tuần <?= $prevWeekLabel ?>
                    </button>
                    <button type="button" class="btn-copy-pill" id="btnCopyMonday">
                        Cả tuần như T2
                    </button>
                </div>

                <div class="legend-row">
                    <span class="legend-pill legend-morning">Sáng 8–13h</span>
                    <span class="legend-pill legend-afternoon">Chiều 13–18h</span>
                    <span class="legend-pill legend-evening">Tối 18–23h</span>
                </div>

                <?php foreach ($weekDays as $idx => $wd):
                    $s1 = $wd['slots'][0]['start'] ?? '';
                    $e1 = $wd['slots'][0]['end'] ?? '';
                    $s2 = $wd['slots'][1]['start'] ?? '';
                    $e2 = $wd['slots'][1]['end'] ?? '';
                    $hasSlot2 = (!empty($s2) || !empty($e2));
                ?>
                <div class="day-row" data-day-index="<?= $idx ?>">
                    <div class="day-label-col">
                        <div class="day-code"><?= $wd['code'] ?></div>
                        <div class="day-date"><?= $wd['date_short'] ?></div>
                    </div>

                    <div class="day-controls-col">
                        <div class="time-select-line">
                            <select name="days[<?= $wd['date_full'] ?>][start1]" class="time-select start1">
                                <option value="">-</option>
                                <?php foreach ($hoursList as $h): ?>
                                    <option value="<?= $h ?>" <?= $s1 === $h ? 'selected' : '' ?>><?= (int)substr($h, 0, 2) ?>h</option>
                                <?php endforeach; ?>
                            </select>
                            <span>-</span>
                            <select name="days[<?= $wd['date_full'] ?>][end1]" class="time-select end1">
                                <option value="">-</option>
                                <?php foreach ($hoursList as $h): ?>
                                    <option value="<?= $h ?>" <?= $e1 === $h ? 'selected' : '' ?>><?= (int)substr($h, 0, 2) ?>h</option>
                                <?php endforeach; ?>
                            </select>

                            <button type="button" class="btn-add-slot2" onclick="toggleSlot2(this)">
                                <?= $hasSlot2 ? '− Ẩn khoảng 2' : '+ Khoảng 2' ?>
                            </button>
                        </div>

                        <div class="time-select-line slot2-line <?= $hasSlot2 ? '' : 'd-none' ?>">
                            <select name="days[<?= $wd['date_full'] ?>][start2]" class="time-select start2">
                                <option value="">-</option>
                                <?php foreach ($hoursList as $h): ?>
                                    <option value="<?= $h ?>" <?= $s2 === $h ? 'selected' : '' ?>><?= (int)substr($h, 0, 2) ?>h</option>
                                <?php endforeach; ?>
                            </select>
                            <span>-</span>
                            <select name="days[<?= $wd['date_full'] ?>][end2]" class="time-select end2">
                                <option value="">-</option>
                                <?php foreach ($hoursList as $h): ?>
                                    <option value="<?= $h ?>" <?= $e2 === $h ? 'selected' : '' ?>><?= (int)substr($h, 0, 2) ?>h</option>
                                <?php endforeach; ?>
                            </select>
                            <span class="small text-muted">(Khoảng 2)</span>
                        </div>

                        <div class="shift-pills-grid">
                            <button type="button" class="shift-quick-btn btn-morning" onclick="toggleShiftPreset(this, 'morning')">Sáng</button>
                            <button type="button" class="shift-quick-btn btn-afternoon" onclick="toggleShiftPreset(this, 'afternoon')">Chiều</button>
                            <button type="button" class="shift-quick-btn btn-evening" onclick="toggleShiftPreset(this, 'evening')">Tối</button>
                            <button type="button" class="shift-quick-btn btn-off" onclick="setDayOff(this)">Nghỉ</button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>

                <div class="helper-caption">
                    Chọn giờ Từ – Đến từng ngày (thêm được khoảng thứ 2). Nút Sáng / Chiều / Tối để chọn nhanh, bấm 2 nút = gộp giờ. Part-time tối thiểu 4 ngày (mỗi ngày từ 4 tiếng). Fulltime 6 ngày. Đăng ký rộng thì dễ được xếp ca hơn.
                </div>

                <div>
                    <button type="button" class="note-toggle-btn" id="toggleNoteBtn">
                        <span id="noteArrow"><?= !empty($weekData['note']) ? '▾' : '▸' ?></span> Ghi chú cho Trưởng ca
                    </button>
                    <div id="noteInputWrap" class="<?= !empty($weekData['note']) ? 'mt-2' : 'd-none mt-2' ?>">
                        <textarea name="general_note" class="form-control form-control-sm" rows="2"
                                  placeholder="Nhập ghi chú gửi Trưởng ca (VD: Xin ưu tiên ca sáng T3, T5)..."><?= htmlspecialchars($weekData['note']) ?></textarea>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-submit-avail">Gửi giờ rảnh</button>
        </form>
    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const prevWeekSlots = <?= json_encode($prevWeekSlotsByIndex) ?>;

    function timeToHour(str) {
        if (!str) return null;
        return parseInt(str.split(':')[0], 10);
    }

    function hourToTime(h) {
        return (h < 10 ? '0' + h : '' + h) + ':00';
    }

    function getCoveredPresets(row) {
        const ranges = [];
        const s1 = timeToHour(row.querySelector('.start1').value);
        const e1 = timeToHour(row.querySelector('.end1').value);
        if (s1 !== null && e1 !== null && e1 > s1) ranges.push([s1, e1]);

        const s2 = timeToHour(row.querySelector('.start2').value);
        const e2 = timeToHour(row.querySelector('.end2').value);
        if (s2 !== null && e2 !== null && e2 > s2) ranges.push([s2, e2]);

        const covers = (a, b) => ranges.some(([s, e]) => s <= a && e >= b);
        return {
            morning: covers(8, 13),
            afternoon: covers(13, 18),
            evening: covers(18, 23),
            hasAny: ranges.length > 0
        };
    }

    function toggleShiftPreset(btn, preset) {
        const row = btn.closest('.day-row');
        const state = getCoveredPresets(row);
        state[preset] = !state[preset];

        const active = [];
        if (state.morning) active.push([8, 13]);
        if (state.afternoon) active.push([13, 18]);
        if (state.evening) active.push([18, 23]);

        const merged = [];
        for (const r of active) {
            if (merged.length > 0 && merged[merged.length - 1][1] === r[0]) {
                merged[merged.length - 1][1] = r[1];
            } else {
                merged.push([...r]);
            }
        }

        const start1 = row.querySelector('.start1');
        const end1   = row.querySelector('.end1');
        const start2 = row.querySelector('.start2');
        const end2   = row.querySelector('.end2');
        const slot2Line = row.querySelector('.slot2-line');
        const addBtn = row.querySelector('.btn-add-slot2');

        start1.value = merged[0] ? hourToTime(merged[0][0]) : '';
        end1.value   = merged[0] ? hourToTime(merged[0][1]) : '';

        if (merged[1]) {
            start2.value = hourToTime(merged[1][0]);
            end2.value   = hourToTime(merged[1][1]);
            slot2Line.classList.remove('d-none');
            addBtn.textContent = '− Ẩn khoảng 2';
        } else {
            start2.value = '';
            end2.value   = '';
            slot2Line.classList.add('d-none');
            addBtn.textContent = '+ Khoảng 2';
        }

        syncRowVisuals(row);
        recalculateSummary();
    }

    function setDayOff(btn) {
        const row = btn.closest('.day-row');
        row.querySelector('.start1').value = '';
        row.querySelector('.end1').value   = '';
        row.querySelector('.start2').value = '';
        row.querySelector('.end2').value   = '';
        row.querySelector('.slot2-line').classList.add('d-none');
        row.querySelector('.btn-add-slot2').textContent = '+ Khoảng 2';

        syncRowVisuals(row);
        recalculateSummary();
    }

    function toggleSlot2(btn) {
        const row = btn.closest('.day-row');
        const line = row.querySelector('.slot2-line');
        if (line.classList.contains('d-none')) {
            line.classList.remove('d-none');
            btn.textContent = '− Ẩn khoảng 2';
        } else {
            row.querySelector('.start2').value = '';
            row.querySelector('.end2').value   = '';
            line.classList.add('d-none');
            btn.textContent = '+ Khoảng 2';
            syncRowVisuals(row);
            recalculateSummary();
        }
    }

    function syncRowVisuals(row) {
        const state = getCoveredPresets(row);
        row.querySelector('.btn-morning').classList.toggle('active-morning', state.morning);
        row.querySelector('.btn-afternoon').classList.toggle('active-afternoon', state.afternoon);
        row.querySelector('.btn-evening').classList.toggle('active-evening', state.evening);
        row.querySelector('.btn-off').classList.toggle('active-off', !state.hasAny);
    }

    function recalculateSummary() {
        let totalHours = 0;
        let totalDays = 0;

        document.querySelectorAll('.day-row[data-day-index]').forEach(row => {
            let dayHours = 0;
            const s1 = timeToHour(row.querySelector('.start1').value);
            const e1 = timeToHour(row.querySelector('.end1').value);
            if (s1 !== null && e1 !== null && e1 > s1) dayHours += (e1 - s1);

            const s2 = timeToHour(row.querySelector('.start2').value);
            const e2 = timeToHour(row.querySelector('.end2').value);
            if (s2 !== null && e2 !== null && e2 > s2) dayHours += (e2 - s2);

            if (dayHours > 0) {
                totalHours += dayHours;
                totalDays++;
            }
        });

        const summaryEl = document.getElementById('availSummaryText');
        if (summaryEl) {
            summaryEl.textContent = `${totalHours} giờ rảnh · ${totalDays} ngày`;
        }
    }

    document.querySelectorAll('.day-row[data-day-index] select').forEach(sel => {
        sel.addEventListener('change', function () {
            const row = this.closest('.day-row');
            syncRowVisuals(row);
            recalculateSummary();
        });
    });

    const btnCopyMonday = document.getElementById('btnCopyMonday');
    if (btnCopyMonday) {
        btnCopyMonday.addEventListener('click', function () {
            const rows = document.querySelectorAll('.day-row[data-day-index]');
            if (rows.length < 7) return;

            const s1 = rows[0].querySelector('.start1').value;
            const e1 = rows[0].querySelector('.end1').value;
            const s2 = rows[0].querySelector('.start2').value;
            const e2 = rows[0].querySelector('.end2').value;
            const show2 = !rows[0].querySelector('.slot2-line').classList.contains('d-none');

            for (let i = 1; i < rows.length; i++) {
                rows[i].querySelector('.start1').value = s1;
                rows[i].querySelector('.end1').value   = e1;
                rows[i].querySelector('.start2').value = s2;
                rows[i].querySelector('.end2').value   = e2;
                rows[i].querySelector('.slot2-line').classList.toggle('d-none', !show2);
                rows[i].querySelector('.btn-add-slot2').textContent = show2 ? '− Ẩn khoảng 2' : '+ Khoảng 2';
                syncRowVisuals(rows[i]);
            }
            recalculateSummary();
        });
    }

    const btnCopyPrevWeek = document.getElementById('btnCopyPrevWeek');
    if (btnCopyPrevWeek) {
        btnCopyPrevWeek.addEventListener('click', function () {
            const rows = document.querySelectorAll('.day-row[data-day-index]');
            rows.forEach((row, idx) => {
                const slots = prevWeekSlots[idx] || [];
                row.querySelector('.start1').value = slots[0] ? slots[0].start : '';
                row.querySelector('.end1').value   = slots[0] ? slots[0].end : '';
                if (slots[1]) {
                    row.querySelector('.start2').value = slots[1].start;
                    row.querySelector('.end2').value   = slots[1].end;
                    row.querySelector('.slot2-line').classList.remove('d-none');
                    row.querySelector('.btn-add-slot2').textContent = '− Ẩn khoảng 2';
                } else {
                    row.querySelector('.start2').value = '';
                    row.querySelector('.end2').value   = '';
                    row.querySelector('.slot2-line').classList.add('d-none');
                    row.querySelector('.btn-add-slot2').textContent = '+ Khoảng 2';
                }
                syncRowVisuals(row);
            });
            recalculateSummary();
        });
    }

    const toggleNoteBtn = document.getElementById('toggleNoteBtn');
    if (toggleNoteBtn) {
        toggleNoteBtn.addEventListener('click', function () {
            const wrap = document.getElementById('noteInputWrap');
            const arrow = document.getElementById('noteArrow');
            const isHidden = wrap.classList.toggle('d-none');
            arrow.textContent = isHidden ? '▸' : '▾';
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.day-row[data-day-index]').forEach(syncRowVisuals);
        recalculateSummary();
    });
</script>
</body>
</html>