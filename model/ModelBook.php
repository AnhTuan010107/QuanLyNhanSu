<?php
class ModelBook extends MasterModel {
    public function __construct() {
        return parent::get_all_from("books");
    }
}
