<?php

class ActivityPhoto extends Image {
    
    protected $table = 'activity_photos';
    public $id;
    public $blob;
    public $size;
    public $name;
    public $type;
    public $datetime;
    public $featured;

    function __construct ($id = NULL) {
        $this->id       = $id;
        $data = $this->getData($this->table);
        $this->blob     = $data['img_blob'];
        $this->size     = intval($data['img_size']);
        $this->name     = $data['img_name'];
        $this->type     = $data['img_type'];
        $this->datetime = new DateTime($data['datetime']);
        if (intval($data['featured']) == 1) $this->featured = true;
        else $this->featured = false;
    }

}