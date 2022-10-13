<?php

class RouteFeaturedImage extends Image {
    
    protected $table = 'routes';
    public $id;
    public $blob;
    public $size;
    public $name;
    public $type;
    
    function __construct ($id = NULL) {
        parent::__construct($id);
        $data = $this->getData($this->table);
        $this->blob = $data['featured_image_blob'];
        $this->size = $data['featured_image_size'];
        $this->name = $data['featured_image_name'];
        $this->type = $data['featured_image_type'];
    }

}