<?php

class Model {
    
    protected $table;
    protected $db;

    function __construct () {
    }

    protected function getPdo () {
        $folder = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
        require $folder . '/actions/databaseAction.php';
        return $db;
    }

    // Get instance data from database
    protected function getData ($table) {
        $db = $this->getPdo();
        $getData = $db->prepare("SELECT * FROM {$this->table} WHERE id = {$this->id}");
        $getData->execute();
        return $getData->fetch();
    }

}