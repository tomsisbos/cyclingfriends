<?php

class UserImage extends Image {
    
    protected $table = 'user_photos';
    public $id;
    public $number;
    public $blob;
    public $size;
    public $name;
    public $type;  
    public $caption;
    
    function __construct ($id = NULL) {
        parent::__construct($id);
        $data = $this->getData($this->table);
        $this->number  = $data['img_id'];
        $this->blob    = $data['img'];
        $this->size    = $data['size'];
        $this->name    = $data['name'];
        $this->type    = $data['type'];
        $this->caption = $data['caption'];
    }

}