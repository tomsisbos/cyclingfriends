<?php

class CheckpointImage extends Model {
    
    protected $table = 'ride_checkpoints';
    public $id;
    public $blob;
    public $size;
    public $name;
    public $type;

    function __construct ($id = NULL) {
        parent::__construct();
        $this->id                        = $id;
        $data = $this->getData($this->table);
        $this->blob                      = $data['img'];
        $this->size                      = $data['img_size'];
        $this->name                      = $data['img_name'];
        $this->type                      = $data['img_type'];
    }

}