<?php

class AdditionalFieldOption extends Model {
    
    protected $table = 'ride_additional_field_options';
    public $id;
    public $field_id;
    public $number;
    public $content;

    function __construct ($id = NULL) {
        parent::__construct($id);
        $this->id       = $id;
        $data = $this->getData($this->table);
        $this->field_id = $data['field_id'];
        $this->number   = $data['number'];
        $this->content  = $data['content'];
        if (isset($data['price'])) $this->product = new Product($this->content, $data['price']);
    }
    
}