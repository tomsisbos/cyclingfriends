<?php

class Image extends Model {
    
    protected $table;
    public $id;

    function __construct ($id = NULL) {
        parent::__construct();
        $this->id = $id;
        $data = $this->getData($this->table);
    }



}