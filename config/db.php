<?php
class DB {
    public static function connect() {
        $dsn = "mysql:host=localhost;dbname=quan_ly_nhan_su;charset=utf8mb4";
        $conn = new PDO($dsn, "root", "");
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $conn;
    }
}