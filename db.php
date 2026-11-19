<?php
class connection {
    public $db = null;
    public $dbname = "bookstore";
    public $username = "root";
    public $password = "";

    public function __construct(){
        $this -> db = new PDO("mysql:host={$this -> host};dbname={$this -> dbname}",
        $this -> username,
        $this -> password,
        [PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"]);
    }

    //select
    //select many
    public function getlist($sql){
        return $this -> db -> query($sql) -> fetchAll();
    }

    //select one
    public function getInstance($sql){
        return $this -> db -> query($sql) -> fetch();
    }

    //insert, update, delete
    public function exec($sql){
        return $this -> db -> exec($sql);
    }

}