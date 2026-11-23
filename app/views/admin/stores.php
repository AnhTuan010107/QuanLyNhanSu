<?php
$pageTitle = 'Quản lý Cửa hàng';
$activePage = 'stores';
require_once __DIR__ . '/../layouts/admin_header.php';
require_once __DIR__ . '/../layouts/admin_sidebar.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-0">Quản lý Cửa hàng</h4>
        <small class="text-muted">Danh sách các cơ sở, chi nhánh kinh doanh trong hệ thống</small>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStoreModal">
        <i class="bi bi-plus-lg me-1"></i> Thêm cửa hàng
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
        <div class="row align-items-center">
            <div class="col-md-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                    <input type="text" id="searchStore" class="form-control" placeholder="Tìm theo tên cửa hàng, địa chỉ hoặc SĐT...">
                </div>
            </div>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="storeTable">
            <thead class="table-light">
                <tr>
                    <th width="60">STT</th>
                    <th>Tên cửa hàng</th>
                    <th>Địa chỉ</th>
                    <th>Số điện thoại</th>
                    <th class="text-center">Nhân sự</th>
                    <th class="text-center">Trạng thái</th>
                    <th class="text-end" width="140">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($stores)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">Chưa có cửa hàng nào trong hệ thống.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($stores as $index => $store): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td class="fw-semibold text-primary"><?= htmlspecialchars($store['store_name']) ?></td>
                            <td><?= htmlspecialchars($store['address']) ?></td>
                            <td><?= htmlspecialchars($store['phone']) ?></td>
                            <td class="text-center">
                                <span class="badge bg-secondary rounded-pill"><?= $store['total_staff'] ?> nhân viên</span>
                            </td>
                            <td class="text-center">
                                <?php if ((int)$store['status'] === 1): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">Hoạt động</span>
                                <?php else: ?>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Ngừng hoạt động</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <button type="button"
                                        class="btn btn-sm btn-outline-warning btn-edit-store"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editStoreModal"
                                        data-id="<?= $store['id'] ?>"
                                        data-name="<?= htmlspecialchars($store['store_name']) ?>"
                                        data-address="<?= htmlspecialchars($store['address']) ?>"
                                        data-phone="<?= htmlspecialchars($store['phone']) ?>"
                                        data-status="<?= $store['status'] ?>"
                                        title="Chỉnh sửa">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <form action="<?= BASE_URL ?>/admin/stores/delete" method="POST" class="d-inline"
                                      onsubmit="return confirm('Bạn có chắc chắn muốn xóa cửa hàng <?= htmlspecialchars($store['store_name']) ?>?');">
                                    <input type="hidden" name="id" value="<?= $store['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Xóa">
                                        <i class="bi bi-trash"></i>
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

<!-- Modal Thêm Cửa Hàng -->
<div class="modal fade" id="addStoreModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?= BASE_URL ?>/admin/stores/store" method="POST" class="modal-content needs-validation" novalidate>
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Thêm cửa hàng mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Tên cửa hàng <span class="text-danger">*</span></label>
                    <input type="text" name="store_name" class="form-control" placeholder="VD: Cửa hàng Quận 3" required>
                    <div class="invalid-feedback">Vui lòng nhập tên cửa hàng.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Địa chỉ <span class="text-danger">*</span></label>
                    <input type="text" name="address" class="form-control" placeholder="Số nhà, đường, quận/huyện..." required>
                    <div class="invalid-feedback">Vui lòng nhập địa chỉ cửa hàng.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Số điện thoại liên hệ <span class="text-danger">*</span></label>
                    <input type="text" name="phone" class="form-control" pattern="[0-9]{9,12}" placeholder="02838221111" required>
                    <div class="invalid-feedback">Số điện thoại gồm 9 - 12 chữ số.</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="submit" class="btn btn-primary">Lưu cửa hàng</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Sửa Cửa Hàng -->
<div class="modal fade" id="editStoreModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?= BASE_URL ?>/admin/stores/update" method="POST" class="modal-content needs-validation" novalidate>
            <input type="hidden" name="id" id="edit_id">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Cập nhật thông tin cửa hàng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Tên cửa hàng <span class="text-danger">*</span></label>
                    <input type="text" name="store_name" id="edit_name" class="form-control" required>
                    <div class="invalid-feedback">Vui lòng nhập tên cửa hàng.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Địa chỉ <span class="text-danger">*</span></label>
                    <input type="text" name="address" id="edit_address" class="form-control" required>
                    <div class="invalid-feedback">Vui lòng nhập địa chỉ cửa hàng.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Số điện thoại <span class="text-danger">*</span></label>
                    <input type="text" name="phone" id="edit_phone" class="form-control" pattern="[0-9]{9,12}" required>
                    <div class="invalid-feedback">Số điện thoại gồm 9 - 12 chữ số.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Trạng thái hoạt động</label>
                    <select name="status" id="edit_status" class="form-select">
                        <option value="1">Hoạt động</option>
                        <option value="0">Ngừng hoạt động</option>
                    </select>
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
    document.querySelectorAll('.btn-edit-store').forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('edit_id').value      = this.dataset.id;
            document.getElementById('edit_name').value    = this.dataset.name;
            document.getElementById('edit_address').value = this.dataset.address;
            document.getElementById('edit_phone').value   = this.dataset.phone;
            document.getElementById('edit_status').value  = this.dataset.status;
        });
    });

    document.getElementById('searchStore').addEventListener('keyup', function () {
        const keyword = this.value.toLowerCase();
        document.querySelectorAll('#storeTable tbody tr').forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(keyword) ? '' : 'none';
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

<?php require_once __DIR__ . '/../layouts/admin_footer.php'; ?>