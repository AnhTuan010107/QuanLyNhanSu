<?php
//Goi tang controller
require_once 'controller/Home.php';
//Goi database
require_once 'config/db.php';
//Dung switch de hien giao dien bang URL. VD: demo.com/?controller=home
switch($controller){
    case 'Home':
        $controller = new Home();
        break;
}
$controller->{ $action }();
?>
