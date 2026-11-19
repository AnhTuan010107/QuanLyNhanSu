<?php

setlocale(LC_NUMERIC, 'vi_VN');

//Nhung thu vien Model va Database
require_once 'config/db.php';
require_once 'model/master.php';
require_once 'config/const.php';
//Nhung thu vien Controller

$connect = new connect();

//Goi URL duoi ten demo.com/?controller=yourController&action=yourAction
if(isset($_GET['controller'], $_GET['action'])){
    $controller = $_GET['controller'];
    $action = $_GET['action'];
} else{
    $controller = 'Home';
    $action = 'index';
}
require_once 'view/layout.php';
?>