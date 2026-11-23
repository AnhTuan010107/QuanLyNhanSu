<?php
$pageTitle = 'Quản lý Hồ sơ Nhân viên';
$activePage = 'users';
require_once __DIR__ . '/../layouts/admin_header.php';
require_once __DIR__ . '/../layouts/admin_sidebar.php';

$isManager = ($_SESSION['user']['role'] === 'Quản lý');
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-0">Quản lý Hồ sơ Nhân viên</h4>
        <small class="text-muted">
            <?= $isManager ? 'Toàn bộ nhân sự trên hệ thống cửa hàng' : 'Nhân sự thuộc: ' . htmlspecialchars($_SESSION['user']['store_name']) ?>
        </small>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
        <i class="bi bi-person-plus-fill me-1"></i> Thêm nhân viên
    </button>
</div>

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

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <div class="row g-2">
            <div class="col-md-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                    <input type="text" id="searchUser" class="form-control" placeholder="Tìm theo mã NV, họ tên, email, SĐT...">
                </div>
            </div>
            <?php if ($isManager): ?>
            <div class="col-md-4">
                <select id="filterStore" class="form-select form-select-sm">
                    <option value="">-- Tất cả cửa hàng --</option>
                    <?php foreach ($stores as $st): ?>
                        <option value="<?= htmlspecialchars($st['store_name']) ?>"><?= htmlspecialchars($st['store_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="userTable">
            <thead class="table-light">
                <tr>
                    <th width="55">STT</th>
                    <th>Mã NV</th>
                    <th>Họ và tên</th>
                    <th>Liên hệ</th>
                    <th>Cửa hàng</th>
                    <th class="text-center">Vai trò</th>
                    <th class="text-end">Lương/giờ</th>
                    <th class="text-center">Trạng thái</th>
                    <th class="text-end" width="120">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">Chưa có hồ sơ nhân viên nào.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $index => $u): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><span class="fw-bold text-primary"><?= htmlspecialchars($u['employee_code']) ?></span></td>
                            <td class="fw-semibold"><?= htmlspecialchars($u['full_name']) ?></td>
                            <td>
                                <div class="small"><?= htmlspecialchars($u['email']) ?></div>
                                <div class="text-muted small"><?= htmlspecialchars($u['phone']) ?></div>
                            </td>
                            <td><?= htmlspecialchars($u['store_name']) ?></td>
                            <td class="text-center">
                                <?php if ($u['role'] === 'Quản lý'): ?>
                                    <span class="badge bg-danger">Quản lý</span>
                                <?php elseif ($u['role'] === 'Trưởng ca (TC)'): ?>
                                    <span class="badge bg-warning text-dark">Trưởng ca (TC)</span>
                                <?php elseif ($u['role'] === 'Nhân viên PartTime'): ?>
                                    <span class="badge bg-primary">Nhân viên PartTime</span>
                                <?php else: ?>
                                    <span class="badge bg-info text-dark">Học Việc</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end fw-semibold"><?= number_format($u['hourly_rate'], 0, ',', '.') ?> đ</td>
                            <td class="text-center">
                                <?php if ((int)$u['status'] === 1): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">Đang làm</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Nghỉ việc</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <button type="button"
                                        class="btn btn-sm btn-outline-warning btn-edit-user"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editUserModal"
                                        data-id="<?= $u['id'] ?>"
                                        data-code="<?= htmlspecialchars($u['employee_code']) ?>"
                                        data-name="<?= htmlspecialchars($u['full_name']) ?>"
                                        data-email="<?= htmlspecialchars($u['email']) ?>"
                                        data-phone="<?= htmlspecialchars($u['phone']) ?>"
                                        data-store="<?= $u['store_id'] ?>"
                                        data-role="<?= htmlspecialchars($u['role']) ?>"
                                        data-rate="<?= (int)$u['hourly_rate'] ?>"
                                        data-status="<?= $u['status'] ?>"
                                        title="Chỉnh sửa">
                                    <i class="bi bi-pencil-square"></i>
                                </button>

                                <?php if ((int)$u['id'] !== (int)$_SESSION['user']['id']): ?>
                                <form action="<?= BASE_URL ?>/admin/users/delete" method="POST" class="d-inline"
                                      onsubmit="return confirm('Xóa hồ sơ nhân viên <?= htmlspecialchars($u['full_name']) ?>?');">
                                    <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Xóa">
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

<!-- Modal Thêm Nhân Viên -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="<?= BASE_URL ?>/admin/users/store" method="POST" class="modal-content needs-validation" novalidate>
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Thêm hồ sơ nhân viên mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Mã nhân viên <span class="text-danger">*</span></label>
                        <input type="text" name="employee_code" class="form-control text-uppercase" placeholder="VD: NV005" required>
                        <div class="invalid-feedback">Vui lòng nhập mã nhân viên.</div>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Họ và tên <span class="text-danger">*</span></label>
                        <input type="text" name="full_name" class="form-control" placeholder="Nguyễn Văn A" required>
                        <div class="invalid-feedback">Vui lòng nhập họ tên.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Email đăng nhập <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" placeholder="nhanvien@hr.vn" required>
                        <div class="invalid-feedback">Vui lòng nhập email hợp lệ.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Mật khẩu khởi tạo <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" minlength="6" placeholder="Tối thiểu 6 ký tự" required>
                        <div class="invalid-feedback">Mật khẩu tối thiểu 6 ký tự.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Số điện thoại <span class="text-danger">*</span></label>
                        <input type="text" name="phone" class="form-control" pattern="0[0-9]{9}" placeholder="0901234567" required>
                        <div class="invalid-feedback">Số điện thoại gồm 10 chữ số, bắt đầu bằng 0.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Lương theo giờ (VNĐ) <span class="text-danger">*</span></label>
                        <input type="number" name="hourly_rate" class="form-control" value="25000" min="15000" step="1000" required>
                        <div class="invalid-feedback">Nhập mức lương hợp lệ.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Cửa hàng trực thuộc <span class="text-danger">*</span></label>
                        <?php if ($isManager): ?>
                            <select name="store_id" class="form-select" required>
                                <?php foreach ($stores as $st): ?>
                                    <option value="<?= $st['id'] ?>"><?= htmlspecialchars($st['store_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php else: ?>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($_SESSION['user']['store_name']) ?>" disabled>
                            <input type="hidden" name="store_id" value="<?= $_SESSION['user']['store_id'] ?>">
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Vai trò <span class="text-danger">*</span></label>
                        <select name="role" class="form-select">
                            <option value="Nhân viên PartTime">Nhân viên PartTime</option>
                            <option value="Học Việc">Học Việc</option>
                            <?php if ($isManager): ?>
                                <option value="Trưởng ca (TC)">Trưởng ca (TC)</option>
                                <option value="Quản lý">Quản lý</option>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="submit" class="btn btn-primary">Lưu hồ sơ</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Sửa Nhân Viên -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="<?= BASE_URL ?>/admin/users/update" method="POST" class="modal-content needs-validation" novalidate>
            <input type="hidden" name="id" id="edit_user_id">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Cập nhật hồ sơ nhân viên</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Mã nhân viên <span class="text-danger">*</span></label>
                        <input type="text" name="employee_code" id="edit_user_code" class="form-control text-uppercase" required>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Họ và tên <span class="text-danger">*</span></label>
                        <input type="text" name="full_name" id="edit_user_name" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" id="edit_user_email" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Mật khẩu mới <small class="text-muted fw-normal">(Để trống nếu giữ nguyên)</small></label>
                        <input type="password" name="password" class="form-control" minlength="6" placeholder="••••••••">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Số điện thoại <span class="text-danger">*</span></label>
                        <input type="text" name="phone" id="edit_user_phone" class="form-control" pattern="0[0-9]{9}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Lương theo giờ (VNĐ) <span class="text-danger">*</span></label>
                        <input type="number" name="hourly_rate" id="edit_user_rate" class="form-control" min="15000" step="1000" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Cửa hàng</label>
                        <?php if ($isManager): ?>
                            <select name="store_id" id="edit_user_store" class="form-select">
                                <?php foreach ($stores as $st): ?>
                                    <option value="<?= $st['id'] ?>"><?= htmlspecialchars($st['store_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php else: ?>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($_SESSION['user']['store_name']) ?>" disabled>
                            <input type="hidden" name="store_id" value="<?= $_SESSION['user']['store_id'] ?>">
                        <?php endif; ?>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Vai trò</label>
                        <select name="role" id="edit_user_role" class="form-select">
                            <option value="Nhân viên PartTime">Nhân viên PartTime</option>
                            <option value="Học Việc">Học Việc</option>
                            <?php if ($isManager): ?>
                                <option value="Trưởng ca (TC)">Trưởng ca (TC)</option>
                                <option value="Quản lý">Quản lý</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Trạng thái</label>
                        <select name="status" id="edit_user_status" class="form-select">
                            <option value="1">Đang làm việc</option>
                            <option value="0">Nghỉ việc</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="submit" class="btn btn-warning fw-semibold">Cập nhật</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.querySelectorAll('.btn-edit-user').forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('edit_user_id').value     = this.dataset.id;
            document.getElementById('edit_user_code').value   = this.dataset.code;
            document.getElementById('edit_user_name').value   = this.dataset.name;
            document.getElementById('edit_user_email').value  = this.dataset.email;
            document.getElementById('edit_user_phone').value  = this.dataset.phone;
            document.getElementById('edit_user_rate').value   = this.dataset.rate;
            document.getElementById('edit_user_status').value = this.dataset.status;

            const storeSelect = document.getElementById('edit_user_store');
            if (storeSelect) storeSelect.value = this.dataset.store;

            const roleSelect = document.getElementById('edit_user_role');
            if (roleSelect) roleSelect.value = this.dataset.role;
        });
    });

    function filterUsers() {
        const keyword = document.getElementById('searchUser').value.toLowerCase();
        const storeEl = document.getElementById('filterStore');
        const storeVal = storeEl ? storeEl.value.toLowerCase() : '';

        document.querySelectorAll('#userTable tbody tr').forEach(row => {
            const text = row.innerText.toLowerCase();
            const matchKeyword = text.includes(keyword);
            const matchStore = !storeVal || text.includes(storeVal);
            row.style.display = (matchKeyword && matchStore) ? '' : 'none';
        });
    }

    document.getElementById('searchUser').addEventListener('keyup', filterUsers);
    const filterStoreEl = document.getElementById('filterStore');
    if (filterStoreEl) filterStoreEl.addEventListener('change', filterUsers);

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

<?php require_once __DIR__ . '/../layouts/admin_footer.php'; ?>