<?php

class Model {
    
    protected $table;

    // Get instance data from database
    protected function getData ($table) {
        require $_SERVER["DOCUMENT_ROOT"] . '/actions/databaseAction.php';
        $getData = $db->prepare("SELECT * FROM {$this->table} WHERE id = {$this->id}");
        $getData->execute();
        return $getData->fetch();
    }

}