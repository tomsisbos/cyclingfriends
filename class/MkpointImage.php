<?php

class MkpointImage extends Image {
    
    protected $table = 'img_mkpoint';
    public $id;
    public $mkpoint_id;
    public $user_id;
    public $user_login;
    public $date;
    public $month;
    public $period;
    public $blob;
    public $size;
    public $name;
    public $type;  
    public $likes;
    
    function __construct ($id = NULL) {
        parent::__construct($id);
        $data = $this->getData($this->table);
        $this->mkpoint_id = intval($data['mkpoint_id']);
        $this->user_id    = intval($data['user_id']);
        $this->user_login = $data['user_login'];
        $this->date       = $data['date'];
        $this->month      = intval($data['month']);
        $this->period     = $data['period'];
        $this->blob       = $data['file_blob'];
        $this->size       = intval($data['file_size']);
        $this->name       = $data['file_name'];
        $this->type       = $data['file_type'];
        $this->likes      = intval($data['likes']);
    }

}