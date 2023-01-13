<?php

class AdditionalFieldAnswer extends Model {
    
    protected $table = 'ride_additional_field_answers';
    public $id;
    public $field_id;
    public $user_id;
    public $content;

    function __construct ($id = NULL) {
        parent::__construct($id);
        $this->id       = $id;
        $data = $this->getData($this->table);
        $this->field_id = $data['field_id'];
        $this->user_id  = $data['user_id'];
        $this->content  = $data['content'];
    }

}