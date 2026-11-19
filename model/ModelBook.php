<?php
class ModelBook extends MasterModel {
    public function getDuLieu() {
        // return parent::get_all_from("books");
        $sql = "SELECT * FROM books";
        return $GLOBALS["connect"]->getlist($sql);
    }
}
