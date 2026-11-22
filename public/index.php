<?php
session_start();

$url = isset($_GET['url']) ? rtrim($_GET['url'], '/') : 'home';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hệ thống Quản lý Nhân sự</title>
</head>
<body style="font-family: Arial, sans-serif; padding: 30px;">
    <h2>Khởi tạo khung PHP MVC thành công!</h2>
    <p>Đường dẫn hiện tại (URL route): <strong><?= htmlspecialchars($url) ?></strong></p>
</body>
</html>
