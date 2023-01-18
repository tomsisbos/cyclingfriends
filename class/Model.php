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
        if ($this->id != NULL) {
            $getData = $this->getPdo()->prepare("SELECT * FROM {$this->table} WHERE id = {$this->id}");
            $getData->execute();
            return $getData->fetch();
        }
    }
    
    // Attribute a color depending on the level
    public function colorLevel ($level) {
        switch ($level) {
            case 1 : return 'green';
            case 2 : return 'blue';
            case 3 : return 'red'; 
            default : return 'black';
        }
    }

}