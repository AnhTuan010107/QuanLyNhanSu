<?php
class Home {
    public static function index() 
    {
        $model = new ModelBook();
        $result = $model -> getDuLieu();    
        require_once "view/home/index.php";
    }
}
?>