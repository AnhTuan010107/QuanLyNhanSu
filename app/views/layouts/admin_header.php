<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Quản trị Hệ thống Nhân sự' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; }
        .sidebar-nav .nav-link { color: #cbd5e1; border-radius: 6px; margin-bottom: 4px; }
        .sidebar-nav .nav-link:hover, .sidebar-nav .nav-link.active { background-color: #0d6efd; color: #fff; }
        @media (min-width: 768px) {
            .sidebar-desktop { width: 250px; min-height: calc(100vh - 56px); }
        }
    </style>
</head>
<body>
<nav class="navbar navbar-dark bg-dark sticky-top shadow-sm px-3">
    <div class="d-flex align-items-center">
        <button class="btn btn-outline-light btn-sm d-md-none me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar">
            <i class="bi bi-list"></i>
        </button>
        <a class="navbar-brand fw-bold mb-0" href="<?= BASE_URL ?>/admin/dashboard">
            <i class="bi bi-people-fill text-primary me-1"></i> HR SYSTEM
        </a>
    </div>
    <div class="d-flex align-items-center text-white small">
        <span class="me-3 d-none d-sm-inline">
            <i class="bi bi-person-circle me-1"></i>
            <?= htmlspecialchars($_SESSION['user']['full_name']) ?>
            <span class="badge bg-primary ms-1"><?= htmlspecialchars($_SESSION['user']['role']) ?></span>
        </span>
        <a href="<?= BASE_URL ?>/logout" class="btn btn-outline-danger btn-sm">
            <i class="bi bi-box-arrow-right"></i> Đăng xuất
        </a>
    </div>
</nav>
<div class="d-flex">